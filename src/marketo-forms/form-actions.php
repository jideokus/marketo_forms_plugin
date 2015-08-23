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
?>