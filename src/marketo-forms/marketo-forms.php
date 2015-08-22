<?php
/*
Plugin Name: Marketo Forms
Description: This plugin is to place Marketo Forms to the IT Convergence website
Version: 1.0
Author: Babajide Okusanya
License: GPLv2
*/
	
	add_action('wp_enqueue_script','marketo_load_admin_style_and_options');
	add_action( 'init', 'create_marketo_form' );
	add_action("admin_init", "marketo_form_admin_init");
	add_action("admin_enqueue_scripts","marketo_form_add_script");
	add_action('save_post', 'save_marketo_form_details'); 
	add_shortcode('mkto_form','form_shortcode_handler');
	add_action('template_redirect', 'check_for_form_submission');
	add_action('wp_head', 'load_validation_script');
	add_action( 'admin_init', 'marketo_form_button' );
	
	function marketo_load_admin_style_and_options(){
		if(is_admin()){
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			include( plugin_dir_path( __FILE__ ) . 'options.php');
			wp_enqueue_style('market-form-admin-css',plugins_url( 'css/admin-style.css', __FILE__ ));
			if(is_plugin_active('whitepapers/whitepapers.php')){
				include( plugin_dir_path( __FILE__ ) . 'whitepaper-options.php');
			}
		}
	}

	function create_marketo_form() {
		register_post_type( 'marketo_form',
			array(
				'labels' => array(
					'name' => 'Marketo Forms',
					'singular_name' => 'marketo_form',
					'add_new' => 'Add New',
					'add_new_item' => 'Add New Form',
					'edit' => 'Edit',
					'edit_item' => 'Edit Form',
					'new_item' => 'New Form',
					'view' => 'View',
					'view_item' => 'View Form',
					'search_items' => 'Search Form',
					'not_found' => 'No Form found',
					'not_found_in_trash' => 'No Form found in Trash',
					'parent' => 'Parent Form'
				),
				
				'public' => false,
				'has_archive'=>false,
				'show_ui'=>true,
				'menu_position' => 5,
				'supports' => array( 'title'),
				'taxonomies' => array( '' ),
				'menu_icon' => plugins_url( 'images/image.png', __FILE__ ),
				'has_archive' => true,
				'rewrite' => array( 'slug' => 'marketo_form' )
			)
		);
	}
	
	function marketo_form_button() {
		if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) ) {
			add_filter( 'mce_buttons', 'register_marketo_button' );
			add_filter( 'mce_external_plugins', 'add_marketo_plugin_js' );
			foreach ( array('post.php','post-new.php') as $hook ) {
				add_action( "admin_head-$hook", 'marketo_form_button_head' );
			}
		}
	}
	function marketo_form_button_head() {
		$plugin_url = plugins_url( '/', __FILE__ );
		$forms = get_posts(array('post_type'=>'marketo_form','post_status'=>'publish'));
		$forms_for_button=[];
		foreach($forms as $post){
			$text = $post->post_title;
			$value= $post->ID;
			array_push($forms_for_button,array("text"=>$text,"value"=>$value));
		}
		wp_reset_postdata();
		
		echo '<script type="text/javascript">var marketo_form_plugin_url="'.$plugin_url.'";';
		echo 'var marketo_forms='.json_encode($forms_for_button).';';
		echo '</script>';
	}
	
	function register_marketo_button( $buttons ) {
		 array_push( $buttons, "-marketo-form");
		 return $buttons;
	}
	
	function add_marketo_plugin_js( $plugin_array ) {
		
		 $plugin_array['marketo_form_script'] = plugins_url( 'js/marketo-form-button.js', __FILE__ ) ;
		 return $plugin_array;
	}
	
	function check_for_form_submission(){
		if(isset($_POST['mkto_form_submit'])){
			include_once(plugin_dir_path(__FILE__) . "form-handler.php");
		}else if (isset($_GET['download'])){
			$file="";
			
			if($_GET['action']=='wp_download')
				$file=get_post($_GET['data'])->whitepaper_file;
			else if($_GET['action']=='webcast_presentation_download')
				$file=get_post($_GET['data'])->webcast_presentation;
			else if($_GET['action']=='webcast_recording_download')
				$file=get_post($_GET['data'])->webcast_recording;
			//$file=str_replace('http://itc-365-v1oraas001.itconvergence.com',"",$file);
			$file = $_SERVER["DOCUMENT_ROOT"].'new-site-08.10' .$file;
			
			
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.basename($file).'"'); 
			header('Content-Length: ' . filesize($file));
			readfile($file);
		}
	}
	function marketo_form_admin_init(){
	  add_meta_box("marketo_form_settings_meta", "Form Fields", "marketo_form_settings_meta", "marketo_form", "normal", "low");
	  add_meta_box("marketo_form_submit_text_meta", "Submit Button Text", "marketo_form_submit_text_meta", "marketo_form", "side", "low");	 
	}
	function marketo_form_add_script(){
		wp_enqueue_script("marketo_forms_script",plugins_url( 'js/script.js', __FILE__ ));
		wp_enqueue_style('market-form-admin-css',plugins_url( 'css/admin-style.css', __FILE__ ));
	}
	function load_validation_script(){
		wp_enqueue_script("marketo_forms_script",get_stylesheet_directory_uri().'/js/jquery.validate.js');
	}
	function marketo_form_settings_meta(){
		global $post;
		$custom = get_post_custom($post->ID);
		$fields = unserialize($custom["form_field"][0]);
		
		?>
		<div>
			<div id="marketo-form-fields"></div>
			<button type="button" id="marketo-form-add-button" class="button-primary">Add Field</button>
		</div>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				
				<?php
					if($custom["form_field"][0]!=""){
							foreach($fields as $field){
								echo 'marketo_form_add_field(' . json_encode($field) . ');';
							}
					}else{
						echo 'marketo_form_add_field(' . json_encode($field) . ');';
					}
				?>
				jQuery("#marketo-form-add-button").click(function(){
					marketo_form_add_field();
				});
				jQuery("#post").validate();
			});
		</script>
		<?php
	}
	function marketo_form_submit_text_meta(){
		global $post;
		$custom = get_post_custom($post->ID);
		$submit_text = $custom["submit_text"][0];
		?>
		<input type="text" name="submit_text" value="<?php echo $submit_text;?>" required/><?php
	}
	function get_marketo_form_fields($form_id){
		$post = get_post($form_id);
		return $post->form_field;
	}
	function show_marketo_form($form_id,$cpn_id,$post_reg_action="",$post_reg_data="",$target="",$form_title="",$popup=false,$button_text="",$popup_id="mkto_form_popup",$lead_text="",$btn_style="small_red"){
		global  $post, $wp_query;
		$post = get_post($form_id);
		$form_display = "";		
		$button_id = $popup_id.'_btn';
		
		if (isset($post)){
						
			$fields = $post->form_field;
			if($_COOKIE["_mkto_trk"])
				$lead_data=get_lead_data($fields);
			if($popup){
				if($btn_style=='small_red')
					$form_display.=sprintf('<input class="wpcf7-form-control wpcf7-submit itc-download-button" type="submit" data-toggle="modal" data-target="#%s" value="%s"/>',$popup_id,$button_text);
				else if ($btn_style=='long_cta'){
					$form_display.='<div class="spb_impact_text spb_content_element clearfix col-sm-12 cta_align_right itc-no-padding">';
							$form_display.='<div class="impact-text-wrap clearfix">';
								$form_display.='<div class="spb_call_text">';
									$form_display.='<p>'.$lead_text.'</p>';
								$form_display.='</div>';
								$form_display.=sprintf('<input class="sf-button sf-button accent " type="submit" data-toggle="modal" data-target="#%s" value="%s"/>',$popup_id,$button_text);
							$form_display.='</div>';
					$form_display.='</div>';
				}
							
				$form_display.='<div id="'.$popup_id.'" tabindex="-1" class="modal fade">
									<div class="modal-dialog" role="document">
										<div class="modal-content">';
											$form_display.='<div class="modal-header">';
												$form_display.='<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="ss-delete"></i></button>';
													if($form_title)
														$form_display.='<h3 id="modal-label">'.$form_title.'</h3>';		
											$form_display.='</div>';
											$form_display.='<div class="modal-body">';
			}
			
												//This is where the form begins
												$form_display.= '<form class = "wpcf7-form" method="POST" action="#">';
													if(!$popup & $form_title)
														$form_display.='<h3>'.$form_title.'</h3>';
													foreach($fields as $field){
														if($lead_data)
															$form_display.=show_field($field,$lead_data[$field["field_name"]]);
														else
															$form_display.=show_field($field);
													}
												
													$form_display.='<input class="wpcf7-form-control wpcf7-submit" id="'.$button_id.'" type="submit" value="'. __($post->submit_text,"swift-framework-admin") .'"/>';
													$form_display.= wp_nonce_field('marketo_form_sbumit','mkto_form_submit');
													$form_display.= '<input type="hidden" name="mkto_form_id" value="'.$form_id . '"/>';
													$form_display.='<input type="hidden" name="cpnid" value="'.$cpn_id . '"/>';
													$form_display.='<input type="hidden" name="post_reg_action" value="'.$post_reg_action . '"/>';
													$form_display.='<input type="hidden" name="post_reg_data" value="'.$post_reg_data . '"/>';
													$form_display.='<input type="hidden" name="post_reg_target" value="'.$target . '"/>';
												$form_display.="</form>";
											
			if($popup){
										$form_display.='</div>
									</div>
								</div>
							</div>';
			}
			$clicked_link="";
			if($post_reg_action=='wp_download'){
				$clicked_link=get_post($post_reg_data)->whitepaper_file;
			}else if ($post_reg_action=='webcast_presentation_download'){
				$clicked_link = get_post($post_reg_data)->webcast_presentation;
			}else if ($post_reg_action=='webcast_webcast_download'){
				$clicked_link = get_post($post_reg_data)->webcast_recording;
			}
				
				
			$form_display.='<script>
								jQuery(document).ready(function(){
									jQuery("#'.$button_id.'").click(function(){
										Munchkin.munchkinFunction("clickLink", { href: "'. $clicked_link.'" }); 
									});
									
								});
							</script>';
			return $form_display;
		}
		
	}
	function get_marketo_forms($field_name,$field_value=null){
		$forms = get_posts(array('post_type'=>'marketo_form'));
		$forms_field = "";
		$forms_field.='<select name="'.$field_name.'">';
		$forms_field.='<option value="">Select a form</option>';
		
		$value="";
		if($field_value){
			$value = $field_value;
		}else{
			$value = get_option($field_name);
		}
		$selected = "";
		foreach($forms as $form){
			$selected="";
			if($form->ID==$value){
				$selected = " selected";
			}
			$forms_field.='<option value="'.$form->ID.'"'. $selected.'>'.$form->post_title.'</option>';
			
		}
		$forms_field.="</select>";
		return $forms_field;
	}
	function form_shortcode_handler($atts){
		$post_reg_action="";
		$post_reg_data="";
		$form_title=$atts['heading'];
		$popup=false;
		$btn_text=$atts['btn_text'];
		$lead_text=$atts['lead_text'];
		$popup_id="mkto_form_popup";
		$btn_style=$atts['style'];
		$target="";
		if($atts['popup']=='yes'){
			$post_reg_action="";
			$post_reg_data="";
			$form_title=$atts['heading'];
			$popup=true;
			$btn_text=$atts['btn_text'];
			$lead_text=$atts['lead_text'];
			$popup_id="mkto_form_popup";
			$btn_style=$atts['style'];
			$target="";
			return show_marketo_form($atts['id'],$atts["cpnid"],$post_reg_action,$post_reg_data,$target,$form_title,$popup,$btn_text,$popup_id,$lead_text,$btn_style);
		}
		
		return show_marketo_form($atts["id"],$atts["cpnid"],$post_reg_action,$post_reg_data,$target,$form_title);
	}
	function is_marketo_form_required($form_id){
	
		if(!$form_id)
			return false;
		$fields=get_marketo_form_fields($form_id);
		$lead_data=get_lead_data($fields);
		if(!$lead_data){
			return true;
		}
		
		foreach($fields as $field){
			if($field=="")
				return true;
		}
		return false;
	}
	function get_lead_data($fields){
		include_once(plugin_dir_path(__FILE__) . "form-actions.php");
		$field_names = $lead_data = array();
	
		foreach($fields as $field){
			array_push($field_names,$field["field_name"]);
		}
		$marketo_token = get_marketo_token();
		$request_action="/v1/leads.json?filterType=cookie&filterValues=" . str_replace("&","%26",$_COOKIE["_mkto_trk"]) . "&fields=" . implode(",",$field_names);
		$rest_call = generate_call($request_action,$marketo_token,false);
		$lead_data = make_rest_call($rest_call,null,"get")->result[0];
		
		if($lead_data){
			$lead_data = get_object_vars($lead_data);	
		}
		return $lead_data;
	}
	function show_field($field,$value=""){
		
		
		$field_element = '<div class="wpcf7-form-control-wrap">';
		$field_element.= '<label for="'.$field["field_name"].'">'.$field["field_label"].'</label>';
		$options_list = explode(",",$field["field_options"]);
		$validation = $field["field_validation"];
		$add_number_rule = false;
		$option_status ="";
		if($field["field_type"]=="radio" || $field["field_type"]=="checkbox")
			$option_status="checked";
		else if($field["field_type"]=="dropdown")
			$option_status="selected";
			
		switch ($field["field_type"]){
			case "text":
				$field_element.= '<input type="' . $field["field_validation"] .'" class="wpcf7-form-control wpcf7-text" name="' . $field["field_name"] . '" '. $field["field_required"] . ' value="'.$value.'"/>';
				break;
			case "text-area":
				$field_element.= '<textarea class="class="wpcf7-form-control wpcf7-textarea" name="' . $field["field_name"] . '" '.$field["field_required"].'>'.$value.'</textarea>';
				break;
			case "dropdown":
				$field_element.= '<select name="'. $field["field_name"] . '">';
				foreach($options_list as $option){
						$field_element.='<option value="'. $option .'" ';
						if($value==$option)
							$field_element.=$option_status;
						$field_element.='>' . $option .'</option>';
				}	
				$field_element.= '</select>';
				break;
			case "radio":
				foreach($options_list as $option){
					$field_element .='<span><input value='.$option.' type="radio" name="'.$field["field_name"] .'" '.$option_status .'/>'.$option.'</span>';
				}
			case "checkbox":
				if(count($options_list)>0){
					foreach($options_list as $option){
						$field_element .='<span><input value='.$option.' type="checkbox" name="'.$field["field_name"] .'[]" '.$option_status.'/>'.$option.'</span>';
					}
				}else{
					$field_element.='<input value='.$option.' type="checkbox" name="'.$field["field_name"] . '[]" '.$option_status.'/>';
				}
		}
		$field_element.="</div>";
		return $field_element;
	}
	
	function save_marketo_form_details(){
		global $post;
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post->ID;
		}
		$form_fields = $_POST["form_field"];
		update_post_meta($post->ID,"submit_text",$_POST["submit_text"]);
		if(count($form_fields)>0){
			update_post_meta($post->ID,"form_field",$form_fields);
		}
	}
?>