<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2><?php esc_html_e( WPHS_NAME ); ?> Settings</h2>
	<form method="post" action="options.php">
		<?php settings_fields( 'wphs_settings' ); ?>
		<?php do_settings_sections( 'wphs_settings' ); ?>

		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
		</p>
	</form>
</div> <!-- .wrap -->
