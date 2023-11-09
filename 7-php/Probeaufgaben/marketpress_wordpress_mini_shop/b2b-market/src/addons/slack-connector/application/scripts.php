<?php
/**
 * Feature Name: Slack Connector - Backend Scripts
 * Version:      1.0
 * Author:       MarketPress
 * Author URI:   https://marketpress.com
 * Licence:      GPLv3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Loads admin scripts
 *
 * @wp-hook	admin_enqueue_scripts
 * @return	void
 */
function slack_connector_admin_enqueue_scripts() {

	// Init
	$script_dir 	= MPSC_PLUGIN_URL . '/application/assets/js/';
	$suffix 		= slack_connector_get_script_suffix();
	$plugin_data 	= get_plugin_data( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . MPSC_BASEFILE );
	$version     	= $plugin_data[ 'Version' ];
	$scripts 		= array();
	
	// Add select2 to scripts
	$scripts[ 'select2' ] = array(
		'src'       => $script_dir . 'select2' . $suffix . '.js',
		'deps'      => array( 'jquery' ),
		'version'   => $version,
		'in_footer' => TRUE
	);

	// Admin Script
	$scripts[ 'slack-connector-admin' ] = array(
		'src'       => $script_dir . 'admin' . $suffix . '.js',
		'deps'      => array( 'jquery', 'wp-color-picker' ),
		'version'   => $version,
		'in_footer' => TRUE
	);

	// Filter the scripts that are will be loaded
	$scripts = apply_filters( 'slack_connector_get_admin_scripts', $scripts );

	// Enqueue all scripts
	foreach ( $scripts as $handle => $script ) {
		
		// enqueue all scripts
		wp_enqueue_script(
			$handle,
			$script[ 'src' ],
			$script[ 'deps' ],
			$script[ 'version' ],
			$script[ 'in_footer' ]
		);

	}

	// Localize admin script for ajax test notification button
	wp_localize_script( 'slack-connector-admin', 'slack_connector_ajax',
        array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'slack-connector-ajax' ) )
	);

} 
