<?php
/**
 * Class which handles all conditional logic
 */
class BM_Conditionals {

	/**
	 * BM_Conditionals constructor.
	 */
	public function __construct() {
		$this->current_customer_group = self::get_validated_customer_group();
	}

	/**
	 * Get current user groups for a the current logged in user
	 *
	 * @return void
	 */
	public static function get_validated_customer_group() {

		$current_user  = wp_get_current_user();
		$current_group = get_transient( 'bm_' . $current_user->ID . '_current_group' );

		/* is guest? */
		if ( 0 == $current_user->ID ) {
			$group_id = get_option( 'bm_guest_group' );
			return $group_id;
		}
		/* has user id? */
		if ( 0 != $current_user->ID ) {
			/* is customer? */
			if ( in_array( 'customer', $current_user->roles ) ) {
				$group_id = get_option( 'bm_customer_group' );
				return $group_id;
			}
			/* is transient for customer group available? */
			if ( $current_group ) {
				$group_id = $current_group;
				return $group_id;
			} else {
				/* if not find the group which belongs to the role */
				foreach ( $current_user->roles as $slug ) {
					$group = get_page_by_path( $slug, OBJECT, 'customer_groups' );

					if ( ! is_null( $group ) ) {
						set_transient( 'bm_' . $current_user->ID . '_current_group', $group->ID, 60 );
						$group_id = $group->ID;
						return $group_id;
					}
				}
			}
		}
	}

	/**
	 * Check if product is activated for group
	 *
	 * @param int $product_id
	 * @return void
	 */
	public function active_price_for_product( $product_id ) {

		$product_in_group = false;

		if ( isset( $this->current_customer_group ) && ! is_null( $this->current_customer_group ) ) {

			$products_customer_group_meta = get_post_meta( $this->current_customer_group, 'bm_products', true );
			$all_products                 = get_post_meta( $this->current_customer_group, 'bm_all_products', true );

			if ( isset( $products_customer_group_meta ) && ! empty( $products_customer_group_meta ) ) {
				$products_customer_group = explode( ',', $products_customer_group_meta );

				if ( isset( $products_customer_group ) && ! empty( $products_customer_group ) ) {

					if ( in_array( $product_id, $products_customer_group ) ) {
						$product_in_group = true;
					}
				}
			} elseif ( 'on' === $all_products ) {
				$product_in_group = true;
			}
		}

		return $product_in_group;
	}

	/**
	 * Check if product from category is activated for group
	 *
	 * @param int $product_id
	 * @return void
	 */
	public function active_price_for_category( $product_id ) {

		$categories_in_group = false;

		if ( isset( $this->current_customer_group ) && ! is_null( $this->current_customer_group ) ) {

			$cat_customer_group_meta = get_post_meta( $this->current_customer_group, 'bm_categories', true );
			$all_products            = get_post_meta( $this->current_customer_group, 'bm_all_products', true );

			if ( isset( $cat_customer_group_meta ) && ! empty( $cat_customer_group_meta ) ) {
				$categories_customer_group = explode( ',', $cat_customer_group_meta );

				if ( isset( $categories_customer_group ) && ! empty( $categories_customer_group ) ) {
					$terms      = wp_get_post_terms( $product_id, 'product_cat' );
					$categories = array();

					if ( isset( $terms ) && ! empty( $terms ) ) {
						foreach ( $terms as $term ) {
							array_push( $categories, $term->term_id );

							if ( ! empty( $term->parent ) ) {
								array_push( $categories, $term->parent );
							}
						}
					}
					$matched_cats = array_intersect( $categories_customer_group, $categories );
					if ( ! empty( $matched_cats ) ) {
						$categories_in_group = true;
					}
				}
			} elseif ( 'on' === $all_products ) {
				$categories_in_group = true;
			}
		}
		return $categories_in_group;
	}

	/**
	 * Check if product is in any customer group
	 *
	 * @param int $product_id
	 * @return boolean
	 */
	public static function is_product_in_customer_groups( $product_id ) {

		$args          = array(
			'posts_per_page' => - 1,
			'post_type'      => 'customer_groups',
			'fields'         => 'ids',
		);
		$groups        = get_posts( $args );
		$active_groups = array();

		if ( isset( $groups ) && ! empty( $groups ) ) {
			foreach ( $groups as $group_id ) {

				$products_customer_group_meta = get_post_meta( $group_id, 'bm_products', true );
				$all_products                 = get_post_meta( $group_id, 'bm_all_products', true );
				$cat_customer_group_meta      = get_post_meta( $group_id, 'bm_categories', true );

				if ( 'on' == $all_products ) {
					array_push( $active_groups, $group_id );

				} elseif ( isset( $products_customer_group_meta ) && ! empty( $products_customer_group_meta ) ) {
					$products_customer_group = explode( ',', $products_customer_group_meta );
					foreach ( $products_customer_group as $product ) {
						if ( $product_id == $product ) {
							array_push( $active_groups, $group_id );
						}
					}
				} elseif ( isset( $cat_customer_group_meta ) && ! empty( $cat_customer_group_meta ) ) {

					$categories_customer_group = explode( ',', $cat_customer_group_meta );

					if ( isset( $categories_customer_group ) && ! empty( $categories_customer_group ) ) {
						$terms      = wp_get_post_terms( $product_id, 'product_cat' );
						$categories = array();

						if ( isset( $terms ) && ! empty( $terms ) ) {
							foreach ( $terms as $term ) {
								array_push( $categories, $term->term_id );
							}
						}
						$matched_cats = array_intersect( $categories_customer_group, $categories );
						if ( ! empty( $matched_cats ) ) {
							array_push( $active_groups, $group_id );
						}
					}
				}
			}
		}
		$current_groups = array_unique( $active_groups, SORT_REGULAR );

		return $current_groups;
	}
}

new BM_Conditionals();
