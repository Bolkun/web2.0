<?php

if ( ! class_exists( 'MarketPress_Improve_Plugin' ) ) {
	require_once( 'abstracts' . DIRECTORY_SEPARATOR . 'abstract-class-marketpress-improve-plugin.php' );
}

class MarketPress_Improve_B2B_Market extends MarketPress_Improve_Plugin {

	/**
	 * Get Plugin Name
	 *
	 * @since 	1.0
	 * @return 	String
	 */
	final protected function get_plugin_name() {
		return 'B2B Market';
	}

	/**
	 * Get Plugin Slug
	 *
	 * @since 	1.0
	 * @return 	String
	 */
	final protected function get_plugin_slug() {
		return 'b2b-market';
	}

	/**
	 * Get Plugin Version
	 *
	 * @since 	1.0
	 * @return 	String
	 */
	final protected function get_plugin_version() {
		return BM::$version;
	}

	/**
	 * Get Plugin Data
	 *
	 * @since 	1.0
	 * @return 	Array
	 */
	final protected function get_plugin_data() {
		
		$data = array();

		// Add-Ons
		$add_on_files = array(
			'bm_addon_shipping_and_payment',
			'bm_addon_import_and_export',
			'bm_addon_registration',
			'bm_addon_slack',
			'bm_addon_quantities',
		);

		foreach ( $add_on_files as $add_on ) {
			$data[ 'Add-On ' . $add_on ] = get_option( $add_on, 'off' );
		}

		return $data;
	}

}
