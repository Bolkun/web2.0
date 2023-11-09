<?php

class IE_Importer {

	/**
	 * @var array|mixed|object
	 */
	private $data;

	/**
	 * IE_Importer constructor.
	 */
	public function __construct() {

		$this->data = json_decode( get_option( 'import_options_raw_data' ), true );
		add_action( 'wp_ajax_trigger_import', array( $this, 'import' ) );
	}

	/**
	 * Runs the importer
	 *
	 * @return void
	 */
	public function import() {

		check_ajax_referer( 'start_export', 'security' );

		/* get data */
		$groups  = $this->get_groups( $this->data );
		$options = $this->get_options( $this->data );

		/* import data */
		$this->update_groups( $groups );
		$this->update_options( $options );

		/* clear raw data option */
		delete_option( 'import_options_raw_data' );
	}

	/**
	 * Get groups from data and build array
	 *
	 * @param array $data
	 * @return array
	 */
	private function get_groups( $data ) {

		$groups = array();
		if ( isset( $data ) && is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				if ( 'options' != $key ) {
					$groups[ $key ] = $value;
				}
			}
		}

		if ( get_option( 'import_b2b_example' ) == 'on' ) {
			$groups['b2b'] = $this->get_b2b_sample_group();
		}

		return $groups;
	}

	/**
	 * Get current options
	 *
	 * @param array $data
	 * @return array
	 */
	private function get_options( $data ) {

		$options = array();

		if ( isset( $data ) && is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				if ( 'options' == $key ) {
					$options = $value;
				}
			}

			return $options;
		}
	}

	/**
	 * Update groups meta
	 *
	 * @param array $groups
	 * @return void
	 */
	private function update_groups( $groups ) {

		if ( isset( $groups ) && is_array( $groups ) ) {

			foreach ( $groups as $group ) {

				$args = array(
					'post_type'      => 'customer_groups',
					'posts_per_page' => 1,
					'post_name__in'  => array( $group['slug'] ),
				);

				$existing_group = get_posts( $args );

				if ( empty( $existing_group[0] ) ) {
					$args = array(
						'post_title'   => $group['title'],
						'post_name'    => $group['slug'],
						'post_type'    => 'customer_groups',
						'post_content' => '',
						'post_status'  => 'publish',
					);

					$group_id = wp_insert_post( $args );

					if ( isset( $group['bm_products'] ) ) {
						update_post_meta( $group_id, 'bm_products', $group['bm_products'] );
					}
					if ( isset( $group['bm_categories'] ) ) {
						update_post_meta( $group_id, 'bm_categories', $group['bm_categories'] );
					}
					if ( isset( $group['bm_all_products'] ) ) {
						update_post_meta( $group_id, 'bm_all_products', $group['bm_all_products'] );
					}
					if ( isset( $group['bm_price'] ) ) {
						update_post_meta( $group_id, 'bm_price', $group['bm_price'] );
					}
					if ( isset( $group['bm_price_type'] ) ) {
						update_post_meta( $group_id, 'bm_price_type', $group['bm_price_type'] );
					}
					if ( isset( $group['bm_tax_type'] ) ) {
						update_post_meta( $group_id, 'bm_tax_type', $group['bm_tax_type'] );
					}
					if ( isset( $group['bm_tax_type'] ) ) {
						update_post_meta( $group_id, 'bm_vat_type', $group['bm_vat_type'] );
					}
					if ( isset( $group['bm_conditional_products'] ) ) {
						update_post_meta( $group_id, 'bm_conditional_products', $group['bm_conditional_products'] );
					}
					if ( isset( $group['bm_conditional_categories'] ) ) {
						update_post_meta( $group_id, 'bm_conditional_categories', $group['bm_conditional_categories'] );
					}
					if ( isset( $group['bm_conditional_all_products'] ) ) {
						update_post_meta( $group_id, 'bm_conditional_categories', $group['bm_conditional_all_products'] );
					}
					if ( isset( $group['bm_bulk_prices'] ) ) {
						update_post_meta( $group_id, 'bm_bulk_prices', $group['bm_bulk_prices'] );
					}
					if ( isset( $group['bm_discount'] ) ) {
						update_post_meta( $group_id, 'bm_discount', $group['bm_discount'] );
					}
					if ( isset( $group['bm_discount_type'] ) ) {
						update_post_meta( $group_id, 'bm_discount_type', $group['bm_discount_type'] );
					}
					if ( isset( $group['bm_discount_name'] ) ) {
						update_post_meta( $group_id, 'bm_discount_name', $group['bm_discount_name'] );
					}
					if ( isset( $group['bm_discount_products'] ) ) {
						update_post_meta( $group_id, 'bm_discount_products', $group['bm_discount_products'] );
					}
					if ( isset( $group['bm_discount_categories'] ) ) {
						update_post_meta( $group_id, 'bm_discount_categories', $group['bm_discount_categories'] );
					}
					if ( isset( $group['bm_discount_all_products'] ) ) {
						update_post_meta( $group_id, 'bm_discount_all_products', $group['bm_discount_all_products'] );
					}
					if ( isset( $group['bm_goods_discount'] ) ) {
						update_post_meta( $group_id, 'bm_goods_discount', $group['bm_goods_discount'] );
					}
					if ( isset( $group['bm_goods_discount_type'] ) ) {
						update_post_meta( $group_id, 'bm_goods_discount_type', $group['bm_goods_discount_type'] );
					}
					if ( isset( $group['bm_goods_discount_categories'] ) ) {
						update_post_meta( $group_id, 'bm_goods_discount_categories', $group['bm_goods_discount_categories'] );
					}
					if ( isset( $group['bm_goods_discount_type'] ) ) {
						update_post_meta( $group_id, 'bm_goods_discount_type', $group['bm_goods_discount_type'] );
					}
					if ( isset( $group['bm_goods_discount_categories'] ) ) {
						update_post_meta( $group_id, 'bm_goods_discount_categories', $group['bm_goods_discount_categories'] );
					}
					if ( isset( $group['bm_goods_product_count'] ) ) {
						update_post_meta( $group_id, 'bm_goods_product_count', $group['bm_goods_product_count'] );
					}
				} else {

					if ( isset( $group['bm_products'] ) ) {
						update_post_meta( $existing_group[0]->ID, 'bm_products', $group['bm_products'] );
					}
					if ( isset( $group['bm_categories'] ) ) {
						update_post_meta( $existing_group[0]->ID, 'bm_categories', $group['bm_categories'] );
					}
					if ( isset( $group['bm_all_products'] ) ) {
						update_post_meta( $existing_group[0]->ID, 'bm_all_products', $group['bm_all_products'] );
					}
					if ( isset( $group['bm_price'] ) ) {
						update_post_meta( $existing_group[0]->ID, 'bm_price', $group['bm_price'] );
					}
					if ( isset( $group['bm_price_type'] ) ) {
						update_post_meta( $existing_group[0]->ID, 'bm_price_type', $group['bm_price_type'] );
					}
					if ( isset( $group['bm_tax_type'] ) ) {
						update_post_meta( $existing_group[0]->ID, 'bm_tax_type', $group['bm_tax_type'] );
					}
					if ( isset( $group['bm_tax_type'] ) ) {
						update_post_meta( $existing_group[0]->ID, 'bm_vat_type', $group['bm_vat_type'] );
					}
					if ( isset( $group['bm_conditional_products'] ) ) {
						update_post_meta( $existing_group[0]->ID, 'bm_conditional_products', $group['bm_conditional_products'] );
					}
					if ( isset( $group['bm_conditional_categories'] ) ) {
						update_post_meta( $existing_group[0]->ID, 'bm_conditional_categories', $group['bm_conditional_categories'] );
					}
					if ( isset( $group['bm_conditional_all_products'] ) ) {
						update_post_meta( $existing_group[0]->ID, 'bm_conditional_categories', $group['bm_conditional_all_products'] );
					}
					if ( isset( $group['bulk_prices'] ) ) {
						update_post_meta( $existing_group[0]->ID, 'bm_bulk_prices', $group['bulk_prices'] );
					}
					if ( isset( $group['bm_discount'] ) ) {
						update_post_meta( $existing_group[0]->ID, 'bm_discount', $group['bm_discount'] );
					}
					if ( isset( $group['bm_discount_type'] ) ) {
						update_post_meta( $existing_group[0]->ID, 'bm_discount_type', $group['bm_discount_type'] );
					}
					if ( isset( $group['bm_discount_name'] ) ) {
						update_post_meta( $existing_group[0]->ID, 'bm_discount_name', $group['bm_discount_name'] );
					}
					if ( isset( $group['bm_discount_products'] ) ) {
						update_post_meta( $existing_group[0]->ID, 'bm_discount_products', $group['bm_discount_products'] );
					}
					if ( isset( $group['bm_discount_categories'] ) ) {
						update_post_meta( $existing_group[0]->ID, 'bm_discount_categories', $group['bm_discount_categories'] );
					}
					if ( isset( $group['bm_discount_all_products'] ) ) {
						update_post_meta( $existing_group[0]->ID, 'bm_discount_all_products', $group['bm_discount_all_products'] );
					}
					if ( isset( $group['bm_goods_discount'] ) ) {
						update_post_meta( $existing_group[0]->ID, 'bm_goods_discount', $group['bm_goods_discount'] );
					}
					if ( isset( $group['bm_goods_discount_type'] ) ) {
						update_post_meta( $existing_group[0]->ID, 'bm_goods_discount_type', $group['bm_goods_discount_type'] );
					}
					if ( isset( $group['bm_goods_discount_categories'] ) ) {
						update_post_meta( $existing_group[0]->ID, 'bm_goods_discount_categories', $group['bm_goods_discount_categories'] );
					}
					if ( isset( $group['bm_goods_discount_type'] ) ) {
						update_post_meta( $existing_group[0]->ID, 'bm_goods_discount_type', $group['bm_goods_discount_type'] );
					}
					if ( isset( $group['bm_goods_discount_categories'] ) ) {
						update_post_meta( $existing_group[0]->ID, 'bm_goods_discount_categories', $group['bm_goods_discount_categories'] );
					}
					if ( isset( $group['bm_goods_product_count'] ) ) {
						update_post_meta( $existing_group[0]->ID, 'bm_goods_product_count', $group['bm_goods_product_count'] );
					}
				}
				/* create user role if not exists */
				$role = new BM_User();
				$role->add_customer_group( $group_id );
			}
		}
	}

	/**
	 * Update options
	 *
	 * @param array $options
	 * @return void
	 */
	private function update_options( $options ) {

		if ( isset( $options ) && is_array( $options ) ) {
			foreach ( $options as $key => $value ) {
				if ( false !== $value ) {
					update_option( $key, $value );
				}
			}
		}
	}
	/**
	 * Get b2b group
	 *
	 * @return void
	 */
	private function get_b2b_sample_group() {

		$b2b = array(
			'title'                       => 'B2B',
			'slug'                        => 'b2b',
			'bm_price'                    => '10.50',
			'bm_price_type'               => 'Fixed Price',
			'bm_tax_type'                 => 'on',
			'bm_vat_type'                 => 'on',
			'bm_all_products'             => 'on',
			'bm_conditional_all_products' => 'off',
			'bm_discount_all_products'    => 'off',
			'bm_bulk_prices'              => array(
				array(
					'bulk_price' => '5',
					'bulk_price_from' => '3',
					'bulk_price_to' => '5',
					'bulk_price_type' => 'fix',
				),
				array(
					'bulk_price' => '2.5',
					'bulk_price_from' => '10',
					'bulk_price_to' => '20',
					'bulk_price_type' => 'fix',
				),
			),
		);

		return $b2b;
	}
}

new IE_Importer();
