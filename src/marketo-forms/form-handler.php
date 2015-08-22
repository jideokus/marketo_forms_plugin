<?php
	include_once(plugin_dir_path(__FILE__) . "form-actions.php");
	$marketo_token = "";
	$marketo_token = get_marketo_token();
	$lead_update = marketo_update_lead($marketo_token);
	
	$leadId="";
	$post_reg_data=$_POST["post_reg_data"];
	$post_reg_action=$_POST["post_reg_action"];
	$post_reg_target=$_POST["post_reg_target"];

	if($lead_update->success){
		$leadId = $lead_update->result[0]->id;	
		marketo_assign_associate_lead($leadId,$marketo_token);
		$campaign_update = marketo_add_to_campaign($leadId,$marketo_token);
		$post_with_content=get_post($_POST['post_reg_data']);
		$download_file_name = $post_with_content->post_title;
		if($post_reg_action=="wp_download"){
			//add_action( 'wp_head', 'whitepaper_post_reg' );
			$whitepaper=$post_with_content->whitepaper_file;
			download_file($whitepaper);
		}else if ($post_reg_action=='webcast_reg'){
			$thank_you_page = get_permalink($post_reg_data);
			wp_redirect($thank_you_page);
		}else if ($post_reg_action=='webcast_presentation_download'){
			$presentation = $post_with_content->webcast_presentation;
			download_file($presentation);
		}else if ($post_reg_action=='webcast_recording_download'){
			$recording = $post_with_content->webcast_recording;
			download_file($recording);
		}
		
	}
	
	
	function download_file($file){
		
		echo '<script>window.open("'.site_url().'?download=true&action='.$_POST["post_reg_action"].'&data='. $_POST["post_reg_data"].'","_blank")</script>';
		
		
		/*$notification_message = __("Please enable pop ups for this site or click on the download button again",'swiftframework');
		$post_reg.="<script>window.open('$file','$post_reg_target');";
		$post_reg.='jQuery(document).ready(function(){
			jQuery("#whitepaper-notification").append(\'<div class="alert spb_content_element alert-info"><div class="messagebox_text"><p><strong>Information</strong>: '.$notification_message.'</p></div></div>\');
			});';
		$post_reg.='var timer = window.setTimeout( function(){
			jQuery("#whitepaper-notification").slideUp();
		}, 5000 );';
		$post_reg .='clearTimeout(timer)';
		$post_reg.='</script>';
		*/
	}
	
	function marketo_update_lead($marketo_token){
		$form = get_post($_POST['mkto_form_id']);
		$fields = $form->form_field;
		$fields_to_submit=array();
		foreach($fields as $field){
			$field_name = $field["field_name"];
			$fields_to_submit[$field_name] = $_POST[$field_name];
		}
		$cpn_filter=get_option("mkto_cpn_filter");
		if($cpn_filter)
			$fields_to_submit[get_option('mkto_cpn_filter')]=$_POST['_wp_http_referer'];
		$request_data["action"] = "createOrUpdate";
		$request_data["lookupfield"] = get_option('mkto_lookup_field');
		$request_data["input"]=array($fields_to_submit);
		$request_action = "/v1/leads.json";
	
		$rest_call = generate_call($request_action,$marketo_token);
		
		return make_rest_call($rest_call,$request_data);
	}
	
	
	function marketo_assign_associate_lead($leadId,$marketo_token){
		$request_action = "/v1/leads/".$leadId."/associate.json";
		//$cookie_id = "&cookie=" . str_replace("&","%26",$_COOKIE["_mkto_trk"]);
		$cookie_id = "&cookie=" . str_replace("&","%26",$_COOKIE["_mkto_trk"]);
		$rest_call = generate_call($request_action,$marketo_token,true,$cookie_id);
		make_rest_call($rest_call);
		
	}
	function marketo_add_to_campaign($leadId,$marketo_token){
		$campaignId = $_POST["cpnid"];
		$request_action =  "/v1/campaigns/".$campaignId ."/trigger.json";
		$rest_call = generate_call($request_action,$marketo_token);
		
		$request_data = array('input'=>array('leads'=>array(array("id"=>$leadId))));

		return make_rest_call($rest_call,$request_data);
	}

	
	
?>