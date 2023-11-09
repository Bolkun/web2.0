<?php
/**
 * Private: Role Based Prices for WooCommerce
 * Uninstalling Options
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$bm_options = array(
	'bm_global_base_price',
	'bm_global_base_price_type',
	'bm_global_bulk_prices',
	'bm_global_price_label',
	'bm_global_discount_message',
	'bm_global_discount_message_background_color',
	'bm_global_discount_message_font_color',
	'bm_addon_shipping_and_payment',
	'bm_addon_slack',
	'bm_addon_import_and_export',
	'bm_double_opt_in_customer_registration',
	'bm_addon_registration',
);

foreach ( $bm_options as $option ) {
	delete_option( $option );
}
