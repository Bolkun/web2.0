<?php
/**
 * Feature Name: Slack Connector - Backend Styles
 * Version:      1.0
 * Author:       MarketPress
 * Author URI:   https://marketpress.com
 * Licence:      GPLv3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Loads admin syles
 *
 * @wp-hook	admin_enqueue_scripts
 * @return	void
 */
function slack_connector_admin_enqueue_styles() {

	// Init
	$styles_dir 	= MPSC_PLUGIN_URL . '/application/assets/css/';
	$suffix 		= slack_connector_get_script_suffix();
	$plugin_data 	= get_plugin_data( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . MPSC_BASEFILE );
	$version     	= $plugin_data[ 'Version' ];
	$styles 		= array();

	$styles[ 'select2' ] = array(
		'src'       => $styles_dir . 'select2' . $suffix . '.css',
		'deps'      => NULL,
		'version'   => $version,
		'media' 	=> NUll
	);

	$styles[ 'slack-connector-admin-style' ] = array(
		'src'       => $styles_dir . 'admin' . $suffix . '.css',
		'deps'      => NULL,
		'version'   => $version,
		'media'		=> NUll
	);

	// Filter the scripts that are will be loaded
	$scripts = apply_filters( 'slack_connector_get_admin_styles', $styles );

	foreach ( $styles as $key => $style ){
		wp_enqueue_style(
			$key,
			$style[ 'src' ],
			$style[ 'deps' ],
			$style[ 'version' ],
			$style[ 'media' ]
		);
	}

	// Color Picker
	wp_enqueue_style( 'wp-color-picker' );

}
