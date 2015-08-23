<?php
	function tsn_mkto_get_token(){
		if(!get_option('tsn_mkto_setting_rest_id')||!get_option('tsn_mkto_setting_client_id')){
			return false;
		}
		$rest_call = get_option('tsn_mkto_setting_rest_id') . '/oauth/token?grant_type=client_credentials&client_id='.get_option('tsn_mkto_setting_client_id').'&client_secret=' . get_option('tsn_mkto_setting_client_secret');
		
		return tsn_mkto_make_rest_call($rest_call)->access_token;
		
	}
	function tsn_mkto_generate_call($request_action,$marketo_token,$token_start=true,$cookie_id=""){
		if($token_start)
			$token_operator = "?";
		else
			$token_operator = "&";
		return get_option('tsn_mkto_setting_rest_endpoint') .$request_action .$token_operator. 'access_token=' . $marketo_token . $cookie_id;
	}
	function tsn_mkto_make_rest_call($rest_call,$request_data=NULL,$method="post"){
		
		$curl = curl_init($rest_call);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if($method=="post")
			curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		if(isset($request_data)){
			curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request_data));		
		}
		$response = curl_exec($curl);
		$errors = curl_error($curl);
		curl_close($curl);
		return json_decode($response);
	}
	function tsn_mkto_get_fields(){
		$token = tsn_mkto_get_token();
		$request_action = '/v1/leads/describe.json';
		$rest_call = tsn_mkto_generate_call($request_action,$token);
		$call_method='get';
		$call_data = NULL;
		$response_from_call = tsn_mkto_make_rest_call($rest_call,$call_data,$call_method);
		if($response_from_call->success!=1){
			return false;
		}else{
			$all_data_from_marketo = $response_from_call->result;
			$data_to_return = array();
			foreach($all_data_from_marketo as $field){
				$data_to_return[$field->rest->name]=$field->displayName;
			}
			asort($data_to_return);
			return $data_to_return;
		}
	}
	function tsn_mkto_show_field($field,$value="",$show_label){
		
		$field_element = '<div class="tsn-mkto-form-field">';
		if($show_label){
			$field_element.= '<label for="'.$field["field_name"].'">'.$field["field_label"].'</label>';
		}
		$options_list = explode(",",$field["field_options"]);
		$validation = $field["field_validation"];
		$add_number_rule = false;
		$option_status ="";
		if($field["field_type"]==TSN_MKTO_FIELD_RADIO || $field["field_type"]==TSN_MKTO_FIELD_CHECKBOX)
			$option_status="checked";
		else if($field["field_type"]==TSN_MKTO_FIELD_DROPDOWN)
			$option_status="selected";
			
		switch ($field["field_type"]){
			case TSN_MKTO_FIELD_TEXT:
				if(!$show_label){
					$no_label_text = ' placeholder="'.$field['field_label'] .'"';
				}
				$field_element.= '<input type="' . $field["field_validation"] .'" name="' . $field["field_name"] . '" '. $field["field_required"] . ' value="'.$value.'" '
									.$no_label_text .'/>';
				break;
			case TSN_MKTO_FIELD_TEXTAREA:
				if(!$show_label){
					$no_label_text = ' placeholder="'.$field['field_label'] .'"';
				}
				$field_element.= '<textarea name="' . $field["field_name"] . '" '.$field["field_required"]. $no_label_text.'>'.$value.'</textarea>';
				break;
			case TSN_MKTO_FIELD_DROPDOWN:
				
				$field_element.= '<select name="'. $field["field_name"] . '">';
				if(!$show_label){
					$field_element.= '<option value="" disabled selected>'.$field['field_label'] .'</option>';
				}
				foreach($options_list as $option){
						$field_element.='<option value="'. $option .'" ';
						if($value==$option)
							$field_element.=$option_status;
						$field_element.='>' . $option .'</option>';
				}	
				$field_element.= '</select>';
				break;
			case TSN_MKTO_FIELD_RADIO:
				foreach($options_list as $option){
					$field_element .='<span><input value='.$option.' type="radio" name="'.$field["field_name"] .'" '.$option_status .'/>'.$option.'</span>';
				}
			case TSN_MKTO_FIELD_CHECKBOX:
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
	function tsn_mkto_get_forms($field_name,$field_value=null){
		$forms = get_posts(array('post_type'=>'tsn_mkto_form'));
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
		wp_reset_query();
		return $forms_field;
	}
	
	function tsn_mkto_show_form($form_id,$cpn_id,$form_title="",$post_reg_action="",$target="",$show_labels=true){
		
		$post = get_post($form_id);
		$form_display = "";		
		
		if($button_text==""){
			$button_text='Submit';
		}
		if ($post && $post->post_type=='tsn_mkto_form'){
						
			$fields = $post->form_field;
			if($_COOKIE[TSN_MKTO_MUNCHKIN_COOKIE]){
				$lead_data=get_lead_data($fields);
			}
								
			$form_display.= '<form class = "tsn_mkto_form" method="POST" action="#">';
				if(!$popup & $form_title)
					$form_display.='<h3>'.$form_title.'</h3>';
				foreach($fields as $field){
					if($lead_data)
						$form_display.=tsn_mkto_show_field($field,$lead_data[$field["field_name"]],$show_labels);
					else
						$form_display.=tsn_mkto_show_field($field,"",$show_labels);
				}
			
				$form_display.='<input class="tsn-mkto-form-submit" type="submit" value="'. __($post->submit_text?$post->submit_text:'Submit') .'"/>';
				$form_display.= wp_nonce_field('tsn_mkto_form_submit','tsn_mkto_form_submit_nonce');
				$form_display.= '<input type="hidden" name="tsn_mkto_form_id" value="'.$form_id . '"/>';
				$form_display.='<input type="hidden" name="tsn_mkto_cpnid" value="'.$cpn_id . '"/>';
				$form_display.='<input type="hidden" name="tsn_mkto_post_action" value="'.$post_reg_action . '"/>';
			$form_display.="</form>";
			
			$clicked_link="";
			
				
				
			$form_display.='<script>
								jQuery(document).ready(function(){
									jQuery("#'.$button_id.'").click(function(){
										Munchkin.munchkinFunction("clickLink", { href: "'. $clicked_link.'" }); 
									});
									
								});
							</script>';
			return $form_display;
		}else{
			echo '<h3>';
			echo _e('This form does not exist');
			echo '</h3>';
		}	
	}
	function tsn_mkto_get_lead_data($fields){
		
		$field_names = $lead_data = array();
	
		foreach($fields as $field){
			array_push($field_names,$field["field_name"]);
		}
		$marketo_token = tsn_mkto_get_token();
		$request_action="/v1/leads.json?filterType=cookie&filterValues=" . str_replace("&","%26",$_COOKIE[TSN_MKTO_MUNCHKIN_COOKIE]) . "&fields=" . implode(",",$field_names);
		$rest_call = tsn_mkto_generate_call($request_action,$marketo_token,false);
		$lead_data = tsn_mkto_make_rest_call($rest_call,null,"get")->result[0];
		
		if($lead_data){
			$lead_data = get_object_vars($lead_data);	
		}
		return $lead_data;
	}
	
	//If you want to check if this person exists in Marketo, use this function and you're good to go
	function is_tsn_mkto_form_required($form_id){
	
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
	function tsn_mkto_update_lead($marketo_token){
		$form = get_post($_POST['tsn_mkto_form_id']);
		$fields = $form->form_field;
		$fields_to_submit=array();
		foreach($fields as $field){
			$field_name = $field["field_name"];
			$fields_to_submit[$field_name] = $_POST[$field_name];
		}
		
		$request_data["action"] = "createOrUpdate";
		$request_data["lookupfield"] = get_option('tsn_mkto_setting_lookup_field');
		$request_data["input"]=array($fields_to_submit);
		$request_action = "/v1/leads.json";
	
		$rest_call = tsn_mkto_generate_call($request_action,$marketo_token);
		
		return tsn_mkto_make_rest_call($rest_call,$request_data);
	}
	
	
	function tsn_mkto_assign_associate_lead($leadId,$marketo_token){
		$request_action = "/v1/leads/".$leadId."/associate.json";
		$cookie_id = "&cookie=" . str_replace("&","%26",$_COOKIE["_mkto_trk"]);
		$rest_call = tsn_mkto_generate_call($request_action,$marketo_token,true,$cookie_id);
		tsn_mkto_make_rest_call($rest_call);
		
	}
	function tsn_mkto_add_to_campaign($leadId,$marketo_token){
		$campaignId = $_POST["cpnid"];
		$request_action =  "/v1/campaigns/".$campaignId ."/trigger.json";
		$rest_call = tsn_mkto_generate_call($request_action,$marketo_token);
		
		$request_data = array('input'=>array('leads'=>array(array("id"=>$leadId))));

		return tsn_mkto_make_rest_call($rest_call,$request_data);
	}
?>