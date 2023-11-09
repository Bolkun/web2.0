<?php

class BM_Automatic_Actions {
	/**
	 * @var string
	 */
	public $meta_prefix;

	/**
	 * BM_Automatic_Actions constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'add_first_order_discount' ), 10 );
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'add_goods_discount' ), 10 );
		add_filter( 'woocommerce_cart_totals_coupon_label', array( $this, 'replace_coupon_label_with_description' ), 10, 2 );
	}

	/**
	 * @param WC_Cart $cart
	 */
	public function add_first_order_discount( WC_Cart $cart ) {

		if ( ! empty( BM_Conditionals::get_validated_customer_group() ) && $this->is_first_order() == 0 ) {

			/* discount meta */
			$discount_name      = get_post_meta( intval( BM_Conditionals::get_validated_customer_group() ), 'bm_discount_name', true );
			$discount_value     = get_post_meta( intval( BM_Conditionals::get_validated_customer_group() ), 'bm_discount', true );
			$discount_type      = get_post_meta( intval( BM_Conditionals::get_validated_customer_group() ), 'bm_discount_type', true );
			$discount_available = false;
			$group_object       = get_post( BM_Conditionals::get_validated_customer_group() );
			$group_slug         = $group_object->post_name;
			$user_id            = get_current_user_id();

			if ( ! empty( $discount_value ) && ! empty( $discount_type ) ) {

				/* selection rules meta */
				$discount_products     = explode( ',', get_post_meta( BM_Conditionals::get_validated_customer_group(), 'bm_discount_products', true ) );
				$discount_categories   = explode( ',', get_post_meta( BM_Conditionals::get_validated_customer_group(), 'bm_discount_categories', true ) );
				$discount_all_products = get_post_meta( BM_Conditionals::get_validated_customer_group(), 'bm_discount_all_products', true );
				$allowed_products      = array();

				if ( 'on' === $discount_all_products ) {
					$discount_available      = true;
					$allowed_products['all'] = true;
				} else {
					foreach ( $cart->get_cart() as $item => $values ) {
						$_product        = wc_get_product( $values['product_id'] );
						$product_cat_ids = $_product->get_category_ids();

						if ( in_array( $_product->get_id(), $discount_products ) ) {
							$discount_available = true;
							$allowed_products[] = $_product->get_id();
						}
						if ( isset( $product_cat_ids ) && is_array( $product_cat_ids ) ) {
							foreach ( $product_cat_ids as $cat ) {
								if ( in_array( $cat, $discount_categories ) ) {
									$discount_available = true;
									$allowed_products[] = $_product->get_id();
								}
							}
						}
					}
					if ( is_array( $allowed_products ) && ! empty( $allowed_products ) ) {
						$allowed_products = implode( ', ', $allowed_products );
					}
				}
			}

			if ( true === $discount_available ) {
				/* calculate and apply discount */

				if ( true === wc_coupons_enabled() && is_cart() ) {
					$discount      = floatval( $discount_value );
					$coupon_code   = 'first_order_' . $group_slug . '_' . $user_id;
					$coupon_exists = get_page_by_title( $coupon_code, OBJECT, 'shop_coupon' );

					if ( ! $cart->has_discount( $coupon_code ) ) {
						if ( is_null( $coupon_exists ) ) {
							$coupon = $this->generate_coupon( $coupon_code, $discount_type, $discount, $discount_name, $allowed_products );
						}

						$coupon_apply = new WC_Coupon( $coupon_code );

						if ( $coupon_apply->get_usage_count() < $coupon_apply->get_usage_limit() ) {
							WC()->cart->add_discount( wc_format_coupon_code( $coupon_code ) );
						}
					}
				}
			}
		}
	}


		/**
	 * @param WC_Cart $cart
	 */
	public function add_goods_discount( WC_Cart $cart ) {

		if ( ! empty( BM_Conditionals::get_validated_customer_group() ) ) {

			$goods_categories    = get_post_meta( BM_Conditionals::get_validated_customer_group(), 'bm_goods_discount_categories', true );
			$goods_product_count = get_post_meta( BM_Conditionals::get_validated_customer_group(), 'bm_goods_product_count', true );
			$goods_discount      = get_post_meta( BM_Conditionals::get_validated_customer_group(), 'bm_goods_discount', true );
			$goods_discount_type = get_post_meta( BM_Conditionals::get_validated_customer_group(), 'bm_goods_discount_type', true );

			$goods_categories_array = explode( ',', $goods_categories );
			$valid_products         = array();
			$valid_categories       = array();
			$valid_cart_quantity    = 0;

			$group_object     = get_post( BM_Conditionals::get_validated_customer_group() );
			$group_slug       = $group_object->post_name;
			$user_id          = get_current_user_id();
			$allowed_products = array();

			/* check if products in discount category */
			foreach ( $cart->get_cart() as $item => $values ) {

				$_product = wc_get_product( $values['product_id'] );

				if ( false === $_product ) {
					return;
				}

				$product_cat_ids = $_product->get_category_ids();

				foreach ( $goods_categories_array as $cat ) {

					if ( in_array( $cat, $product_cat_ids ) ) {
						array_push( $valid_products, array( $_product->get_id() => $values['quantity'] ) );
						$allowed_products[] = $_product->get_id();
						$valid_categories[] = $cat;
					}
				}
			}
			/* check if quantity match discount quantity */
			if ( isset( $valid_products ) ) {
				foreach ( $valid_products as $product ) {
					foreach ( $product as $key => $value ) {
						$valid_cart_quantity = $valid_cart_quantity + $value;
					}
				}
			}

			if ( count( $valid_products ) >= $goods_product_count || $valid_cart_quantity >= $goods_product_count ) {

				if ( ! empty( $goods_discount ) && ! empty( $goods_discount_type ) ) {

					$discounted_cats = array();

					foreach ( $valid_categories as $id ) {
						$term              = get_term_by( 'id', $id, 'product_cat' );
						$discounted_cats[] = $term->name;
					}

					/* dynamic discount name */
					if ( count( array_unique( $discounted_cats ) ) == 1 ) {
						$discount_name = $goods_product_count . ' ' . __( 'Products', 'b2b-market' ) . ' ' . __( 'from Product Category', 'b2b-market' ) . ': ' . implode( ',', array_unique( $discounted_cats ) );
					} elseif ( count( array_unique( $discounted_cats ) ) > 1 ) {
						$discount_name = $goods_product_count . ' ' . __( 'Products', 'b2b-market' ) . ' ' . __( 'from Product Categories', 'b2b-market' ) . ': ' . implode( ',', array_unique( $discounted_cats ) );
					}

					$allowed_products = implode( ', ', $allowed_products );

					if ( true === wc_coupons_enabled() && is_cart() ) {
						$discount      = floatval( $goods_discount );
						$coupon_code   = 'category_discount' . $group_slug . '_' . $user_id;
						$coupon_exists = get_page_by_title( $coupon_code, OBJECT, 'shop_coupon' );

						if ( ! $cart->has_discount( $coupon_code ) ) {
							if ( is_null( $coupon_exists ) ) {
								$coupon = $this->generate_coupon( $coupon_code, $goods_discount_type, $discount, $discount_name, $allowed_products );
							}

							$coupon_apply = new WC_Coupon( $coupon_code );

							if ( $coupon_apply->get_usage_count() < $coupon_apply->get_usage_limit() ) {
								WC()->cart->add_discount( wc_format_coupon_code( $coupon_code ) );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * @return int
	 */
	protected function is_first_order() {

		$customer_orders = get_posts( array(
			'numberposts' => - 1,
			'meta_key'    => '_customer_user',
			'meta_value'  => get_current_user_id(),
			'post_type'   => wc_get_order_types(),
			'post_status' => array_keys( wc_get_order_statuses() ),
			'fields'      => 'ids',
		) );

		return count( $customer_orders );
	}
	protected function generate_coupon( $coupon_code, $discount_type, $discount_amount, $discount_name, $allowed_products ) {

		$coupon = array(
			'post_title'   => $coupon_code,
			'post_excerpt' => $discount_name,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_author'  => 1,
			'post_type'    => 'shop_coupon',
		);

		$new_coupon_id = wp_insert_post( $coupon );

		if ( 'order-discount-fix' == $discount_type ) {
			$type = 'fixed_cart';
		} elseif ( 'order-discount-percent' == $discount_type ) {
			$type = 'percent';
		}

		$customer = new WC_Customer( get_current_user_id() );

		// Add meta
		update_post_meta( $new_coupon_id, 'discount_type', $type );
		update_post_meta( $new_coupon_id, 'coupon_amount', $discount_amount );
		update_post_meta( $new_coupon_id, 'usage_limit', '1' );
		update_post_meta( $new_coupon_id, 'expiry_date', '' );
		update_post_meta( $new_coupon_id, 'apply_before_tax', 'yes' );

		if ( ! isset( $allowed_products['all'] ) ) {
				update_post_meta( $new_coupon_id, 'product_ids', $allowed_products );
		}
		update_post_meta( $new_coupon_id, 'free_shipping', 'no' );
		update_post_meta( $new_coupon_id, 'customer_email', $customer->get_email() );

		return $coupon;

	}

	public function replace_coupon_label_with_description( $label, $coupon ) {

		if ( false !== strpos( $coupon->get_code(), 'first_order' ) || false !== strpos( $coupon->get_code(), 'category_discount' ) ) {

			if ( is_callable( array( $coupon, 'get_description' ) ) ) {
				$description = $coupon->get_description();
			} else {
				$coupon_post = get_post( $coupon->id );
				$description = ! empty( $coupon_post->post_excerpt ) ? $coupon_post->post_excerpt : null;
			}
			return $description ? sprintf( esc_html__( 'Coupon: %s', 'woocommerce' ), $description ) : esc_html__( 'Coupon', 'woocommerce' );
		} else {
			return $label;
		}
	}

}

new BM_Automatic_Actions();
