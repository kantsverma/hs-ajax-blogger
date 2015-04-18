<h3>WPHS User Fields</h3>

<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<label for="wphs_no-of-blog">No of Blos show</label>
		</th>

		<td>
			<input id="wphs_no-of-blog" name="wphs_no-of-blog" type="text" class="regular-text" value="<?php esc_attr_e( get_user_meta( $user->ID, 'wphs_no-of-blog', true ) ); ?>" />
			<span class="description">Example description.</span>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label for="wphs_ajax_option">Disable Ajax Blog</label>
		</th>
		<td>
			<?php esc_attr_e( get_user_meta( $user->ID, 'wphs_ajax_option', true ) ); ?>
			<select id="wphs_ajax_option" name="wphs_ajax_option" type="text" class="regular-text">
				<option value="ajax_blogger">Ajax Blog</option>
				<option value="normal_blogger">Normal Blog</option>
			</select>
			<span class="description">Here you can change the blogger page loading type from ajax to normal.</span>
		</td>
	</tr>
</table>
