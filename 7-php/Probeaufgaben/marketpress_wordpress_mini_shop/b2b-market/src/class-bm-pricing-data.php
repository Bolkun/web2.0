<?php

/**
 * Class which handles the getter and setter for pricing data
 */
class BM_Pricing_Data {

	/**
	 * Get global base price
	 *
	 * @return void
	 */
	public static function get_global_base_price() {
		$base_price_value = get_option( 'bm_global_base_price' );
		$base_price_type  = get_option( 'bm_global_base_price_type' );

		$base_price = array( $base_price_type => $base_price_value );

		if ( isset( $base_price ) && ! empty( $base_price ) ) {
			return $base_price;
		}
	}

	/**
	 * Get global bulk price
	 *
	 * @return void
	 */
	public static function get_global_bulk_price() {
		$bulk_prices = get_option( 'bm_global_bulk_prices' );

		if ( isset( $bulk_prices ) && ! empty( $bulk_prices ) ) {
			return $bulk_prices;
		}
	}

	/**
	 * Get group base price
	 *
	 * @param int $group
	 * @return void
	 */
	public static function get_group_base_price( $group ) {
		$base_price_value = get_post_meta( $group, 'bm_price', true );
		$base_price_type  = get_post_meta( $group, 'bm_price_type', true );

		if ( ! empty( $base_price_value ) && ! empty( $base_price_type ) ) {
			$base_price = array( $base_price_type => $base_price_value );
			return $base_price;
		}
	}

	/**
	 * Get group bulk price
	 *
	 * @param int $group
	 * @return void
	 */
	public static function get_group_bulk_price( $group ) {
		$bulk_prices = get_post_meta( $group, 'bm_bulk_prices' );

		if ( isset( $bulk_prices ) && ! empty( $bulk_prices ) ) {
			return $bulk_prices[0];
		}
	}

	/**
	 * Get all product status
	 *
	 * @param int $group
	 * @return void
	 */
	public static function get_group_global_status( $group ) {
		$status = get_post_meta( $group, 'bm_all_products', true );

		if ( isset( $status ) && ! empty( $status ) ) {
			return $status;
		}
	}

	/**
	 * Get product group price
	 *
	 * @param int $product_id
	 * @param int $group_id
	 * @return void
	 */
	public static function get_product_group_price( $product_id, $group_id ) {

		$product = wc_get_product( $product_id );
		$group   = get_post( $group_id );

		if ( is_null( $group ) ) {
			return;
		}

		$base_price_value = get_post_meta( $product_id, 'bm_' . $group->post_name . '_price', true );
		$base_price_type  = get_post_meta( $product_id, 'bm_' . $group->post_name . '_price_type', true );

		if ( ! empty( $base_price_value ) && ! empty( $base_price_type ) ) {
			$base_price = array( $base_price_type => $base_price_value );
			return $base_price;
		}
	}

	/**
	 * Get product bulk price
	 *
	 * @param int $product_id
	 * @return void
	 */
	public static function get_product_bulk_price( $product_id ) {

		$group_id     = BM_Conditionals::get_validated_customer_group();
		$group_object = get_post( $group_id );

		if ( is_null( $group_object ) ) {
			return;
		}
		$bulk_prices = get_post_meta( $product_id, 'bm_' . $group_object->post_name . '_bulk_prices' );
		if ( isset( $bulk_prices ) && ! empty( $bulk_prices ) ) {
			return $bulk_prices[0];
		}
	}
}
