<?php
	
	add_action('admin_menu', 'whitepapers_create_menu');
	
	$categories = array();
		
	
	function whitepapers_create_menu() {
		add_submenu_page('marketo-form-settings', 'Whitepapers Settings', 'Whitepapers Settings','administrator', 'whitepapers_settings', 'whitepapers_settings_page');
		//call register settings function
		add_action( 'admin_init', 'register_whitepapers_settings' );
	}
	function register_whitepapers_settings() {
	//register our settings
		register_setting( 'whitepapers-settings-group', 'whitepaper_global_cpn' );
		register_setting( 'whitepapers-settings-group', 'whitepaper_global_form' );
		global $categories;
		$categories = get_terms( 'whitepaper-category', 'orderby=count&hide_empty=0' );
		foreach($categories as $cat){
			$options = get_whitepaper_cat_option_name($cat->term_id);
			$cat_option_cpn_id = $options["cpn"];
			$cat_option_form_id = $options["form"];
			
			register_setting('whitepapers-settings-group',$cat_option_cpn_id);
			register_setting('whitepapers-settings-group',$cat_option_form_id);
			//add_action('delete_term','whitepaper_delete_options',10,1);
		}
		/*function whitepaper_delete_options($term, $tt_id, $taxonomy,$deleted_term){
			$options = get_whitepaper_cat_option_name($term);
			delete_option($options["form"]);
			delete_option($options["cpn"]);
		}*/
	}

	
	function whitepapers_settings_page() {
		global $forms;
		global $categories;
		$categories = get_terms( 'whitepaper-category', 'orderby=count&hide_empty=0' );
	?>
		<div class="wrap">
		<h2>Whitepapers Configuration</h2>

		<form method="post" action="options.php">
			<?php settings_fields( 'whitepapers-settings-group' ); ?>
			<table class="form-table">
				<tr>
					<td colspan="2"><h2>Global Level Marketo Integration</h2></td>
				</tr>
				
				<tr valign="top">
					<th scope="row">Global Campaign ID</th>
					<td><input type="text" name="whitepaper_global_cpn" value="<?php echo get_option('whitepaper_global_cpn'); ?>" /></td>
				
					<th scope="row">Global Form</th>
					<td><?php echo get_marketo_forms('whitepaper_global_form');?></td>
				</tr>
				<tr>
					<td colspan="2"><h2>Category Level Marketo Integration</h2></td>
				</tr>
				<?php 
				foreach($categories as $cat){
					$options = get_whitepaper_cat_option_name($cat->term_id);
					$cat_option_cpn_id = $options["cpn"];
					$cat_option_form_id = $options["form"];
				?>
					<tr>
					<td colspan="2"><h3><?php echo $cat->name;?></h3></td>
				</tr>
					<tr valign="top" class="setting_cat_row">
						<th scope="row">Campaign ID</th>
						<td><input type="text" name="<?php echo $cat_option_cpn_id;?>" value="<?php echo get_option($cat_option_cpn_id);?>" /></td>
						<th scope="row">Form</th>
						<td><?php echo get_marketo_forms($cat_option_form_id);?></td>
					</tr>
				<?php
				}?>
			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>

		</form>
		</div>
	<?php }
	
?>