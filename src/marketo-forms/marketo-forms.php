<?php
/*
Plugin Name: Marketo Forms for WP
Description: Howdy there. This right here is a fabulous plugin to integrate Marketo into your WordPress site. Build your form straight from WordPress, style it and trigger a campaign. Simple and easy to use. If you got any feedback on it, let me know; if not Keep calm and enjoy :)
Version: 1.0
Author: Jide Okusanya
Author URI: http://jide.okus.me

License: GPLv2
*/
	if(is_admin()){
		include( plugin_dir_path( __FILE__ ) . 'options.php');
		
	}
	add_action( 'init', 'tsn_mkto_create_post_type' );
	add_action('init', 'tsn_mkto_includes');
	add_action('admin_init', 'tsn_mkto_post_type_setup');
	
	
	add_action('admin_enqueue_scripts','tsn_mkto_load_admin_scripts');
	add_action('save_post', 'tsn_mkto_save_form'); 
	add_shortcode('tsn_mkto_form','tsn_mkto_shortcode_handler');
	add_action('template_redirect', 'tsn_mkto_check_for_submission');
	add_action( 'admin_init', 'tsn_mkto_add_editor_button' );
	
	function tsn_mkto_includes(){
		include_once(plugin_dir_path(__FILE__) . 'form-actions.php');
		include_once(plugin_dir_path(__FILE__) . 'constants.php');
	}
	function tsn_mkto_load_admin_styles(){
		wp_enqueue_style('market-form-admin-css',plugins_url( 'css/admin-style.css', __FILE__ ));
	}
	
	function tsn_mkto_create_post_type() {
		register_post_type( 'tsn_mkto_form',
			array(
				'labels' => array(
					'name' => 'Marketo Forms',
					'singular_name' => 'Marketo Form',
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
	
	
	
	function tsn_mkto_check_for_submission(){
		if(isset($_POST['mkto_form_submit'])){
			include_once(plugin_dir_path(__FILE__) . "form-handler.php");
		}
	}
	function tsn_mkto_post_type_setup(){
	  add_meta_box("tsn_mkto_form_settings_meta", "Form Fields", "tsn_mkto_form_add_setting_meta", "tsn_mkto_form", "normal", "low");
	  add_meta_box("tsn_mkto_form_submit_text_meta", "Submit Button Text", "tsn_mkto_form_add_submit_text_meta", "tsn_mkto_form", "side", "low");	 
	}
	function tsn_mkto_form_add_setting_meta(){
		global $post;
		$custom = get_post_custom($post->ID);
		$fields = unserialize($custom["form_field"][0]);
		include_once(plugin_dir_path(__FILE__) . "form-actions.php");
		$all_fields = tsn_mkto_get_fields();
		
		if($all_fields):
			$all_fields = json_encode($all_fields);
			
		?>
			<script type="text/javascript">
				var tsn_mkto_all_fields = <?php echo $all_fields?>;
				var TSN_MKTO_FIELD_TEXT = "<?php echo TSN_MKTO_FIELD_TEXT?>";
				var TSN_MKTO_FIELD_TEXTAREA = "<?php echo TSN_MKTO_FIELD_TEXTAREA?>";
				var TSN_MKTO_FIELD_RADIO = "<?php echo TSN_MKTO_FIELD_RADIO?>";
				var TSN_MKTO_FIELD_CHECKBOX = "<?php echo TSN_MKTO_FIELD_CHECKBOX?>";
				var TSN_MKTO_FIELD_DROPDOWN = "<?php echo TSN_MKTO_FIELD_DROPDOWN?>";
			</script>
			<div>
				<div id="tsn-mkto-form-fields"></div>
				<button type="button" id="tsn-mkto-admin-add-button" class="button-primary"><?php _e('Add Field')?></button>
			</div>
			<script type="text/javascript">
				jQuery(document).ready(function(){
					
					<?php
						if($custom["form_field"][0]!=""){
								foreach($fields as $field){
									echo 'tsn_mkto_add_field(' . json_encode($field) . ');';
								}
						}else{
							echo 'tsn_mkto_add_field(' . json_encode($field) . ');';
						}
					?>
					jQuery("#tsn-mkto-admin-add-button").click(function(){
						tsn_mkto_add_field();
					});
					jQuery("#post").validate();
				});
			</script>
		<?php
		else:?>
		<h3 class="tsn-mkto-admin-error">Marketo settings missing! Please configure Marketo plugin <a href="<?php echo admin_url().'edit.php?post_type=tsn_mkto_form&page=tsn-mkto-form-settings'?>">here</a></h3>
	<?php
		endif;
	}
	function tsn_mkto_form_add_submit_text_meta(){
		global $post;
		$custom = get_post_custom($post->ID);
		$submit_text = $custom["submit_text"][0];
		?>
		<input type="text" name="submit_text" value="<?php echo $submit_text;?>" placeholder="Submit"/><?php
	}
	function tsn_mkto_save_form(){
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
	function tsn_mkto_load_admin_scripts(){
		wp_enqueue_script("tsn_mkto_form_script",plugins_url( 'js/script.js', __FILE__ ));
		wp_enqueue_script("tsn_mkto_form_validate_script",plugins_url( 'js/validate.js', __FILE__ ));
		wp_enqueue_style("tsn_mkto_form_css",plugins_url( 'css/admin-style.css', __FILE__ ));
		
	}
	
	
	function tsn_mkto_get_form_fields($form_id){
		$post = get_post($form_id);
		return $post->form_field;
	}
	
	
	function tsn_mkto_shortcode_handler($atts){
		$form_title=$atts['heading'];
		$post_reg_url = $atts['post_reg'];
		$target=$atts['new_window'];
		if($atts['show_labels']=='yes'){
			$show_labels = true;
		}else{
			$show_labels=false;
		}
		
		$campaign_id=$atts['cpnid'];
		$form_id=$atts['id'];		
		return tsn_mkto_show_form($atts["id"],$atts["cpnid"],$form_title,$post_reg_url,$target,$show_labels);
	}
		
	
	/**Functions for adding the editor button**/
	function tsn_mkto_add_editor_button() {
		if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) ) {
			add_filter( 'mce_buttons', 'tsn_mkto_register_editor_button' );
			add_filter( 'mce_external_plugins', 'tsn_mkto_add_button_script' );
			foreach ( array('post.php','post-new.php') as $hook ) {
				add_action( "admin_head-$hook", 'tsn_mkto_button_head' );
			}
		}
	}
	function tsn_mkto_register_editor_button( $buttons ) {
		 array_push( $buttons, "-tsn-mkto-form");
		 return $buttons;
	}
	function tsn_mkto_button_head() {
		$plugin_url = plugins_url( '/', __FILE__ );
		$forms = get_posts(array('post_type'=>'tsn_mkto_form','post_status'=>'publish'));
		$forms_for_button=[];
		foreach($forms as $post){
			$text = $post->post_title;
			$value= $post->ID;
			array_push($forms_for_button,array("text"=>$text,"value"=>$value));
		}
		wp_reset_postdata();
		
		echo '<script type="text/javascript">';
		echo 'var marketo_form_plugin_url="'.$plugin_url.'";';
		echo 'var marketo_forms='.json_encode($forms_for_button).';';
		echo '</script>';
	}
		
	function tsn_mkto_add_button_script( $plugin_array ) {
		 $plugin_array['tsn_mkto_button_script'] = plugins_url( 'js/marketo-form-button.js', __FILE__ ) ;
		 return $plugin_array;
	}
?>