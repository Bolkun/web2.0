<?php

class IE_Exporter {

	/**
	 * @var array
	 */
	private $groups;


	/**
	 * IE_Exporter constructor.
	 */
	public function __construct() {
		$groups       = new BM_User();
		$this->groups = $groups->get_all_customer_groups();

		add_action( 'wp_ajax_trigger_export', array( $this, 'trigger_export' ) );
		add_action( 'wp_head', array( $this, 'get_export_options' ) );
	}

	/**
	 * @return array
	 */
	public function get_export_options() {

		$export_groups = array();

		if ( isset( $this->groups ) && ! empty( $this->groups ) ) {
			foreach ( $this->groups as $group ) {
				if ( isset( $group ) && ! empty( $group ) ) {
					foreach ( $group as $key => $value ) {
						if ( get_option( 'export_' . $key ) == 'on' ) {
							array_push( $export_groups, $value );
						}
					}
				}
			}
		}

		return $export_groups;
	}

	/**
	 * @param $export_groups
	 *
	 * @return array
	 */
	public function get_export_data( $export_groups ) {

		$export_data = array();

		if ( isset( $export_groups ) && ! empty( $export_groups ) ) {
			foreach ( $export_groups as $group ) {

				$group = get_post( $group );

				/* customer groups */
				$export_data[ $group->post_name ]['title']                        = $group->post_title;
				$export_data[ $group->post_name ]['slug']                         = $group->post_name;
				$export_data[ $group->post_name ]['bm_products']                  = get_post_meta( $group->ID, 'bm_products', true );
				$export_data[ $group->post_name ]['bm_categories']                = get_post_meta( $group->ID, 'bm_categories', true );
				$export_data[ $group->post_name ]['bm_all_products']              = get_post_meta( $group->ID, 'bm_all_products', true );
				$export_data[ $group->post_name ]['bm_price']                     = get_post_meta( $group->ID, 'bm_price', true );
				$export_data[ $group->post_name ]['bm_price_type']                = get_post_meta( $group->ID, 'bm_price_type', true );
				$export_data[ $group->post_name ]['bm_conditional_products']      = get_post_meta( $group->ID, 'bm_conditional_products', true );
				$export_data[ $group->post_name ]['bm_conditional_categories']    = get_post_meta( $group->ID, 'bm_conditional_categories', true );
				$export_data[ $group->post_name ]['bm_conditional_all_products']  = get_post_meta( $group->ID, 'bm_conditional_all_products', true );
				$export_data[ $group->post_name ]['bm_bulk_prices']               = get_post_meta( $group->ID, 'bm_bulk_prices', true );
				$export_data[ $group->post_name ]['bm_discount']                  = get_post_meta( $group->ID, 'bm_discount', true );
				$export_data[ $group->post_name ]['bm_discount_type']             = get_post_meta( $group->ID, 'bm_discount_type', true );
				$export_data[ $group->post_name ]['bm_discount_name']             = get_post_meta( $group->ID, 'bm_discount_name', true );
				$export_data[ $group->post_name ]['bm_discount_products']         = get_post_meta( $group->ID, 'bm_discount_products', true );
				$export_data[ $group->post_name ]['bm_discount_categories']       = get_post_meta( $group->ID, 'bm_discount_categories', true );
				$export_data[ $group->post_name ]['bm_discount_all_products']     = get_post_meta( $group->ID, 'bm_discount_all_products', true );
				$export_data[ $group->post_name ]['bm_goods_discount']            = get_post_meta( $group->ID, 'bm_goods_discount', true );
				$export_data[ $group->post_name ]['bm_goods_discount_type']       = get_post_meta( $group->ID, 'bm_goods_discount_type', true );
				$export_data[ $group->post_name ]['bm_goods_discount_categories'] = get_post_meta( $group->ID, 'bm_goods_discount_categories', true );
				$export_data[ $group->post_name ]['bm_goods_product_count']       = get_post_meta( $group->ID, 'bm_goods_product_count', true );
				$export_data[ $group->post_name ]['bm_tax_type']                  = get_post_meta( $group->ID, 'bm_tax_type', true );
				$export_data[ $group->post_name ]['bm_vat_type']                  = get_post_meta( $group->ID, 'bm_vat_type', true );

				/* addon options */
				if ( get_option( 'export_plugin_settings' ) !== 'off' ) {

					/* options */
					$export_data['options']['bm_global_price_label']                       = get_option( 'bm_global_price_label' );
					$export_data['options']['enable_total_price_calculation']              = get_option( 'enable_total_price_calculation' );
					$export_data['options']['deactivate_whitelist_hooks']                  = get_option( 'deactivate_whitelist_hooks' );
					$export_data['options']['deactivate_whitelist_admin']                  = get_option( 'deactivate_whitelist_admin' );
					$export_data['options']['bm_global_discount_message']                  = get_option( 'bm_global_discount_message' );
					$export_data['options']['bm_global_discount_message_background_color'] = get_option( 'bm_global_discount_message_background_color' );
					$export_data['options']['bm_global_discount_message_font_color']       = get_option( 'bm_global_discount_message_font_color' );

					if ( get_option( 'bm_addon_shipping_and_payment' ) !== 'off' ) {

						$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
						$shipping           = WC_Shipping::instance();
						$shipping->get_shipping_methods();
						$shipping_methods = $shipping->get_shipping_methods();

						if ( isset( $available_gateways ) && ! empty( $available_gateways ) ) {
							foreach ( $available_gateways as $gateway ) {
								$export_data['options'][ $gateway->id . '_' . $group->post_name ] = get_option( 'enable_' . $gateway->id . '_' . $group->post_name );
							}
						}
						if ( isset( $shipping_methods ) && ! empty( $shipping->get_shipping_methods() ) ) {
							foreach ( $shipping->get_shipping_methods() as $method ) {
								$export_data['options'][ $method->id . '_' . $group->post_name ] = get_option( 'enable_' . $method->id . '_' . $group->post_name );
							}
						}
					}
				}
			}
		}

		return $export_data;
	}

	/**
	 * triggered from ajaxcall
	 */
	public function trigger_export() {

		check_ajax_referer( 'start_export', 'security' );

		$export_groups = $this->get_export_options();
		$export_data   = $this->get_export_data( $export_groups );

		update_option( 'export_options_raw_data', json_encode( $export_data ) );

	}


}

new IE_Exporter();
