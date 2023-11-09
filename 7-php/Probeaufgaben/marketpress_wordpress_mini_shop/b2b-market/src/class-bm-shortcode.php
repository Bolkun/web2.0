<?php

/**
 * Class which handles the frontend pricing display
 */
class BM_Shortcode {

	/**
	 * BM_Shortcode constructor.
	 */
	public function __construct() {
		add_shortcode( 'bulk-price-table', array( $this, 'bulk_price_table' ) );
		add_shortcode( 'b2b-group-display', array( $this, 'conditional_customer_group_output' ) );
	}

	/**
	 * Outputs the bulk-price-table shortcode
	 *
	 * @param array $atts
	 * @return void
	 */
	public function bulk_price_table( $atts ) {

		$group       = BM_Conditionals::get_validated_customer_group();
		$conditional = new BM_Conditionals();

		if ( ! isset( $group ) || empty( $group ) ) {
			return;
		}

		$group_object = get_post( $group );
		$group_slug   = $group_object->post_name;
		$tax_type     = get_post_meta( $group, 'bm_tax_type', true );
		$tax_input    = get_option( 'woocommerce_prices_include_tax' );

		$columns = array();

		if ( isset( $atts['product-title'] ) ) {
			$columns['product_title'] = __( 'Product', 'b2b-market' );
		}
		$columns['bulk_price'] = __( 'Bulk Price', 'b2b-market' );
		$columns['quantity_from'] = __( 'Quantity (from)', 'b2b-market' );
		$columns['quantity_to'] = __( 'Quantity (to)', 'b2b-market' );

		$table_class = apply_filters( 'b2b_bulk_price_table_class', 'bm-bulk-table' );

		$product_id = get_the_id();

		if ( isset( $atts['product-id'] ) ) {
				$product_id = $atts['product-id'];
		}

		$_product = wc_get_product( $product_id );

		$use_regular = apply_filters( 'bm_use_regular_for_bulk_price', true );

		if ( true === $use_regular ) {
			$sale    = get_post_meta( $product_id, '_sale_price', true );
			$regular = get_post_meta( $product_id, '_regular_price', true );

			if ( isset( $sale ) && ! empty( $sale ) ) {
				$base_price = $sale;
			} else {
				$base_price = $regular;
			}
		} else {
			$base_price = $_product->get_price();
		}

		$product_bulk_prices = get_post_meta( $product_id, 'bm_' . $group_slug . '_bulk_prices', true );
		$group_bulk_prices   = get_post_meta( $group, 'bm_bulk_prices', true );
		$product_in_group    = false;
		$price_type          = '';

		if ( 'on' === get_post_meta( intval( $group ), 'bm_all_products', true ) ) {
			$product_in_group = true;
		} elseif ( $conditional->active_price_for_product( $product_id ) === true ) {
			$product_in_group = true;
		} elseif ( $conditional->active_price_for_category( $product_id ) == true ) {
			$product_in_group = true;
		}

		if ( ! is_null( $group ) && '' !== $group && true === $product_in_group ) {

			if ( isset( $product_bulk_prices ) && ! empty( $product_bulk_prices ) || isset( $group_bulk_prices ) && ! empty( $group_bulk_prices ) ) {

				$shortcode  = '<table class="' . $table_class . '">';
				$shortcode .= '<thead>';
				$shortcode .= '<tr>';

				foreach ( $columns as $key => $value ) {
					$shortcode .= '<td>' . $value . '</td>';
				}
				$shortcode .= '</tr>';
				$shortcode .= '</thead>';
				$shortcode .= '<tbody>';

				if ( is_array( $product_bulk_prices ) && true === $product_in_group ) {

					foreach ( $product_bulk_prices as $price ) {

						if ( ! isset( $price['bulk_price_type'] ) ) {
							$price_type = 'fix';
						} else {
							$price_type = $price['bulk_price_type'];
						}

						switch ( $price['bulk_price_type'] ) {
							case 'fix':
								$total = $price['bulk_price'];
								break;
							case 'discount':
								if ( $base_price > $price['bulk_price'] ) {
									$total = $base_price - $price['bulk_price'];
								}
								break;
							case 'discount-percent':
								$total = $base_price - ( $price['bulk_price'] * $base_price / 100 );
						}

						$shortcode .= '<tr>';

						if ( isset( $atts['product-title'] ) ) {
							$shortcode .= '<td>' . $_product->get_name() . '</td>';
						}

						foreach ( $columns as $key => $value ) {

							if ( 'on' === $tax_type ) {
								$args = array( 'price' => $total );
								$total = round( wc_get_price_excluding_tax( wc_get_product( $product_id ), $args ), 2 );
							} else {
								if ( 'no' === $tax_input ) {
									$args = array( 'price' => $total );
									$total = round( wc_get_price_including_tax( wc_get_product( $product_id ), $args ), 2 );
								}
							}

							if ( 'bulk_price' === $key ) {
								$shortcode .= '<td>' . wc_price( $total ) . '</td>';
							}
							if ( 'quantity_from' === $key ) {
								$shortcode .= '<td>' . $price['bulk_price_from'] . '</td>';
							}
							if ( 'quantity_to' === $key ) {
								$shortcode .= '<td>' . $price['bulk_price_to'] . '</td>';
							}
						}
						$shortcode .= '</tr>';

					}
				}

				if ( is_array( $group_bulk_prices ) && true === $product_in_group ) {

					foreach ( $group_bulk_prices as $price ) {

						if ( ! isset( $price['bulk_price_type'] ) ) {
							$price_type = 'fix';
						} else {
							$price_type = $price['bulk_price_type'];
						}

						switch ( $price['bulk_price_type'] ) {
							case 'fix':
								$total = $price['bulk_price'];
								break;
							case 'discount':
								if ( $base_price > $price['bulk_price'] ) {
									$total = $base_price - $price['bulk_price'];
								}
								break;
							case 'discount-percent':
								$total = $base_price - ( $price['bulk_price'] * $base_price / 100 );
						}

						$shortcode .= '<tr>';

						if ( isset( $atts['product-title'] ) ) {
							$shortcode .= '<td>' . $_product->get_name() . '</td>';
						}

						foreach ( $columns as $key => $value ) {

							if ( 'on' === $tax_type ) {
								$args = array( 'price' => $total );
								$total = round( wc_get_price_excluding_tax( wc_get_product( $product_id ), $args ), 2 );
							} else {
								if ( 'no' === $tax_input ) {
									$args = array( 'price' => $total );
									$total = round( wc_get_price_including_tax( wc_get_product( $product_id ), $args ), 2 );
								}
							}

							if ( 'bulk_price' === $key ) {
								$shortcode .= '<td>' . wc_price( $total ) . '</td>';
							}
							if ( 'quantity_from' === $key ) {
								$shortcode .= '<td>' . $price['bulk_price_from'] . '</td>';
							}
							if ( 'quantity_to' === $key ) {
								$shortcode .= '<td>' . $price['bulk_price_to'] . '</td>';
							}
						}

						$shortcode .= '</tr>';
					}
				}

				$shortcode .= '</tbody>';
				$shortcode .= '</table>';

				return $shortcode;
			}
		}
	}
	/**
	 * Shortcode for group based content display
	 *
	 * @param array  $atts
	 * @param string $content
	 * @return void
	 */
	public function conditional_customer_group_output( $atts, $content = null ) {

		$output = '';

		$special_signs = array( '„', '“' );
		$group         = str_replace( $special_signs, '', $atts['group'] );

		if ( isset( $group ) ) {

			/* check if guest or customer */
			if ( 'guest' === $group || 'gast' === $group || 'customer' === $group || 'kunde' === $group ) {

				if ( ! is_user_logged_in() ) {
					$output = $content;
				}
			} else {
				/* check if customer group */
				if ( is_user_logged_in() ) {
					$user = wp_get_current_user();
					$role = $user->roles;

					if ( in_array( $group, $role ) ) {
						$output = $content;
					}
				}
			}

			$output = apply_filters( 'the_content', $output );
			return $output;
		} else {
			return $content;
		}
	}
}

new BM_Shortcode();
