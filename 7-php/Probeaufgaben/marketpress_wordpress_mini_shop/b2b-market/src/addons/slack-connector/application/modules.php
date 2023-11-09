<?php
/**
 * Feature Name: Slack Connector - Modules
 * Version:      1.0
 * Author:       MarketPress
 * Author URI:   https://marketpress.com
 * Licence:      GPLv3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Loads all modules
 *
 * @wp-hook	slack_connector_load_modules
 * @return	void
 */
function slack_connector_load_modules() {

	// Init
	$module_files = array();

	$module_dir = @opendir( MPSC_MODULE_DIR );

	if ( $module_dir ) {
		while ( ( $file = readdir( $module_dir ) ) !== FALSE ) {

			// Skip the folders
			if ( substr( $file, 0, 1 ) == '.' )
				continue;
				
			// We only acceppt folder structures
			$module_files[ $file ] = MPSC_MODULE_DIR . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR . $file . '.php';
		}

		closedir( $module_dir );
	}

	// Include files if they exist
	foreach ( $module_files as $module_id => $module ) {

		if ( file_exists( $module ) ) {
			require_once $module;
		}
		
	}

} 

add_action( 'slack_connector_load_modules', 'slack_connector_load_modules' );
