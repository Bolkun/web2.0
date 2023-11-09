<?php
/**
 * Plugin Name:  Slack Connector
 * Description:  Connect WordPress and Slack - with the Slack Connector. The plugin offers you and your team all conditions to always be informed about all activities on your blog, shop and forum. And to perfectly optimize your processes. 
 * Version:      1.0
 * Author:       MarketPress
 * Author URI:   https://marketpress.com
 * Text Domain:  slack-connector
 * Domain Path:  /languages
 * Licence:      GPLv3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

// Define needed constants
define( 'MPSC_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'MPSC_PLUGIN_PATH', untrailingslashit( ( plugin_dir_path( __FILE__ ) ) ) );
define( 'MPSC_BASEFILE', plugin_basename( __FILE__ ) );
define( 'MPSC_APPLICATION_DIR', untrailingslashit( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'application' );
define( 'MPSC_MODULE_DIR', MPSC_APPLICATION_DIR . DIRECTORY_SEPARATOR . 'modules' );

/**
 * Loads all the files and registers all actions and filters
 *
 * @wp-hook	plugins_loaded
 * @return	void
 */
function slack_connector_init() {

	// Set the directory
	$application_directory = MPSC_APPLICATION_DIR . DIRECTORY_SEPARATOR;

	// Load textdomain
	add_action( 'init', 'slack_connector_load_text_domain' );

	// Get general functions
	require_once( $application_directory . 'general.php' );

	// Load ajax actions
	if ( is_admin() ) {
		add_action( 'wp_ajax_slack_connector_test_notification', 'slack_connector_test_notification' );
	}

	// Load only if necessary
	if ( slack_connector_is_admin() ) {

		// Load styles and scripts in admin
		require_once( $application_directory . 'styles.php' );
		require_once( $application_directory . 'scripts.php' );

		add_action( 'admin_enqueue_scripts', 'slack_connector_admin_enqueue_styles' ); 
		add_action( 'admin_enqueue_scripts', 'slack_connector_admin_enqueue_scripts' );

	}

	// Load modules
	require_once( $application_directory . 'modules.php' );
	do_action( 'slack_connector_load_modules' );

	// Register custom post type
	require_once( $application_directory . 'custom-post-type.php' );
	add_action( 'init', 'slack_connector_register_custom_post_type' );

	// Init modules
	do_action( 'slack_connector_init_module' );

}

/**
* Load text domain
*
* @since 1.0
* @static
* @hook init
* @return void
*/	
function slack_connector_load_text_domain() {
	load_plugin_textdomain( 'slack-connector', FALSE, untrailingslashit( dirname( plugin_basename( __FILE__) ) ) . DIRECTORY_SEPARATOR . 'languages' );
}

add_action( 'plugins_loaded', 'slack_connector_init' );
