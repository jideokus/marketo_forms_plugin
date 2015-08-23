<?php
	

	function marketo_form_button() {
		
			die("adding script");
			add_filter( 'mce_buttons', 'register_marketo_button' );
			add_filter( 'mce_external_plugins', 'add_marketo_plugin_js' );
		
	}

	function register_marketo_button( $buttons ) {
		 array_push( $buttons, "-marketo-form");
		 return $buttons;
	}

	function add_marketo_plugin_js( $plugin_array ) {
		
		 $plugin_array['marketo_form_script'] = plugins_url( 'js/marketo-form-button.js', __FILE__ ) ;
		 return $plugin_array;
	}
?>