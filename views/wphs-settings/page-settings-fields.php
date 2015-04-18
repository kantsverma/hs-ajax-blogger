<?php
/*
 * Basic Section
 */
?>

<?php if ( 'wpps_field-example1' == $field['label_for'] ) : ?>

	<input id="<?php esc_attr_e( 'wphs_settings[basic][wphs_noof_blog]' ); ?>" name="<?php esc_attr_e( 'wphs_settings[basic][wphs_noof_blog]' ); ?>" class="" value="<?php esc_attr_e( $settings['basic']['wphs_noof_blog'] ); ?>" />
	<p class="description">Set numeric value i.e 10 by default it will be 5</p>
 
<?php endif; ?>


<?php
/*
 * Advanced Section
 */
?>

<?php if ( 'wphs_ajax_option' == $field['label_for'] ) : ?>

	<select id="<?php esc_attr_e( 'wphs_settings[advanced][wphs_ajax_option]' ); ?>" name="<?php esc_attr_e( 'wphs_settings[advanced][wphs_ajax_option]' ); ?>" class="regular-text">
		<?php 
		$optionArr = array('Normal Blog','Ajax Blog');
		foreach($optionArr as $okey=>$ovalue): 
			if($settings['advanced']['wphs_ajax_option'] == $okey){ ?>
				<option value="<?php echo $okey; ?>" selected="selected"><?php echo $ovalue; ?></option>
		<?php } else { ?>		
				<option value="<?php echo $okey; ?>"><?php echo $ovalue; ?></option>	
		<?php } endforeach;?>
	</select>
	<p class="description">Here you can change the blogger page loading type from ajax to normal.</p>
<?php elseif ( 'wphs_shortcode_field' == $field['label_for'] ) : ?>

<p> This is the shortcode to shwo the Blog Content : [wphs-shortcode-blog] </p>


	<p>Another example</p>

<?php endif; ?>
