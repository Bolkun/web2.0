<?php
/**
 * Class which handles all price calculations
 */
class BM_Calculation {

	/**
	 * Calculate group price
	 *
	 * @param int $product_id current product id.
	 * @return array
	 */
	public static function get_available_group_prices( $product_id ) {

		$prices      = array();
		$prices_meta = array();
		$group_id    = BM_Conditionals::get_validated_customer_group();

		$all_products = get_post_meta( $group_id, 'bm_all_products', true );
		$conditional  = new BM_Conditionals();

		$product = wc_get_product( $product_id );
		$price   = $product->get_price();

		$force_product_price = apply_filters( 'bm_force_product_price', false );
		$use_regular         = apply_filters( 'bm_use_regular_for_group_price', false );

		if ( true === $use_regular ) {
			$regular = get_post_meta( $product_id, '_regular_price', true );
			$price   = $regular;
		}

		if ( $product->is_type( apply_filters( 'bm_check_product_type', 'variation' ) ) ) {
			$product_id = $product->get_parent_id();
		}

		/* get prices */
		$group_price         = BM_Pricing_Data::get_group_base_price( $group_id );
		$product_group_price = BM_Pricing_Data::get_product_group_price( $product->get_id(), $group_id );
		$global_base_price   = BM_Pricing_Data::get_global_base_price();

		if ( is_array( $group_price ) && BM_Helper::is_array_empty( $group_price ) === false && ! is_null( $group_id ) ) {
			if ( $conditional->active_price_for_product( $product_id ) === true || $conditional->active_price_for_category( $product_id ) === true || 'on' === $all_products ) {
				$prices_meta[] = $group_price;
			}
		}
		if ( is_array( $product_group_price ) && BM_Helper::is_array_empty( $product_group_price ) === false && ! is_null( $group_id ) ) {
			if ( $conditional->active_price_for_product( $product_id ) === true || $conditional->active_price_for_category( $product_id ) === true || 'on' === $all_products) {
				$prices_meta[] = $product_group_price;
			}
		}

		if ( is_array( $global_base_price ) && BM_Helper::is_array_empty( $global_base_price ) === false ) {
			$prices_meta[] = $global_base_price;
		}

		foreach ( $prices_meta as $meta ) {
			$key   = key( $meta );
			$value = floatval( reset( $meta ) );

			switch ( $key ) {
				case 'fix':
					$b2b_price = $value;
					$prices[]  = $b2b_price;
					break;
				case 'discount':
					if ( $price ) {
						$b2b_price = $price - $value;
						$prices[]  = $b2b_price;
					}
					break;
				case 'discount-percent':
					if ( $price ) {
						$b2b_price = $price - ( $value * $price / 100 );
						$prices[]  = $b2b_price;
					}
					break;
			}
		}

		if ( false === $force_product_price || ! isset( $product_group_price ) ) {
			$prices[] = $price;
		}
		if ( true === $use_regular ) {
			$prices[] = $product->get_price();
		}

		return $prices;
	}

	/**
	 * Calculate bulk prices
	 *
	 * @param int $product_id current product id.
	 * @param int $quantity current given quantity.
	 * @return array
	 */
	public static function get_available_bulk_prices( $product_id, $quantity ) {

		$prices      = array();
		$prices_meta = array();

		$group_id = BM_Conditionals::get_validated_customer_group();

		if ( ! isset( $group_id ) || empty( $group_id ) ) {
			return;
		}

		$group      = get_post( $group_id );
		$group_slug = $group->post_name . '_';

		$conditional  = new BM_Conditionals();
		$all_products = get_post_meta( $group_id, 'bm_all_products', true );

		$product = wc_get_product( $product_id );

		if ( is_null( $group ) || false === $product ) {
			return;
		}

		$group_prices = self::get_available_group_prices( $product_id );
		$base_price   = min( $group_prices );
		$use_regular  = apply_filters( 'bm_use_regular_for_bulk_price', true );

		if ( true === $use_regular ) {
			$sale    = get_post_meta( $product_id, '_sale_price', true );
			$regular = get_post_meta( $product_id, '_regular_price', true );

			if ( isset( $sale ) && ! empty( $sale ) ) {
				$base_price = $sale;
			} else {
				$base_price = $regular;
			}
		}
		$product_id = $product->get_id();

		if ( $product->is_type( apply_filters( 'bm_check_product_type', 'variation' ) ) ) {
			$product_id = $product->get_parent_id();
		}

		/* bulk price data */
		$group_bulk_prices   = BM_Pricing_Data::get_group_bulk_price( $group_id );
		$product_bulk_prices = BM_Pricing_Data::get_product_bulk_price( $product->get_id() );
		$global_bulk_prices  = BM_Pricing_Data::get_global_bulk_price();

		if ( is_array( $group_bulk_prices ) && ! is_null( $group_id ) ) {
			if ( $conditional->active_price_for_product( $product_id ) === true || $conditional->active_price_for_category( $product_id ) === true || 'on' === $all_products ) {
				$prices_meta[] = $group_bulk_prices;
			}
		}

		if ( is_array( $product_bulk_prices ) && ! is_null( $group_id ) ) {
			if ( $conditional->active_price_for_product( $product_id ) === true || $conditional->active_price_for_category( $product_id ) === true || 'on' === $all_products ) {
				$prices_meta[] = $product_bulk_prices;
			}
		}

		if ( is_array( $global_bulk_prices ) ) {
			$prices_meta[] = $global_bulk_prices;
		}

		foreach ( $prices_meta as $bulks ) {

			foreach ( $bulks as $values ) {

				if ( isset( $values['bulk_price'] ) && 0 != $values['bulk_price'] ) {

					$bulk_price = floatval( $values['bulk_price'] );

					if ( isset( $values['bulk_price_from'] ) ) {
						$from = intval( $values['bulk_price_from'] );
					}
					if ( isset( $values['bulk_price_to'] ) ) {
						$to = intval( $values['bulk_price_to'] );
					} else {
						$to = 0;
					}
					if ( isset( $values['bulk_price_type'] ) ) {
						$type = $values['bulk_price_type'];
					}

					if ( ! isset( $type ) || is_null( $type ) ) {
						$type = 'fix';
					}

					if ( 0 == $to ) {
						$to = INF;
					}

					if ( ( $quantity >= $from ) && ( $quantity <= $to ) ) {

						switch ( $type ) {
							case 'fix':
								$price    = $bulk_price;
								$prices[] = $price;
								break;

							case 'discount':
								if ( $base_price > $bulk_price ) {
									$price    = $base_price - $bulk_price;
									$prices[] = $price;
								}
								break;

							case 'discount-percent':
								if ( $base_price ) {
									$price    = $base_price - ( $bulk_price * $base_price / 100 );
									$prices[] = $price;
								}
								break;
						}
					}
				}
			}
		}
		return $prices;

	}

	/**
	 * Calculate group price
	 *
	 * @param int $product_id current product id.
	 * @return array
	 */
	public static function get_available_addon_prices( $price, $product_id ) {

		$prices      = array();
		$prices_meta = array();
		$group_id    = BM_Conditionals::get_validated_customer_group();

		if ( ! isset( $group_id ) || empty( $group_id ) ) {
			return;
		}

		$all_products = get_post_meta( $group_id, 'bm_all_products', true );
		$conditional  = new BM_Conditionals();

		$product = wc_get_product( $product_id );

		if ( $product->is_type( apply_filters( 'bm_check_product_type', 'variation' ) ) ) {
			$product_id = $product->get_parent_id();
		}

		/* get prices */
		$group_price         = BM_Pricing_Data::get_group_base_price( $group_id );
		$product_group_price = BM_Pricing_Data::get_product_group_price( $product->get_id(), $group_id );
		$global_base_price   = BM_Pricing_Data::get_global_base_price();

		if ( is_array( $group_price ) && BM_Helper::is_array_empty( $group_price ) === false && ! is_null( $group_id ) ) {
			if ( $conditional->active_price_for_product( $product_id ) === true || $conditional->active_price_for_category( $product_id ) === true || 'on' === $all_products ) {
				$prices_meta[] = $group_price;
			}
		}
		if ( is_array( $product_group_price ) && BM_Helper::is_array_empty( $product_group_price ) === false && ! is_null( $group_id ) ) {
			if ( $conditional->active_price_for_product( $product_id ) === true || $conditional->active_price_for_category( $product_id ) === true || 'on' === $all_products) {
				$prices_meta[] = $product_group_price;
			}
		}

		if ( is_array( $global_base_price ) && BM_Helper::is_array_empty( $global_base_price ) === false ) {
			$prices_meta[] = $global_base_price;
		}

		foreach ( $prices_meta as $meta ) {
			$key   = key( $meta );
			$value = floatval( reset( $meta ) );

			switch ( $key ) {
				case 'fix':
					$b2b_price = $value;
					$prices[]  = $b2b_price;
					break;
				case 'discount':
					$b2b_price = $price - $value;
					$prices[]  = $b2b_price;
					break;
				case 'discount-percent':
					$b2b_price = $price - ( $value * $price / 100 );
					$prices[]  = $b2b_price;
					break;
			}
		}
		$prices[] = $price;
		return $prices;
	}

	/**
	 * Calculate cart price
	 *
	 * @param object $cart_object the current cart object.
	 * @return void
	 */
	public function set_cart_price( $cart_object ) {

		foreach ( $cart_object->get_cart() as $item ) {

			$prices   = array();
			$quantity = $item['quantity'];
			$product  = wc_get_product( $item['product_id'] );

			$group_id = BM_Conditionals::get_validated_customer_group();

			if ( ! isset( $group_id ) || empty( $group_id ) ) {
				return;
			}

			$tax_type = get_post_meta( $group_id, 'bm_tax_type', true );

			if ( $product->is_type( 'variable' ) ) {

				$base_prices = self::get_available_group_prices( $item['variation_id'] );
				$bulk_prices = self::get_available_bulk_prices( $item['variation_id'], $quantity );

				if ( isset( $base_prices ) && ! empty( $base_prices ) ) {
					foreach ( $base_prices as $base_price ) {
						$prices[] = floatval( $base_price );
					}
				}
				if ( isset( $bulk_prices ) && ! empty( $bulk_prices ) ) {
					foreach ( $bulk_prices as $bulk_price ) {
						$prices[] = floatval( $bulk_price );
					}
				}
				$cheapest_price = min( $prices );
			} else {
				$base_prices = self::get_available_group_prices( $product->get_id() );
				$bulk_prices = self::get_available_bulk_prices( $product->get_id(), $quantity );

				if ( isset( $base_prices ) && ! empty( $base_prices ) ) {
					foreach ( $base_prices as $base_price ) {
						$prices[] = floatval( $base_price );
					}
				}

				if ( isset( $bulk_prices ) && ! empty( $bulk_prices ) ) {
					foreach ( $bulk_prices as $bulk_price ) {
						$prices[] = floatval( $bulk_price );
					}
				}
				$cheapest_price = min( $prices );
			}
			/* check if addons available */
			$addon_total = 0;

			if ( isset( $item['addons'] ) && ! empty( $item['addons'] ) ) {
				foreach ( $item['addons'] as $addon ) {
					$prices = self::get_available_addon_prices( $addon['price'], $item['product_id'] );
					$price  = min( $prices );

					$addon_total = $addon_total + $price;
				}
			}

			if ( 0 !== $addon_total ) {
				$cheapest_price = $cheapest_price + $addon_total;
			}
			/* check if product bundle */
			if ( function_exists( 'wc_pb_is_bundled_cart_item' ) ) {
				if ( wc_pb_is_bundled_cart_item( $item ) ) {
					$item['data']->set_price( 0 );
				} else {
					/* update the total */
					$item['data']->set_price( $cheapest_price );
				}
			} else {
					/* update the total */
					$item['data']->set_price( $cheapest_price );
			}
		}
	}

	/**
	 * Show the cheapest price on item in cart and mini cart
	 *
	 * @param string $price the current price.
	 * @param object $cart_item current cart object.
	 * @param string $cart_item_key current cart item key.
	 * @return string
	 */
	public function set_cart_item_price( $price, $cart_item, $cart_item_key ) {

		$prices    = array();
		$quantity  = $cart_item['quantity'];
		$product   = wc_get_product( $cart_item['product_id'] );
		$tax_input = get_option( 'woocommerce_prices_include_tax' );

		$group_id = BM_Conditionals::get_validated_customer_group();

		if ( ! isset( $group_id ) || empty( $group_id ) ) {
			return;
		}

		$tax_type = get_post_meta( $group_id, 'bm_tax_type', true );

		if ( $product->is_type( 'variable' ) ) {

			$base_prices = self::get_available_group_prices( $cart_item['variation_id'] );
			$bulk_prices = self::get_available_bulk_prices( $cart_item['variation_id'], $quantity );

			if ( isset( $base_prices ) && ! empty( $base_prices ) ) {
				foreach ( $base_prices as $base_price ) {
					$prices[] = floatval( $base_price );
				}
			}
			if ( isset( $bulk_prices ) && ! empty( $bulk_prices ) ) {
				foreach ( $bulk_prices as $bulk_price ) {
					$prices[] = floatval( $bulk_price );
				}
			}

			if ( 'on' === $tax_type ) {
				$args = array( 'price' => min( $prices ) );
				$cheapest_price = round( wc_get_price_excluding_tax( wc_get_product( $cart_item['variation_id'] ), $args ), 2 );
			} else {
				if ( 'no' === $tax_input ) {
					$args = array( 'price' => min( $prices ) );
					$cheapest_price = round( wc_get_price_including_tax( wc_get_product( $cart_item['variation_id'] ), $args ), 2 );
				} else {
					$cheapest_price = min( $prices );
				}
			}
		} else {
			$base_prices = self::get_available_group_prices( $product->get_id() );
			$bulk_prices = self::get_available_bulk_prices( $product->get_id(), $quantity );

			if ( isset( $base_prices ) && ! empty( $base_prices ) ) {
				foreach ( $base_prices as $base_price ) {
					$prices[] = floatval( $base_price );
				}
			}

			if ( isset( $bulk_prices ) && ! empty( $bulk_prices ) ) {
				foreach ( $bulk_prices as $bulk_price ) {
					$prices[] = floatval( $bulk_price );
				}
			}
			if ( 'on' === $tax_type ) {
				$args = array( 'price' => min( $prices ) );
				$cheapest_price = round( wc_get_price_excluding_tax( wc_get_product( $product ), $args ), 2 );
			} else {
				if ( 'no' === $tax_input ) {
					$args = array( 'price' => min( $prices ) );
					$cheapest_price = round( wc_get_price_including_tax( wc_get_product( $product ), $args ), 2 );
				} else {
					$cheapest_price = min( $prices );
				}
			}
		}
		return wc_price( $cheapest_price );
	}
}
