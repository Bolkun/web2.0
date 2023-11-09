<?php

class BM_Live_Price {

	/**
	 * Initialize the live price
	 *
	 * @return void
	 */
	public static function init_bm_live_price() {
		if ( BM_Helper::is_rest() !== true ) {
			$current_theme = wp_get_theme();
			if ( 'shopkeeper' !== $current_theme->template ) {
				add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_assets' ) );
			}
			add_filter( 'woocommerce_get_price_html', array( __CLASS__, 'single_product_live_price' ), 100, 2 );
			add_filter( 'woocommerce_variable_price_html', array( __CLASS__, 'single_product_live_price' ), 100, 2 );
			add_action( 'woocommerce_before_add_to_cart_form', array( __CLASS__, 'add_hidden_id_field' ), 5 );
			add_action( 'wp_ajax_update_live_price', array( __CLASS__, 'update_live_price' ) );
			add_action( 'wp_ajax_nopriv_update_live_price', array( __CLASS__, 'update_live_price' ) );
			add_filter( 'woocommerce_product_addons_option_price', array( __CLASS__, 'update_addon_price' ), 10, 4 );
		}
	}

	/**
	 * Enqueue assets
	 *
	 * @return void
	 */
	public static function load_assets() {

		if ( ! is_product() ) {
			return;
		}

		$product = wc_get_product( get_the_id() );

		if ( is_null( $product ) ) {
			return;
		}

		$group_id = BM_Conditionals::get_validated_customer_group();

		if ( ! isset( $group_id ) || empty( $group_id ) ) {
			return;
		}

		if ( ! is_cart() ) {
			wp_enqueue_script( 'bm-live-price-js', B2B_PLUGIN_URL . '/assets/public/bm-live-price.js', array( 'jquery' ), '1.0', false );

			if ( $product->is_type( 'variable' ) ) {
				wp_localize_script( 'bm-live-price-js', 'ajax', array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'variable' => true,
				) );
			} else {
				wp_localize_script( 'bm-live-price-js', 'ajax', array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				) );
			}
		}
	}

	/**
	 * Show single product price based on serverside quantity
	 *
	 * @param string $price current price.
	 * @param object $product current product object.
	 * @return string
	 */
	public static function single_product_live_price( $price, $product ) {

		$quantity  = 1;
		$group_id  = BM_Conditionals::get_validated_customer_group();
		$rrp_price = false;
		$tax_input = get_option( 'woocommerce_prices_include_tax' );

		if ( is_null( $group_id ) ) {
			return $price;
		}

		$group    = get_post( $group_id );
		$tax_type = get_post_meta( $group_id, 'bm_tax_type', true );
		$prices   = array();

		if ( $product->is_type( 'variable' ) ) {

			$options_saved    = get_option( 'bm_all_options_saved' );
			$variations_saved = get_post_meta( $product->get_id(), '_min_variation_group_price_saved', true );

			if ( $options_saved === $variations_saved ) {

				$min_variation_price = floatval( get_post_meta( $product->get_id(), '_min_variation_group_price', true ) );

				if ( ! is_null( $min_variation_price ) && ! empty( $min_variation_price ) ) {
					$prices[] = $min_variation_price;
				} else {
					$prices[] = $product->get_price();
				}
			} else {
				$min_variation_price = self::calculate_min_variation_price( $product->get_id(), $group_id );

				if ( ! is_null( $min_variation_price ) && ! empty( $min_variation_price ) ) {
					$prices[] = $min_variation_price;
				} else {
					$prices[] = $product->get_price();
				}
			}

			$rrp_price = get_post_meta( $product->get_id(), 'bm_rrp', true );

			if ( 'on' === $tax_type ) {
				if ( isset( $rrp_price ) && ! empty( $rrp_price ) ) {
					$rrp_args  = array( 'price' => $rrp_price );
					$rrp_price = round( wc_get_price_excluding_tax( $product, $rrp_args ), 2 );
				}
				$args = array( 'price' => min( $prices ) );
				$cheapest_price = round( wc_get_price_excluding_tax( $product, $args ), 2 );
			} else {
				if ( 'no' === $tax_input ) {
					$args = array( 'price' => min( $prices ) );
					$cheapest_price = round( wc_get_price_including_tax( $product, $args ), 2 );
				} else {
					$cheapest_price = min( $prices );
				}
			}
		} else {
			$base_prices = BM_Calculation::get_available_group_prices( $product->get_id() );
			$bulk_prices = BM_Calculation::get_available_bulk_prices( $product->get_id(), $quantity );
			$rrp_price   = get_post_meta( $product->get_id(), 'bm_rrp', true );

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
				if ( isset( $rrp_price ) && ! empty( $rrp_price ) ) {
					$rrp_args  = array( 'price' => $rrp_price );
					$rrp_price = round( wc_get_price_including_tax( $product, $rrp_args ), 2 );
				}
				$args = array( 'price' => min( $prices ) );
				$cheapest_price = round( wc_get_price_excluding_tax( wc_get_product( $product ), $args ), 2 );
			} else {
				if ( isset( $rrp_price ) && ! empty( $rrp_price ) ) {
					$rrp_args  = array( 'price' => $rrp_price );
					$rrp_price = round( wc_get_price_including_tax( $product, $rrp_args ), 2 );
				}
				if ( 'no' === $tax_input ) {
					$args = array( 'price' => min( $prices ) );
					$cheapest_price = round( wc_get_price_including_tax( wc_get_product( $product ), $args ), 2 );
				} else {
					$cheapest_price = min( $prices );
				}
			}
		}

		if ( isset( $rrp_price ) && ! empty( $rrp_price ) ) {
			$markup = apply_filters( 'bm_price_html', '<span class="b2b-single-price"><small>' . __( 'RRP', 'b2b-market' ) . ': [rrp]</small><br>[cheapest]</span>', $cheapest_price );

			if ( $product->is_type( 'variable' ) ) {
				$markup = apply_filters( 'bm_price_html', '<span class="b2b-single-price"><small>' . __( 'RRP', 'b2b-market' ) . ': [rrp]</small><br>' . __( 'From', 'b2b-market' ) . ': [cheapest]</span>', $cheapest_price );
			}
			return str_replace( array( '[rrp]', '[cheapest]' ), array( wc_price( $rrp_price ), wc_price( $cheapest_price ) ), $markup );
		} elseif ( isset( $cheapest_price ) && ! empty( $cheapest_price ) ) {
			$markup = apply_filters( 'bm_original_price_html', '<span class="b2b-single-price">[cheapest]</span>', $cheapest_price );

			if ( $product->is_type( 'variable' ) ) {
				$markup = apply_filters( 'bm_original_price_html', '<span class="b2b-single-price">' . __( 'From', 'b2b-market' ) . ': [cheapest]</span>', $cheapest_price );
			}
			return str_replace( '[cheapest]', wc_price( $cheapest_price ), $markup );
		} else {
			return $price;
		}
	}

	/**
	 * Add hidden id for js live price
	 *
	 * @return void
	 */
	public function add_hidden_id_field() {
		?>
		<!-- Assumes that you're using Bootstrap -->
		<span id="current_id" style="visibility:hidden;" data-id="<?php echo get_the_id(); ?>"></span>
		<?php
	}

	/**
	 * Update addon price for WooCommerce Product Addons
	 *
	 * @param string $price current price.
	 * @param string $option given options.
	 * @param string $key option key.
	 * @param string $text option description.
	 * @return string
	 */
	public function update_addon_price( $price, $option, $key, $text ) {

		$quantity = 1;

		/* product addons price formatting hack */
		$cleaned_price = substr( preg_replace( '/\D/', '', $price ), 0, -2 );
		$base_prices   = BM_Calculation::get_available_addon_prices( floatval( $cleaned_price ), get_the_id() );

		if ( ! is_null( $base_prices ) ) {
			$price = min( $base_prices );
			return wc_price( $price );
		} else {
			return $price;
		}
	}

	/**
	 * Live update price with ajax
	 *
	 * @return void
	 */
	public function update_live_price() {

		if ( isset( $_POST['id'] ) && ! empty( $_POST['id'] ) && isset( $_POST['qty'] ) && ! empty( $_POST['qty'] ) ) {

			$prices    = array();
			$quantity  = $_POST['qty'];
			$product   = wc_get_product( $_POST['id'] );
			$tax_input = get_option( 'woocommerce_prices_include_tax' );
			$rrp_price = get_post_meta( $_POST['id'], 'bm_rrp', true );

			$group_id = BM_Conditionals::get_validated_customer_group();

			if ( ! isset( $group_id ) || empty( $group_id ) ) {
				return;
			}

			$tax_type    = get_post_meta( $group_id, 'bm_tax_type', true );
			$base_prices = BM_Calculation::get_available_group_prices( $product->get_id() );
			$bulk_prices = BM_Calculation::get_available_bulk_prices( $product->get_id(), $quantity );

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
				if ( isset( $rrp_price ) && ! empty( $rrp_price ) ) {
					$rrp_args      = array( 'price' => $rrp_price );
					$rrp_price = round( wc_get_price_excluding_tax( $product, $rrp_args ), 2 );
				}
				$args           = array( 'price' => min( $prices ) );
				$cheapest_price = round( wc_get_price_excluding_tax( $product, $args ), 2 );
			} else {
				if ( 'no' === $tax_input ) {
					$args = array( 'price' => min( $prices ) );
					$cheapest_price = round( wc_get_price_including_tax( wc_get_product( $product ), $args ), 2 );
				} else {
					$cheapest_price = min( $prices );
				}
			}

			/* send the response */
			$response = array(
				'sucess' => true,
				'id'     => $_POST['id'],
			);

			if ( isset( $rrp_price ) && ! empty( $rrp_price ) ) {
				$markup            = apply_filters( 'bm_price_html', '<span class="b2b-single-price"><small>' . __( 'RRP', 'b2b-market' ) . ': [rrp]</small><br>[cheapest]</span>' );
				$response['price'] = str_replace( array( '[rrp]', '[cheapest]' ), array( wc_price( $rrp_price ), wc_price( $cheapest_price ) ), $markup );
				$response['rrp'] = $rrp_price;
			} else {
				$markup            = apply_filters( 'bm_price_html', '<span class="b2b-single-price">[cheapest]</span>' );
				$response['price'] = str_replace( '[cheapest]', wc_price( $cheapest_price ), $markup );
			}

			print wp_json_encode( $response );
			exit;
		}
	}
	/**
	 * Calculate the current variation from price.
	 *
	 * @param int $product_id the current product id.
	 * @return float
	 */
	public static function calculate_min_variation_price( $product_id ) {

		$group_id = BM_Conditionals::get_validated_customer_group();

		/* price type options */
		$options = array(
			__( 'Fixed Price', 'b2b-market' )  => 'fix',
			__( 'Discount (fixed Value)', 'b2b-market' ) => 'discount',
			__( 'Discount (%)', 'b2b-market' ) => 'discount-percent',
		);

		/* get variations from product */
		$args = array(
			'post_type'   => 'product_variation',
			'numberposts' => -1,
			'post_parent' => $product_id,
			'fields'      => 'ids',
		);

		$variations = get_posts( $args );

		/* build array with all prices */
		$prices_meta = array();
		$bulk_prices_meta = array();
		$min_prices = array();

		foreach ( $variations as $variation_id ) {

			$variation     = wc_get_product( $variation_id );
			$regular_price = floatval( $variation->get_price() );

			/* group prices */
			$group_price         = BM_Pricing_Data::get_group_base_price( $variation_id );
			$product_group_price = BM_Pricing_Data::get_product_group_price( $variation_id, $group_id );
			$global_base_price   = BM_Pricing_Data::get_global_base_price();

			if ( is_array( $group_price ) && BM_Helper::is_array_empty( $group_price ) === false && ! is_null( $group_id ) ) {
				$prices_meta[] = $group_price;
			}
			if ( is_array( $product_group_price ) && BM_Helper::is_array_empty( $product_group_price ) === false && ! is_null( $group_id ) ) {
				$prices_meta[] = $product_group_price;
			}
			if ( is_array( $global_base_price ) && BM_Helper::is_array_empty( $global_base_price ) === false ) {
				$prices_meta[] = $global_base_price;
			}

			foreach ( $prices_meta as $meta ) {
				$key   = key( $meta );
				$value = floatval( reset( $meta ) );

				switch ( $key ) {
					case 'fix':
						$price        = $value;
						$min_prices[] = $price;
						break;
					case 'discount':
						$price        = $regular_price - $value;
						$min_prices[] = $price;
						break;
					case 'discount-percent':
						$price        = $regular_price - ( $value * $regular_price / 100 );
						$min_prices[] = $price;
						break;
				}
			}

			/* bulk prices */
			$quantity            = 1;
			$group_bulk_prices   = BM_Pricing_Data::get_group_bulk_price( $group_id );
			$product_bulk_prices = BM_Pricing_Data::get_product_bulk_price( $variation_id );
			$global_bulk_prices  = BM_Pricing_Data::get_global_bulk_price();

			if ( is_array( $group_bulk_prices ) && ! is_null( $group_id ) ) {
				$bulk_prices_meta[] = $group_bulk_prices;
			}

			if ( is_array( $product_bulk_prices ) && ! is_null( $group_id ) ) {
				$bulk_prices_meta[] = $product_bulk_prices;
			}

			if ( is_array( $global_bulk_prices ) ) {
				$bulk_prices_meta[] = $global_bulk_prices;
			}

			foreach ( $bulk_prices_meta as $bulks ) {

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
									$price        = $bulk_price;
									$min_prices[] = $price;
									break;

								case 'discount':
									if ( $regular_price > $bulk_price ) {
										$price        = $regular_price - $bulk_price;
										$min_prices[] = $price;
									}
									break;

								case 'discount-percent':
									if ( $regular_price ) {
										$price        = $regular_price - ( $bulk_price * $regular_price / 100 );
										$min_prices[] = $price;
									}
									break;
							}
						}
					}
				}
			}
		}
		if ( isset( $min_prices ) && ! empty( $min_prices ) ) {
			$min_prices[] = $regular_price;
			update_post_meta( $product_id, '_min_variation_group_price', min( $min_prices ) );
			update_post_meta( $product_id, '_min_variation_group_price_saved', date( 'Y-m-d-H-i' ) );
			return min( $min_prices );
		}
	}
}
