<?php
/**
 * Feature Name:  Slack Connector - WooCommerce Module
 * Version:       1.0
 * Author:        MarketPress
 * Author URI:    https://marketpress.com
 * Licence:       GPLv3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Load WooCommerce module
 *
 * @wp-hook	slack_connector_init_module
 * @return	void
 */
function slack_connector_woocommerce_init() {

	// WooCommerce has to be activated
	if ( slack_connector_is_plugin_activated( 'woocommerce/woocommerce.php' ) ) {

		// Module directory
		$module_application_directory = untrailingslashit( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'application';

		// Load general module functions
		require_once( $module_application_directory . DIRECTORY_SEPARATOR . 'general.php' );

		if ( slack_connector_is_admin() ) {

			// Meta Boxes
			require_once( $module_application_directory . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'meta-boxes.php' );
			add_action( 'slack_connector_meta_boxes', 'slack_connector_meta_boxes_woocommerce' );

			// Save Post
			add_action( 'slack_connector_save_post', 'slack_connector_save_post_woocommerce', 10, 1 );

		}

		// Init the module with actions and filters

		// New Order / Product Sale / Product Category Sale
		add_action( 'woocommerce_checkout_order_processed', 'slack_connector_woocommerce_checkout_order_processed', 10, 2 );

		// Low Stock
		add_action( 'woocommerce_low_stock', 'slack_connector_woocommerce_low_stock', 10, 1 );

		// Out of Stock
		add_action( 'woocommerce_no_stock', 'slack_connector_woocommerce_no_stock', 10, 1 );

		// New Customer
		add_action( 'woocommerce_created_customer', 'slack_connector_woocommerce_new_customer', 10, 3 );

		// New Review
		add_action( 'comment_post', 'slack_connector_woocommerce_new_review', 10, 3 ); // 3rd parameter since WordPress 4.5


	}

} 

add_action( 'slack_connector_init_module', 'slack_connector_woocommerce_init' );
