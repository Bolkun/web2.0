<?php

if ( ! class_exists( 'BM_Quantities' ) ) {
	/**
	* Class to handle min and max quantities
	*/

	class BM_Quantities {

		private static $instance = null;

		/**
		 * Initialise quantity class
		 *
		 * @return void
		 */
		public function __construct() {
			add_filter( 'woocommerce_quantity_input_args', array( $this, 'bm_product_quantity' ), 10, 2 );
			add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'bm_add_to_cart_quantity' ), 1, 5 );
			add_filter( 'woocommerce_update_cart_validation', array( $this, 'bm_cart_update_quantity' ), 1, 4 );
			add_filter( 'woocommerce_available_variation', array( $this, 'bm_variation_quantity' ) );
		}

		/**
		 * Return an instance of BM_Quantities class
		 *
		 * @return object
		 */
		public static function get_instance() {
			if ( null == self::$instance ) {
				self::$instance = new BM_Quantities();
			}
			return self::$instance;
		}

		/**
		 * Filter input args on product page based on meta
		 *
		 * @param array $args
		 * @param object $product
		 * @return void
		 */
		public function bm_product_quantity( $args, $product ) {

			$group        = BM_Conditionals::get_validated_customer_group();
			$group_object = get_post( $group );
			$group_slug   = $group_object->post_name . '_';
			$current_id   = get_the_id();

			$min   = get_post_meta( $current_id, 'bm_' . $group_slug . 'min_quantity', true );
			$input = get_post_meta( $current_id, 'bm_' . $group_slug . 'min_quantity', true );

			if ( ! isset( $min ) || empty( $min ) ) {
				$min   = 1;
				$input = 0;
			}

			$max = get_post_meta( $current_id, 'bm_' . $group_slug . 'max_quantity', true );

			if ( ! isset( $max ) || empty( $max ) ) {
				$max = -1;
			}

			$step = get_post_meta( $current_id, 'bm_' . $group_slug . 'step_quantity', true );

			if ( ! isset( $step ) || empty( $step ) ) {
				$step = 1;
			}

			if ( ! is_cart() ) {
				$args['input_value'] = $min;
				$args['max_value']   = $max;
				$args['min_value']   = $input;
				$args['step']        = $step;
			} else {
				$args['max_value'] = $max;
				$args['step']      = $step;
				$args['min_value'] = $min;
			}

			return $args;
		}
		/**
		 * Filter input args on product page for variation based on meta
		 *
		 * @param array $args
		 * @return void
		 */
		public function bm_variation_quantity( $args ) {

			$variation = wc_get_product( $args['variation_id'] );

			$group        = BM_Conditionals::get_validated_customer_group();
			$group_object = get_post( $group );
			$group_slug   = $group_object->post_name . '_';
			$current_id   = $variation->get_parent_id();

			$min = get_post_meta( $current_id, 'bm_' . $group_slug . 'min_quantity', true );
			$max = get_post_meta( $current_id, 'bm_' . $group_slug . 'max_quantity', true );

			if ( isset( $min ) && ! empty( $min ) ) {
				$args['min_qty'] = $min;
			}

			if ( isset( $max ) && ! empty( $max ) ) {
				$args['max_qty'] = $max;
			}

			return $args;
		}

		/**
		 * Validate cart quantity
		 *
		 * @param array $passed
		 * @param int $product_id
		 * @param int $quantity
		 * @param string $variation_id
		 * @param string $variations
		 * @return void
		 */
		public function bm_add_to_cart_quantity( $passed, $product_id, $quantity, $variation_id = '', $variations = '' ) {

			$group        = BM_Conditionals::get_validated_customer_group();
			$group_object = get_post( $group );
			$group_slug   = $group_object->post_name . '_';

			$min = get_post_meta( $product_id, 'bm_' . $group_slug . 'min_quantity', true );
			$max = get_post_meta( $product_id, 'bm_' . $group_slug . 'max_quantity', true );

			if ( ! empty( $min ) ) {
				if ( false !== $min ) {
					$new_min = $min;
				} else {
					return $passed;
				}
			}

			if ( ! empty( $max ) ) {
				if ( false !== $max ) {
					$new_max = $max;
				} else {
					return $passed;
				}
			}

			$already_in_cart = $this->get_quantity_in_cart( $product_id );
			$product         = wc_get_product( $product_id );

			if ( ! empty( $already_in_cart ) ) {
				if ( isset( $new_max ) && ! empty( $new_max ) ) {
					if ( ( $already_in_cart + $quantity ) > $new_max ) {

						$passed = false;

						wc_add_notice( apply_filters( 'bm_max_quantity_in_cart', sprintf( __( 'You can add a maximum of %1$s %2$s\'s to %3$s. You already have %4$s.', 'b2b-market' ),
							$new_max,
							$product->get_title(),
							'<a href="' . esc_url( wc_get_cart_url() ) . '">' . __( 'your cart', 'b2b-market' ) . '</a>',
						$already_in_cart ), $new_max, $already_in_cart ), 'error' );
					}
				}
			}
			return $passed;
		}

		/**
		 * Is allready in cart?
		 *
		 * @param int $product_id
		 * @return void
		 */
		public function get_quantity_in_cart( $product_id ) {

			global $woocommerce;
			$running_qty = 0;

			foreach ( $woocommerce->cart->get_cart() as $other_cart_item_keys => $values ) {
				if ( $product_id == $values['product_id'] ) {
					$running_qty += $values['quantity'];
				}
			}
			return $running_qty;
		}

		/**
		 * Is allready in cart and try update?
		 *
		 * @param int $product_id
		 * @return void
		 */
		public function get_quantity_in_cart_update( $product_id , $cart_item_key = '' ) {
			global $woocommerce;
			$running_qty = 0; // iniializing quantity to 0
			// search the cart for the product in and calculate quantity.
			foreach ( $woocommerce->cart->get_cart() as $other_cart_item_keys => $values ) {
				if ( $product_id == $values['product_id'] ) {
					if ( $cart_item_key == $other_cart_item_keys ) {
						continue;
					}
					$running_qty += (int) $values['quantity'];
				}
			}
			return $running_qty;
		}

		/**
		 * Matched quantity in cart?
		 *
		 * @param bool  $passed
		 * @param array  $cart_item_key
		 * @param array  $values
		 * @param int  $quantity
		 * @return void
		 */
		public function bm_cart_update_quantity( $passed, $cart_item_key, $values, $quantity ) {

			$group        = BM_Conditionals::get_validated_customer_group();
			$group_object = get_post( $group );
			$group_slug   = $group_object->post_name . '_';
			$current_id   = $values['product_id'];

			$min = get_post_meta( $current_id, 'bm_' . $group_slug . 'min_quantity', true );
			$max = get_post_meta( $current_id, 'bm_' . $group_slug . 'max_quantity', true );

			if ( ! empty( $min ) ) {
				if ( false !== $min ) {
					$new_min = $min;
				} else {
					return $passed;
				}
			}

			if ( ! empty( $max ) ) {
				if ( false !== $max ) {
					$new_max = $max;
				} else {
					return $passed;
				}
			}

			$product = wc_get_product( $values['product_id'] );
			$already_in_cart = $this->get_quantity_in_cart_update( $values['product_id'], $cart_item_key );

			if ( isset( $new_max ) && ! empty( $new_max ) ) {
				if ( ( $already_in_cart + $quantity ) > $new_max ) {
					wc_add_notice( apply_filters( 'wc_qty_error_message', sprintf( __( 'You can add a maximum of %1$s %2$s\'s to %3$s.', 'b2b-market' ),
						$new_max,
						$product->get_name(),
					'<a href="' . esc_url( wc_get_cart_url() ) . '">' . __( 'your cart', 'b2b-market' ) . '</a>'), $new_max ), 'error' );

						$passed = false;
				}
			}
			if ( isset( $new_min ) && ! empty( $new_min ) ) {
				if ( ( $already_in_cart + $quantity ) < $new_min ) {
					wc_add_notice( apply_filters( 'wc_qty_error_message', sprintf( __( 'You should have minimum of %1$s %2$s\'s to %3$s.', 'b2b-market' ),
						$new_min,
						$product->get_name(),
					'<a href="' . esc_url( wc_get_cart_url() ) . '">' . __( 'your cart', 'b2b-market' ) . '</a>'), $new_min ), 'error' );

					$passed = false;
				}
			}
			return $passed;
		}
	}

	BM_Quantities::get_instance();
}
