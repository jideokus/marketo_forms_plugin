<?php
	
	function add_form_quicktags(){
		if (wp_script_is('quicktags')){
		?>		
			<script type="text/javascript">
				QTags.addButton( 'mkto_form_shortcode', 'Marketo Form', 'run_form_shortcode_builder');
				
				function run_form_shortcode_builder(){
					alert("this is a test");
				}
			</script>
		<?php }
	}
	add_action('admin_print_footer_scripts', 'add_form_quicktags');

	
?>