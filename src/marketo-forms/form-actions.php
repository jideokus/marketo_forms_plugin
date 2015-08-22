<?php
	function get_marketo_token(){
		$rest_call = get_option('mkto_auth_identity') . '/oauth/token?grant_type=client_credentials&client_id='.get_option('mkto_auth_client_id').'&client_secret=' . get_option('mkto_auth_client_secret');
		
		return make_rest_call($rest_call)->access_token;
		
	}
	function generate_call($request_action,$marketo_token,$token_start=true,$cookie_id=""){
		if($token_start)
			$token_operator = "?";
		else
			$token_operator = "&";
		return get_option('mkto_rest_endpoint') .$request_action .$token_operator. 'access_token=' . $marketo_token . $cookie_id;
	}
	function make_rest_call($rest_call,$request_data=NULL,$method="post"){
		
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
?>