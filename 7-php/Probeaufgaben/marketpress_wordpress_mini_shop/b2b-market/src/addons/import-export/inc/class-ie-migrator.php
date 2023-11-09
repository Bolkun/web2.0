<?php

class IE_Migrator {

	/**
	 * IE_Migrator constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_trigger_migration', array( $this, 'migrate' ) );
	}

	/**
	 * initialize migrate process
	 */
	public function migrate() {

		$groups = $this->get_groups();

		foreach ( $groups as $group_name ) {

			$group = get_page_by_title( $group_name, OBJECT, 'customer_groups' );

			if ( ! is_null( $group ) ) {
				$group_slug = $group->post_name;
			} else {
				$special_chars = array( ' ', '/', '_' );
				$replace_chars = array( '-', '-', '-' );
				$group_slug    = str_replace( $special_chars, $replace_chars, strtolower( $group_name ) );

				$umlaute    = array( 'ä', 'ü', 'ö' );
				$umlaute_en = array( 'ae', 'ue', 'oe' );

				$group_slug = str_replace( $umlaute, $umlaute_en, $group_slug );
			}

			if ( is_null( $group ) ) {
				$group_id = $this->migrate_group( $group_slug, $group_name );
			} else {
				$group_id = $group->ID;
			}

			$this->migrate_user_with_group_meta( $group_slug );
			$this->migrate_products( $group_id, $group_slug );
			$this->migrate_variations( $group_id, $group_slug );
			$this->migrate_global_prices();
			$this->migrate_whitelist( $group_id, $group_slug );

			$rbp_live_price_update = get_option( 'rbp_bulk_pricing_ajax_update' );

			if ( isset( $rbp_live_price_update ) && 'yes' === $rbp_live_price_update ) {
				update_option( 'enable_total_price_calculation', 'on' );
			}
		}
	}

	/**
	 * get all rbp defined groups
	 *
	 * @return mixed|string|void
	 */
	public function get_groups() {

		$rbp_groups = get_option( 'rbp_defined_customer_groups' );

		if ( isset( $rbp_groups ) && ! empty( $rbp_groups ) ) {
			return $rbp_groups;
		}
	}

	/**
	 * create customer group and user role
	 *
	 * @param $group
	 * @param $group_name
	 */
	public function migrate_group( $group_slug, $group_name ) {

		$args = array(
			'post_type'    => 'customer_groups',
			'post_title'   => $group_name,
			'post_content' => '',
			'post_status'  => 'publish',
		);

		$group_id = wp_insert_post( $args );

		$role = get_role( $group_slug );

		if ( null == $role ) {
			add_role( $group_slug, $group_name, array(
				'read'    => true,
				'level_0' => true,
			) );
		}
		return $group_id;
	}

	/**
	 * get rules for given product
	 *
	 * @param $product_id
	 *
	 * @return array
	 */
	public function get_product_rules( $product_id ) {

		$rbp_data        = '';
		$bm_product_data = array();
		$rbp_plugins     = get_option( 'rbp_active_plugins' );

		$rbp_config = get_post_meta( $product_id, 'rbp_role_config', true );

		if ( isset( $rbp_config ) && ! empty( $rbp_config ) ) {
			$rbp_data = $rbp_config;
		}

		if ( isset( $rbp_data ) && ! empty( $rbp_data ) ) {

			foreach ( $rbp_data as $group_slug => $values ) {

				/* old format */
				if ( isset( $values['group_price']['price_type'] ) && ! empty( $values['group_price']['price_type'] ) ) {
					$rbp_price_type = $values['group_price']['price_type'];
				} else {
					$rbp_price_type = '';
				}
				if ( isset( $values['bulk_price'] ) && ! empty( $values['bulk_price'] ) ) {
					$rbp_bulk = $values['bulk_price'];
				} else {
					$rbp_bulk = '';
				}

				/* new format */
				$group_price      = floatval( $values['group_price']['price'] );
				$group_price_type = '';

				/* modify data */
				switch ( $rbp_price_type ) {
					case 'currency':
						$group_price_type = 'fix';
						break;
					case 'discount':
						$group_price_type = 'discount';
						break;
					case 'percent':
						$group_price_type = 'discount-percent';
						break;
				}
				if ( array_key_exists( 'bulk-pricing.php', $rbp_plugins ) ) {
					/* build array for bulk prices */
					$bulks = array();

					if ( isset( $rbp_bulk ) && ! empty( $rbp_bulk ) ) {
						foreach ( $rbp_bulk as $bulk ) {

							switch ( $bulk['price_type'] ) {
								case 'currency':
									$bulk_price_type = 'fix';
									break;
								case 'discount':
									$bulk_price_type = 'discount';
									break;
								case 'percent':
									$bulk_price_type = 'discount-percent';
									break;
							}

							$bulk_array = array(
								'bulk_price'    => floatval( $bulk['price'] ),
								'bulk_price_from' => intval( $bulk['from'] ),
								'bulk_price_to' => intval( $bulk['to'] ),
								'bulk_price_type' => $bulk_price_type,
							);

							array_push( $bulks, $bulk_array );
						}
					}

					/* build array to return for migrate */
					$bm_product_data[ 'bm_' . $group_slug . '_price' ]       = $group_price;
					$bm_product_data[ 'bm_' . $group_slug . '_price_type' ]  = $group_price_type;
					$bm_product_data[ 'bm_' . $group_slug . '_bulk_prices' ] = $bulks;
				}
			}
		}
		return $bm_product_data;

	}

		/**
	 * get rules for given product
	 *
	 * @param $product_id
	 *
	 * @return array
	 */
	public function get_variation_rules( $variation_id ) {

		$rbp_data        = '';
		$bm_product_data = array();
		$rbp_plugins     = get_option( 'rbp_active_plugins' );

		$rbp_config = get_post_meta( $variation_id, 'rbp_role_config', true );

		if ( isset( $rbp_config ) && ! empty( $rbp_config ) ) {
			$rbp_data = $rbp_config;
		}

		if ( isset( $rbp_data ) && ! empty( $rbp_data ) ) {

			foreach ( $rbp_data as $group_slug => $values ) {

				/* old format */
				if ( isset( $values['group_price']['price_type'] ) && ! empty( $values['group_price']['price_type'] ) ) {
					$rbp_price_type = $values['group_price']['price_type'];
				} else {
					$rbp_price_type = '';
				}
				if ( isset( $values['bulk_price'] ) && ! empty( $values['bulk_price'] ) ) {
					$rbp_bulk = $values['bulk_price'];
				} else {
					$rbp_bulk = '';
				}

				/* new format */
				$group_price      = '';
				$group_price_type = '';

				if ( isset( $values['group_price']['price'] ) ) {
					$group_price = floatval( str_replace( ',', '.', $values['group_price']['price'] ) );
				}

				/* modify data */
				switch ( $rbp_price_type ) {
					case 'currency':
						$group_price_type = 'fix';
						break;
					case 'discount':
						$group_price_type = 'discount';
						break;
					case 'percent':
						$group_price_type = 'discount-percent';
						break;
				}
				if ( array_key_exists( 'bulk-pricing.php', $rbp_plugins ) ) {
					/* build array for bulk prices */
					$bulks = array();

					if ( isset( $rbp_bulk ) && ! empty( $rbp_bulk ) ) {
						foreach ( $rbp_bulk as $bulk ) {

							switch ( $bulk['price_type'] ) {
								case 'currency':
									$bulk_price_type = 'fix';
									break;
								case 'discount':
									$bulk_price_type = 'discount';
									break;
								case 'percent':
									$bulk_price_type = 'discount-percent';
									break;
							}

							$bulk_array = array(
								'bulk_price'      => floatval( str_replace( ',', '.', $bulk['price'] ) ),
								'bulk_price_from' => intval( $bulk['from'] ),
								'bulk_price_to'   => intval( $bulk['to'] ),
								'bulk_price_type' => $bulk_price_type,
							);

							array_push( $bulks, $bulk_array );
						}
					}

					/* build array to return for migrate */
					$bm_product_data[ 'bm_' . $group_slug . '_' . $variation_id . '_price' ]       = $group_price;
					$bm_product_data[ 'bm_' . $group_slug . '_' . $variation_id . '_price_type' ]  = $group_price_type;
					$bm_product_data[ 'bm_' . $group_slug . '_' . $variation_id . '_bulk_prices' ] = $bulks;
				}
			}
		}

		return $bm_product_data;

	}

	/**
	 * migrate product rules
	 */
	public function migrate_products( $group_id, $group_slug ) {

		$old_global_prices = get_option( 'global_discounts_config' );

		$args = array(
			'posts_per_page' => - 1,
			'post_type'      => 'product',
			'fields'         => 'ids',
		);

		$products = get_posts( $args );

		foreach ( $products as $product_id ) {

			$bm_data = $this->get_product_rules( $product_id );

			if ( isset( $bm_data ) && ! empty( $bm_data ) && ! is_null( $bm_data ) ) {

				$bm_products_meta = get_post_meta( $group_id, 'bm_products', true );
				$bm_products      = explode( ',', $bm_products_meta );

				if ( ! in_array( $product_id, $bm_products ) ) {
					$bm_products[] = $product_id;
				}

				if ( isset( $old_global_prices[ $group_slug ]['active'] ) && ! is_null( $old_global_prices[ $group_slug ]['active'] ) ) {
					update_post_meta( $group_id, 'bm_all_products', 'on' );
				} else {
					update_post_meta( $group_id, 'bm_products', implode( ',', $bm_products ) );
				}

				foreach ( $bm_data as $key => $value ) {
					update_post_meta( $product_id, $key, $value );
				}
			}
			$product = wc_get_product( $product_id );
			$product->save();
		}

	}

		/**
	 * migrate variation rules
	 */
	public function migrate_variations( $group_id, $group_slug ) {

		$old_global_prices = get_option( 'global_discounts_config' );

		$args = array(
			'posts_per_page' => - 1,
			'post_type'      => 'product',
			'fields'         => 'ids',
		);

		$products = get_posts( $args );

		foreach ( $products as $product_id ) {

			$_product = wc_get_product( $product_id );

			if ( ! is_bool( $_product ) ) {
				if ( $_product->is_type( 'variable' ) ) {

					$args = array(
						'post_type'   => 'product_variation',
						'post_status' => array( 'private', 'publish' ),
						'numberposts' => -1,
						'orderby'     => 'menu_order',
						'order'       => 'asc',
						'post_parent' => $_product->get_id(),
						'fields'      => 'ids',
					);

					$variations = get_posts( $args );

					foreach ( $variations as $variation_id ) {

						$variation_of_product = wc_get_product( $variation_id );

						$bm_data = $this->get_variation_rules( $variation_id );

						if ( isset( $bm_data ) && ! empty( $bm_data ) && ! is_null( $bm_data ) ) {

							$bm_products_meta = get_post_meta( $group_id, 'bm_products', true );
							$bm_products      = explode( ',', $bm_products_meta );

							if ( ! in_array( $variation_of_product->get_parent_id(), $bm_products ) ) {
								$bm_products[] = $variation_of_product->get_parent_id();
							}

							if ( isset( $old_global_prices[ $group_slug ]['active'] ) && ! is_null( $old_global_prices[ $group_slug ]['active'] ) ) {
								update_post_meta( $group_id, 'bm_all_products', 'on' );
							} else {
								update_post_meta( $group_id, 'bm_products', implode( ',', $bm_products ) );
							}

							foreach ( $bm_data as $key => $value ) {
								update_post_meta( $variation_id, $key, $value );
								update_post_meta( $variation_of_product->get_parent_id(), $key, $value );
							}
						}
						$variation_of_product->save();
					}
				}
			}
		}

	}

	/**
	 * migrate global price options
	 */
	public function migrate_global_prices() {

		$rbp_plugins       = get_option( 'rbp_active_plugins' );
		$old_global_prices = get_option( 'global_discounts_config' );

		if ( ! is_null( $old_global_prices ) && array_key_exists( 'global-discounts.php', $rbp_plugins ) ) {

			foreach ( $old_global_prices as $key => $value ) {

				/* get old meta */
				$rbp_group_price      = $value['price'];
				$rbp_group_price_type = $value['price_type'];

				if ( isset( $value['active'] ) ) {
					if ( '__global' === $key ) {

						update_option( 'bm_global_base_price', $rbp_group_price );

						switch ( $rbp_group_price_type ) {
							case 'currency':
								$bm_price_type = 'fix';
								break;
							case 'discount':
								$bm_price_type = 'discount';
								break;
							case 'percent':
								$bm_price_type = 'discount-percent';
								break;
						}

						update_option( 'bm_global_base_price_type', $bm_price_type );

					} else {

						$group = get_page_by_path( $key, OBJECT, 'customer_groups' );

						switch ( $rbp_group_price_type ) {
							case 'currency':
								$bm_price_type = 'fix';
								break;
							case 'discount':
								$bm_price_type = 'discount';
								break;
							case 'percent':
								$bm_price_type = 'discount-percent';
								break;
						}
						if ( ! is_null( $group ) ) {
							update_post_meta( $group->ID, 'bm_price', $rbp_group_price );
							update_post_meta( $group->ID, 'bm_price_type', $bm_price_type );
						}
					}
				}
			}
		}
	}

	/**
	 * get visibility status from group slug
	 *
	 * @param $group
	 *
	 * @return bool
	 */
	public function get_group_visibility_status( $group ) {

		$hidden = false;

		$rbp_groups      = get_option( 'rbp_defined_customer_groups' );
		$rbp_group_slugs = array();

		if ( isset( $rbp_groups ) && ! empty( $rbp_groups ) ) {

			foreach ( $rbp_groups as $key => $value ) {
				array_push( $rbp_group_slugs, $key );
			}

			if ( in_array( $group, $rbp_group_slugs ) ) {
				$hidden = true;
			}
		}
		return $hidden;
	}

	/**
	 * @param $product_id
	 *
	 * @return array
	 */
	public function get_product_whitelist_data( $product_id ) {

		$product       = wc_get_product( $product_id );
		$whitelist_raw = array();
		$rbp_roles     = wp_get_post_terms( $product->get_id(), 'rbp_role', array( "fields" => "all" ) );

		foreach ( $rbp_roles as $rbp_role ) {

			$status = $this->get_group_visibility_status( $rbp_role->slug );

			/*  get current product ID and check $status for true and false */

			if ( true == $status ) {

				/* add product list for meta: bm_conditional_products and bm_products */

				$customer_group = get_page_by_path( $rbp_role->slug, OBJECT, 'customer_groups' );

				if ( ! empty( $customer_group->ID ) ) {
					$whitelist_raw[ $customer_group->ID ] = $product->get_id();
				}
			}
		}
		return $whitelist_raw;
	}


	/**
	 * do migration for whitelist
	 */
	public function migrate_whitelist( $group_id, $group_slug ) {

		$rbp_plugins = get_option( 'rbp_active_plugins' );

		if ( array_key_exists( 'hide-products.php', $rbp_plugins ) ) {

			$mode          = get_option( 'rbp_hide_products_use_whitelist' ); // yes = whitelist  or no = blacklist
			$global_hidden = get_option( 'rbp_global_hidden_products' );

			if ( 'no' === $mode || in_array( $group_slug, $global_hidden ) ) {
					update_post_meta( $group_id, 'bm_conditional_all_products', 'off' );
			} elseif ( 'yes' === $mode || ! in_array( $group_slug, $global_hidden ) ) {
					update_post_meta( $group_id, 'bm_conditional_all_products', 'on' );
			}

			$whitelist = array();

			$args = array(
				'posts_per_page' => - 1,
				'post_type'      => 'product',
				'fields'         => 'ids',
			);

			$products = get_posts( $args );

			foreach ( $products as $product_id ) {

				$whitelist_entry = $this->get_product_whitelist_data( $product_id );

				if ( ! empty( $whitelist_entry ) ) {

					foreach ( $whitelist_entry as $key => $value ) {

						if ( ! key_exists( $key, $whitelist ) ) {
							$whitelist[ $key ] = ',' . $value;
						} else {
							$whitelist[ $key ] = $whitelist[ $key ] . ',' . $value;
						}
					}
				}
			}

			foreach ( $whitelist as $key => $value ) {

				$bm_products_meta = get_post_meta( $key, 'bm_conditional_products', true );
				$bm_products      = explode( ',', $bm_products_meta );

				if ( ! in_array( $value, $bm_products ) ) {
					$bm_products[] = $value;
				}
				update_post_meta( $key, 'bm_conditional_products', implode( ',', $bm_products ) );
			}
		}
	}


	/**
	 * migrate users
	 *
	 * @param $group
	 */
	public function migrate_user_with_group_meta( $group_slug ) {

		$args = array(
			'meta_key'   => 'customer_group',
			'meta_value' => $group_slug,
		);

		$users_to_modify = get_users( $args );

		foreach ( $users_to_modify as $user ) {

			$role_to_assign = get_role( $group_slug );
			$current_user   = new WP_User( $user->ID );
			$standard_roles = array( 'contributor', 'author', 'editor', 'administrator' );
			$old_roles      = $current_user->roles;

			foreach ( $old_roles  as $role ) {
				if ( ! in_array( $role, $standard_roles ) ) {
					$user = new WP_User( $user->ID );
					$user->set_role( $group_slug );
				}
			}
		}
	}

	/**
	 * Migrate 1.0.1 bulk prices to 1.0.2
	 *
	 * @return void
	 */
	public static function migrate_101_bulk_prices() {

		update_option( 'bm_global_bulk_prices', array() );

		$product_args = array(
			'posts_per_page' => - 1,
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'fields'         => 'ids',
		);

		$groups_args = array(
			'posts_per_page' => - 1,
			'post_type'      => 'customer_groups',
			'post_status'    => 'publish',
		);

		$groups   = get_posts( $groups_args );
		$products = get_posts( $product_args );

		foreach ( $products as $product_id ) {

			$product = wc_get_product( $product_id );

			if ( $product->is_type( 'variable' ) ) {

				$variations = $product->get_available_variations();

				foreach ( $variations as $key => $value ) {

					$variation_id    = $value['variation_id'];

					foreach ( $groups as $group ) {

						$old_bulk_prices = get_post_meta( $product_id, 'bm_' . $group->post_name . '_' . $variation_id . '_bulk_prices', true );
						$new_bulk_prices = array();

						if ( isset( $old_bulk_prices ) && ! empty( $old_bulk_prices ) ) {
							foreach ( $old_bulk_prices as $bulk_prices ) {

								if ( 0 !== $bulk_prices[ $group->post_name . '_' . $variation_id . '_bulk_price' ] && ! empty( $bulk_prices[ $group->post_name . '_' . $variation_id . '_bulk_price' ] ) ) {

									if ( isset( $bulk_prices[ $group->post_name . '_' . $variation_id . '_bulk_price' ] ) ) {
										$bulk_prices['bulk_price']      = $bulk_prices[ $group->post_name . '_' . $variation_id . '_bulk_price' ];
										unset( $bulk_prices[ $group->post_name . '_' . $variation_id . '_bulk_price' ] );
									}

									if ( isset( $bulk_prices[ $group->post_name . '_' . $variation_id . '_bulk_price_from' ] ) ) {
										$bulk_prices['bulk_price_from'] = $bulk_prices[ $group->post_name . '_' . $variation_id . '_bulk_price_from' ];
										unset( $bulk_prices[ $group->post_name . '_' . $variation_id . '_bulk_price_from' ] );
									}

									if ( isset( $bulk_prices[ $group->post_name . '_' . $variation_id . '_bulk_price_to' ] ) ) {
										$bulk_prices['bulk_price_to']   = $bulk_prices[ $group->post_name . '_' . $variation_id . '_bulk_price_to' ];
										unset( $bulk_prices[ $group->post_name . '_' . $variation_id . '_bulk_price_to' ] );
									}

									if ( isset( $bulk_prices[ $group->post_name . '_' . $variation_id . '_bulk_price_type' ] ) ) {
										$bulk_prices['bulk_price_type'] = $bulk_prices[ $group->post_name . '_' . $variation_id . '_bulk_price_type' ];
										unset( $bulk_prices[ $group->post_name . '_' . $variation_id . '_bulk_price_type' ] );
									}

									array_push( $new_bulk_prices, $bulk_prices );
								}
							}
							update_post_meta( $product_id, 'bm_' . $group->post_name . '_' . $variation_id . '_bulk_prices', $new_bulk_prices );
						}
					}
				}
			} else {
				foreach ( $groups as $group ) {

					$old_bulk_prices = get_post_meta( $product_id, 'bm_' . $group->post_name . '_bulk_prices', true );
					$new_bulk_prices = array();

					if ( isset( $old_bulk_prices ) && ! empty( $old_bulk_prices ) ) {
						foreach ( $old_bulk_prices as $bulk_prices ) {

							if ( 0 !== $bulk_prices[ $group->post_name . '_bulk_price' ] && ! empty( $bulk_prices[ $group->post_name . '_bulk_price' ] ) ) {

								if ( isset( $bulk_prices[ $group->post_name . '_bulk_price' ] ) ) {
									$bulk_prices['bulk_price']      = $bulk_prices[ $group->post_name . '_bulk_price' ];
									unset( $bulk_prices[ $group->post_name . '_bulk_price' ] );
								}

								if ( isset( $bulk_prices[ $group->post_name . '_bulk_price_from' ] ) ) {
									$bulk_prices['bulk_price_from'] = $bulk_prices[ $group->post_name . '_bulk_price_from' ];
									unset( $bulk_prices[ $group->post_name . '_bulk_price_from' ] );
								}

								if ( isset( $bulk_prices[ $group->post_name . '_bulk_price_to' ] ) ) {
									$bulk_prices['bulk_price_to']   = $bulk_prices[ $group->post_name . '_bulk_price_to' ];
									unset( $bulk_prices[ $group->post_name . '_bulk_price_to' ] );
								}

								if ( isset( $bulk_prices[ $group->post_name . '_bulk_price_type' ] ) ) {
									$bulk_prices['bulk_price_type'] = $bulk_prices[ $group->post_name . '_bulk_price_type' ];
									unset( $bulk_prices[ $group->post_name . '_bulk_price_type' ] );
								}

								array_push( $new_bulk_prices, $bulk_prices );
							}
						}
						update_post_meta( $product_id, 'bm_' . $group->post_name . '_bulk_prices', $new_bulk_prices );
					}
				}
			}
		}
	}
}

new IE_Migrator();
