<?php

/**
 * Class which handles all the helper functions
 */
class BM_Helper {

	/**
	 * BM_Helper constructor.
	 */
	public function __construct() {
		add_action( 'wp_trash_post', array( $this, 'skip_trash' ) );
		add_action( 'before_delete_post', array( $this, 'clear_options' ) );
	}

	/**
	 * Get available products
	 *
	 * @return void
	 */
	public static function get_available_products() {
		$args     = array(
			'posts_per_page' => - 1,
			'post_type'      => 'product',
			'post_status'    => 'publish',
		);
		$products = array();
		$posts    = get_posts( $args );

		foreach ( $posts as $product ) {
			$_product = wc_get_product( $product->ID );
			if ( ! $_product->is_type( 'grouped' ) ) {
				array_push( $products, array( $product->post_title, $product->ID ) );
			}
		}
		return $products;
	}

	/**
	 * Get avaialable product categories
	 *
	 * @return void
	 */
	public static function get_available_categories() {
		$cats = get_terms( 'product_cat', array(
			'hide_empty' => false,
		) );

		$available_categories = array();

		foreach ( $cats as $cat ) {
			array_push( $available_categories, array( $cat->name, $cat->term_id ) );
		}

		return $available_categories;
	}

	/**
	 * Get current posttype from admin page
	 *
	 * @return void
	 */
	public static function get_current_post_type() {
		global $post, $typenow, $current_screen;

		if ( $post && $post->post_type ) {
			return $post->post_type;
		} elseif ( $typenow ) {
			return $typenow;
		} elseif ( $current_screen && $current_screen->post_type ) {
			return $current_screen->post_type;
		} elseif ( isset( $_REQUEST['post_type'] ) ) {
			return sanitize_key( $_REQUEST['post_type'] );
		} elseif ( isset( $_REQUEST['post'] ) ) {
			return get_post_type( $_REQUEST['post'] );
		}

		return null;
	}

	/**
	 * force delete customer_groups
	 *
	 * @param $post_id
	 */
	public function skip_trash( $post_id ) {
		if ( $this->get_current_post_type() == 'customer_groups' ) {
			// Force delete
			wp_delete_post( $post_id, true );
		}
	}

	/**
	 * Delete all options for customer group
	 *
	 * @param int $postid
	 * @return void
	 */
	public function clear_options( $postid ) {

		if ( $this->get_current_post_type() == 'customer_groups' ) {
			global $wpdb;

			$group_object = get_post( $postid );
			$group        = $group_object->post_name;

			if ( ! empty( $group ) ) {

				$results = $wpdb->get_results( $wpdb->prepare( "SELECT * from $wpdb->options WHERE option_name LIKE %s", $group ) );

				foreach ( $results as $result ) {
					delete_option( $result->option_name );
				}
			}
		}
	}
	/**
	 * Checks if array is empty
	 *
	 * @param array $array
	 * @return boolean
	 */
	public static function is_array_empty( $array ) {

		foreach ( $array as $key => $val ) {
			if ( '' == $val ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if the current visit is a rest api call
	 *
	 * @return boolean
	 */
	public static function is_rest() {

		$prefix = rest_get_url_prefix();

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST || isset( $_GET['rest_route'] ) && strpos( trim( $_GET['rest_route'], '\\/' ), $prefix, 0 ) === 0 ) {
			return true;
		}

		$rest_url    = wp_parse_url( site_url( $prefix ) );
		$current_url = wp_parse_url( add_query_arg( array() ) );
		return strpos( $current_url['path'], $rest_url['path'], 0 ) === 0;
	}
	/**
	 * Delete bm transients
	 *
	 * @return void
	 */
	public static function delete_b2b_transients() {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_bm_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_bm_%'" );
	}

	public static function bm_from_price_without_b2b( $price, $product ) {

		$group_id = BM_Conditionals::get_validated_customer_group();

		if ( ! is_null( $group_id ) ) {
			return;
		}

		$prefix = sprintf( '%s: ', __( 'From', 'b2b-market' ) );

		$min_price_regular = $product->get_variation_regular_price( 'min', true );
		$min_price_sale    = $product->get_variation_sale_price( 'min', true );

		$max_price = $product->get_variation_price( 'max', true );
		$min_price = $product->get_variation_price( 'min', true );

		$price = ( $min_price_sale == $min_price_regular ) ?
			wc_price( $min_price_regular ) :
			'<del>' . wc_price( $min_price_regular ) . '</del>' . '<ins>' . wc_price( $min_price_sale ) . '</ins>';

		return ( $min_price == $max_price ) ?
			$price :
			sprintf( '%s%s', $prefix, $price );
	}

}
