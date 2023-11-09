<?php

/**
 * Class to handle Whitelist / Blacklist
 */
class BM_Whitelist {

	/**
	 * BM_Conditionals constructor.
	 */
	public function __construct() {
		$this->current_customer_group = BM_Conditionals::get_validated_customer_group();

		$products   = $this->get_products_whitelist();
		$categories = $this->get_categories_whitelist();

		if ( is_array( $products ) && is_array( $categories ) ) {
			$this->blacklist = array_merge( $products, $categories );
		} elseif ( is_array( $products ) ) {
			$this->blacklist = $products;
		} elseif ( is_array( $categories ) ) {
			$this->blacklist = $products;
		}

		if ( isset( $this->current_customer_group ) && ! is_null( $this->current_customer_group ) ) {
			$this->active_whitelist = get_post_meta( $this->current_customer_group, 'bm_conditional_all_products', true );
		}
	}

	/**
	 * Get the products on whitelist
	 *
	 * @return array
	 */
	public function get_products_whitelist() {

		$whitelist = array();

		if ( isset( $this->current_customer_group ) && ! is_null( $this->current_customer_group ) ) {
			$products_customer_group_meta = get_post_meta( $this->current_customer_group, 'bm_conditional_products', true );
		}

		if ( isset( $products_customer_group_meta ) && ! empty( $products_customer_group_meta ) ) {
			$products_customer_group = explode( ',', $products_customer_group_meta );

			if ( isset( $products_customer_group ) ) {
				foreach ( $products_customer_group as $product ) {

					if ( '' != $product ) {
						array_push( $whitelist, intval( $product ) );
					}
				}
			}
		}
		return $whitelist;
	}

	/**
	 * Get categories for whitelist / blacklist
	 *
	 * @return array
	 */
	public function get_categories_whitelist() {

		$whitelist = array();

		if ( isset( $this->current_customer_group ) && ! is_null( $this->current_customer_group ) ) {
			$cat_customer_group_meta = get_post_meta( $this->current_customer_group, 'bm_conditional_categories', true );
		}

		if ( isset( $cat_customer_group_meta ) && ! empty( $cat_customer_group_meta ) ) {
			$categories_customer_group = explode( ',', $cat_customer_group_meta );

			if ( isset( $categories_customer_group ) ) {
				foreach ( $categories_customer_group as $category ) {

					if ( '' != $category ) {

						$term = get_term( $category, 'product_cat' );

						if ( ! empty( $term ) && ! is_null( $term ) ) {

							$args     = array(
								'posts_per_page'   => - 1,
								'post_type'        => 'product',
								'fields'           => 'ids',
								'post_status'      => 'publish',
								'suppress_filters' => false,
								'tax_query'        => array(
									array(
										'taxonomy' => 'product_cat',
										'field'    => 'slug',
										'terms'    => $term->slug,
									),
								),
							);
							$products = get_posts( $args );

							foreach ( $products as $product_id ) {
								array_push( $whitelist, $product_id );
							}
						}
					}
				}
			}
		}

		return $whitelist;
	}

	/**
	 * Set whitelist
	 *
	 * @param object $query
	 * @return void
	 */
	public function set_whitelist( $query ) {
		$q = 'post__not_in';

		if ( ! empty( $this->active_whitelist ) && 'on' == $this->active_whitelist ) {
			$q = 'post__in';
			if ( count( $this->blacklist ) === 0 ) {
				set_query_var( $q, array( 0 ) );
			} else {
				set_query_var( $q, array_unique( $this->blacklist ) );
			}
		} else {
			set_query_var( $q, array_unique( $this->blacklist ) );
		}
	}

	/**
	 * Set whitelist / blacklist for related products
	 *
	 * @param array $related_posts
	 * @param int $product_id
	 * @param array $args
	 * @return void
	 */
	public function set_related_whitelist( $related_posts, $product_id, $args ) {

		if ( is_product() ) {

			if ( ! empty( $this->active_whitelist ) && 'on' == $this->active_whitelist ) {
				if ( count( $this->blacklist ) === 0 ) {
					return $this->blacklist;
				}
			} else {
				$exclude_ids = $this->blacklist;
				return array_diff( $related_posts, $exclude_ids );
			}
		}
	}

	/**
	 * Set whitelist / blacklist for upsells
	 *
	 * @param [type] $relatedIds
	 * @param [type] $product
	 * @return void
	 */
	public function set_upsell_whitelist( $relatedIds, $product ) {

		if ( ! empty( $this->active_whitelist ) && 'on' == $this->active_whitelist ) {
			if ( count( $this->blacklist ) === 0 ) {
				return $this->blacklist;
			}
		} else {
			$exclude_ids = $this->blacklist;
			return array_diff( $relatedIds, $exclude_ids );
		}

	}

	/**
	 * Set whitelist / blacklist for widgets
	 *
	 * @param array $query_args
	 * @return void
	 */
	public function set_widget_whitelist( $query_args ) {

		$q         = 'post__not_in';

		if ( ! empty( $this->active_whitelist ) && 'on' == $this->active_whitelist ) {
			$q = 'post__in';
			if ( count( $this->blacklist ) === 0 ) {
				$query_args[ $q ] = array( 0 );
			} else {
				$query_args[ $q ] = array_unique( $this->blacklist );
			}
		}

		return $query_args;
	}

	/**
	 * Set whitelist / blacklist for search
	 *
	 * @param array $query
	 * @return void
	 */
	public function set_search_whitelist( $query ) {

		if ( ! $query->is_admin && $query->is_search ) {

			$q = 'post__not_in';

			if ( ! empty( $this->active_whitelist ) && 'on' == $this->active_whitelist ) {
				$q = 'post__in';
				if ( count( $this->blacklist ) === 0 ) {
					$query->set( $q, array( 0 ) );
					return $query;
				} else {
					$query->set( $q, array_unique( $this->blacklist ) );
				}
			} else {
				$query->set( $q, array_unique( $this->blacklist ) );
			}
		}
	}

	/**
	 * Set redirects based on whitelist / blacklist
	 *
	 * @return void
	 */
	public function redirect_based_on_whitelist() {

		if ( is_product() ) {

			if ( ! empty( $this->active_whitelist) && 'on' == $this->active_whitelist ) {
				if ( ! in_array( get_the_id(), $this->blacklist ) ) {
					load_template( get_template_directory() . '/404.php' );
					exit;
				}
			} elseif ( isset( $this->blacklist ) && ! empty( $this->blacklist ) ) {
				if ( in_array( get_the_id(), $this->blacklist ) ) {
					load_template( get_template_directory() . '/404.php' );
					exit;
				}
			}
		}
	}
}
