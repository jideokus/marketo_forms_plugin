<?php

add_action('admin_menu', 'tsn_mkto_create_options');

function tsn_mkto_create_options() {
	add_submenu_page('edit.php?post_type=tsn_mkto_form', 'Marketo Forms Settings', 'Settings','manage_options', 'tsn-mkto-form-settings', 'tsn_mkto_display_settings');
	add_action( 'admin_init', 'tsn_mkto_register_settings' );
}

function tsn_mkto_display_settings() {
?>
	<div class="wrap">
	<h2>Marketo Forms Settings</h2>

		<form method="post" action="options.php">
			<?php settings_fields( 'tsn-mkto-settings-group' ); ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php echo _e('REST API Endpoint')?></th>
					<td><input type="text" name="tsn_mkto_setting_rest_endpoint" value="<?php echo get_option('tsn_mkto_setting_rest_endpoint'); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php echo _e('Marketo REST Identity')?></th>
					<td><input type="text" name="tsn_mkto_setting_rest_id" value="<?php echo get_option('tsn_mkto_setting_rest_id'); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php echo _e('Marketo Client ID')?></th>
					<td><input type="text" name="tsn_mkto_setting_client_id" value="<?php echo get_option('tsn_mkto_setting_client_id'); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php echo _e('Marketo Client Secret')?></th>
					<td><input type="text" name="tsn_mkto_setting_client_secret" value="<?php echo get_option('tsn_mkto_setting_client_secret'); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php echo _e('Lookup Field')?></th>
					<td><input type="text" name="tsn_mkto_setting_lookup_field" value="<?php echo get_option('tsn_mkto_setting_lookup_field'); ?>" /></td>
				</tr>
			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>

		</form>
	</div>
<?php }

function tsn_mkto_register_settings() {
	//register our settings
	register_setting( 'tsn-mkto-settings-group', 'tsn_mkto_setting_rest_endpoint' );
	register_setting( 'tsn-mkto-settings-group', 'tsn_mkto_setting_rest_id' );
	register_setting( 'tsn-mkto-settings-group', 'tsn_mkto_setting_client_id' );
	register_setting( 'tsn-mkto-settings-group', 'tsn_mkto_setting_client_secret' );
	register_setting( 'tsn-mkto-settings-group', 'tsn_mkto_setting_lookup_field' );
}


