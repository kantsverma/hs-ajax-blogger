<?php
/*
Plugin Name: HS Blogger Ajax Loader
Description: This plugins is specialy used to show the all blog post 
Version:     1.0
Author:      Kantsverma
Author URI:  http://kantsverma.tumblr.com
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

define( 'WPHS_NAME',                 'HS Ajax Blogger' );
define( 'WPHS_PLUGINS_URL',  plugins_url('hs-ajax-blogger') ); 			   //  define plugins url 
define( 'WPHS_PLUGINS_DIR',  WP_PLUGIN_DIR.'/hs-ajax-blogger' ); 			   //  define plugins url 

define( 'WPHS_REQUIRED_PHP_VERSION', '5.3' );                          // because of get_called_class()
define( 'WPHS_REQUIRED_WP_VERSION',  '3.1' );                          // because of esc_textarea()

/**
 * Checks if the system requirements are met
 *
 * @return bool True if system requirements are met, false if not
 */
function wphs_requirements_met() {
	global $wp_version;
	//require_once( ABSPATH . '/wp-admin/includes/plugin.php' );		// to get is_plugin_active() early

	if ( version_compare( PHP_VERSION, WPHS_REQUIRED_PHP_VERSION, '<' ) ) {
		return false;
	}

	if ( version_compare( $wp_version, WPHS_REQUIRED_WP_VERSION, '<' ) ) {
		return false;
	}

	/*
	if ( ! is_plugin_active( 'plugin-directory/plugin-file.php' ) ) {
		return false;
	}
	*/

	return true;
}

/**
 * Prints an error that the system requirements weren't met.
 */
function wphs_requirements_error() {
	global $wp_version;

	require_once( dirname( __FILE__ ) . '/views/requirements-error.php' );
}

/*
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets loaded if the plugin requirements are met. Otherwise older PHP installations could crash when trying to parse it.
 */
if ( wphs_requirements_met() ) {
	require_once( __DIR__ . '/classes/wphs-module.php' );
	require_once( __DIR__ . '/classes/wphs-blogger-ajax.php' );
	require_once( __DIR__ . '/includes/admin-notice-helper/admin-notice-helper.php' );
	require_once( __DIR__ . '/classes/wphs-custom-post-type.php' );
	require_once( __DIR__ . '/classes/wphs-main.php' );
	require_once( __DIR__ . '/classes/wphs-settings.php' );
	require_once( __DIR__ . '/classes/wphs-instance-class.php' );

	if ( class_exists( 'WordHS_Blogger' ) ) {
		$GLOBALS['wphs'] = WordHS_Blogger::get_instance();
		register_activation_hook(   __FILE__, array( $GLOBALS['wphs'], 'activate' ) );
		register_deactivation_hook( __FILE__, array( $GLOBALS['wphs'], 'deactivate' ) );
	}
} else {
	add_action( 'admin_notices', 'wphs_requirements_error' );
}
