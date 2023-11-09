<?php

if ( BM_Helper::is_rest() !== true ) {
	add_action( 'init', 'init_bm_calculation' );
}
/**
 * Initialize all calculation hooks
 *
 * @return void
 */
function init_bm_calculation() {
	$calculation = new BM_Calculation();
	add_action( 'woocommerce_before_calculate_totals', array( $calculation, 'set_cart_price' ), 20, 1 );
	add_filter( 'woocommerce_cart_item_price', array( $calculation, 'set_cart_item_price' ), 10, 3 );
	do_action( 'bm_calculation_init', $calculation );

	/* filter for WooCommerce Product Addons */
	if ( is_plugin_active( 'woocommerce-product-addons/woocommerce-product-addons.php' ) ) {
		add_filter('woocommerce_addons_addon_cart_price', function( $price, $cart_item ) {
			$base_prices = BM_Calculation::get_available_addon_prices( floatval( $price ), $cart_item['product_id'] );
			$price       = min( $base_prices );

			return $price;
		}, 10, 2 );

		add_filter( 'woocommerce_product_addons_show_grand_total', true );
	}

	/* filter for German Market uid */
	add_filter( 'wcvat_woocommerce_billing_fields_vat_default', function( $default ) {
		$user_id = get_current_user_id();
		return get_user_meta( $user_id, 'b2b_uid', true );
	} );

	add_filter( 'woocommerce_variable_sale_price_html', 'BM_Helper::bm_from_price_without_b2b', 10, 2 );
	add_filter( 'woocommerce_variable_price_html', 'BM_Helper::bm_from_price_without_b2b', 10, 2 );
}

/* init whitelist hooks */
if ( BM_Helper::is_rest() !== true ) {
	add_action( 'init', 'init_bm_whitelist' );
}

/**
 * Initialize all whitelist hooks
 *
 * @return void
 */
function init_bm_whitelist() {

	$whitelist = new BM_Whitelist();

	/* whitelist hooks */
	$whitelist_hooks = get_option( 'deactivate_whitelist_hooks' );
	$whitelist_admin = get_option( 'deactivate_whitelist_admin' );

	if ( ! isset( $whitelist_hooks ) || empty( $whitelist_hooks ) || 'off' === $whitelist_hooks ) {
		if ( ! current_user_can( 'administrator' ) ) {
			add_action( 'woocommerce_product_query', array( $whitelist, 'set_whitelist' ) );
			add_action( 'template_redirect', array( $whitelist, 'redirect_based_on_whitelist' ) );
			add_filter( 'pre_get_posts', array( $whitelist, 'set_search_whitelist' ) );
			add_filter( 'woocommerce_related_products', array( $whitelist, 'set_related_whitelist' ), 10, 3 );
			add_filter( 'woocommerce_product_get_upsell_ids', array( $whitelist, 'set_upsell_whitelist' ), 10, 2 );
			add_filter( 'woocommerce_product_get_cross_sell_ids', array( $whitelist, 'set_upsell_whitelist' ), 10, 2 );

			if ( has_filter( 'woocommerce_products_widget_query_args' ) ) {
				add_filter( 'woocommerce_products_widget_query_args', array( $whitelist, 'set_widget_whitelist' ), 10, 1 );
			}
			add_filter( 'woocommerce_shortcode_products_query', array( $whitelist, 'set_widget_whitelist' ), 10, 1 );
		}
	}

	do_action( 'bm_whitelist_init', $whitelist );
}

/**
 * Initialize all tax hooks
 *
 * @return void
 */
if ( BM_Helper::is_rest() !== true ) {
	$tax = new BM_Tax();

	if ( ! is_admin() ) {
		add_filter( 'pre_option_woocommerce_tax_display_shop', array( $tax, 'filter_tax_display' ), 10, 1 );
		add_filter( 'pre_option_woocommerce_tax_display_cart', array( $tax, 'filter_tax_display' ), 10, 1 );
		add_filter( 'woocommerce_get_variation_prices_hash', array( $tax, 'tax_display_add_hash_user_id' ) );
	}
}
