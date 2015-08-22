<?php
// create custom plugin settings menu
add_action('admin_menu', 'marketo_forms_create_menu');

function marketo_forms_create_menu() {

	//create new top-level menu
	add_menu_page('Marketo Forms Settings', 'Marketo Forms Settings', 'administrator', 'marketo-form-settings', 'marketo_forms_settings_page',plugins_url('/images/settings.png', __FILE__));
	
	
	//call register settings function
	add_action( 'admin_init', 'register_marketo_form_settings' );
}

function register_marketo_form_settings() {
	//register our settings
	register_setting( 'marketo-forms-settings-group', 'mkto_rest_endpoint' );
	register_setting( 'marketo-forms-settings-group', 'mkto_auth_identity' );
	register_setting( 'marketo-forms-settings-group', 'mkto_auth_client_id' );
	register_setting( 'marketo-forms-settings-group', 'mkto_auth_client_secret' );
	register_setting( 'marketo-forms-settings-group', 'mkto_lookup_field' );
	register_setting( 'marketo-forms-settings-group', 'mkto_cpn_filter' );
}

function marketo_forms_settings_page() {
?>
<div class="wrap">
<h2>Marketo Forms Configuration</h2>

<form method="post" action="options.php">
    <?php settings_fields( 'marketo-forms-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
			<th scope="row">REST API Endpoint</th>
			<td><input type="text" name="mkto_rest_endpoint" value="<?php echo get_option('mkto_rest_endpoint'); ?>" /></td>
        </tr>
		<tr valign="top">
			<th scope="row">Marketo REST Identity</th>
			<td><input type="text" name="mkto_auth_identity" value="<?php echo get_option('mkto_auth_identity'); ?>" /></td>
        </tr>
		<tr valign="top">
			<th scope="row">Marketo Client ID</th>
			<td><input type="text" name="mkto_auth_client_id" value="<?php echo get_option('mkto_auth_client_id'); ?>" /></td>
        </tr>
		<tr valign="top">
			<th scope="row">Marketo Client Secret</th>
			<td><input type="text" name="mkto_auth_client_secret" value="<?php echo get_option('mkto_auth_client_secret'); ?>" /></td>
        </tr>
		<tr valign="top">
			<th scope="row">Lookup Field</th>
			<td><input type="text" name="mkto_lookup_field" value="<?php echo get_option('mkto_lookup_field'); ?>" /></td>
        </tr>
		<tr valign="top">
			<th scope="row">Campaign Filter Field</th>
			<td><input type="text" name="mkto_cpn_filter" value="<?php echo get_option('mkto_cpn_filter'); ?>" /></td>
        </tr>
    </table>
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div>
<?php } ?>