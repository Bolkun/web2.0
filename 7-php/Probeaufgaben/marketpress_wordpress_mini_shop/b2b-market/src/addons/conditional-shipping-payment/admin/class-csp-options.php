<?php

class CSP_Options {

	/**
	 * @param $items
	 *
	 * @return mixed
	 */
	public function add_menu_item( $items ) {

		$items[3] = array(
			'title'    => __( 'Shipping and Payment', 'b2b-market' ),
			'slug'     => 'shipping_and_payment',
			'callback' => array( $this, 'shipping_and_payment_tab' ),
			'options'  => false,
			'submenu'  => array(

				array(
					'title'    => __( 'Payment', 'b2b-market' ),
					'slug'     => 'payment',
					'callback' => array( $this, 'payment_tab' ),
					'options'  => 'yes',
				),
				array(
					'title'    => __( 'Shipping', 'b2b-market' ),
					'slug'     => 'shipping',
					'callback' => array( $this, 'shipping_tab' ),
					'options'  => 'yes',
				),
			),
		);

		return $items;

	}

	/**
	 * @return array|mixed|void
	 */
	public function payment_tab() {

		$gateways = new WC_Payment_Gateways();
		$options  = array();
		$groups   = new BM_User();

		foreach ( $groups->get_all_customer_groups() as $group ) {

			foreach ( $group as $key => $value ) {

				$title = array(
					'name' => __( 'Customer Group: ', 'b2b-market' ) . ucfirst( $key ),
					'type' => 'title',
					'id'   => 'payment_options' . $key,
				);

				array_push( $options, $title );

				foreach ( $gateways->payment_gateways() as $gateway ) {

					$settings = $gateway->settings;

					if ( 'yes' == $settings['enabled'] ) {
						$default = 'on';

						if ( get_option( 'enable_' . $gateway->id . '_' . $key ) === false ) {
							update_option( 'enable_' . $gateway->id . '_' . $key, 'on' );
						}
					} else {
						$default = 'off';

						if ( get_option( 'enable_' . $gateway->id . '_' . $key ) === false ) {
							update_option( 'enable_' . $gateway->id . '_' . $key, 'off' );
						}
					}

					$gateway_option = array(
						'name'     => $gateway->title,
						'desc_tip' => $gateway->method_description,
						'id'       => 'enable_' . $gateway->id . '_' . $key,
						'type'     => 'bm_ui_checkbox',
						'default'  => $default,
					);

					array_push( $options, $gateway_option );
				}

				$end = array(
					'type' => 'sectionend',
					'id'   => 'payment_options',
				);

				array_push( $options, $end );

			}
		}

		$options = apply_filters( 'woocommerce_bm_ui_payments', $options );

		return $options;
	}

	/**
	 * @return array|mixed|void
	 */
	public function shipping_tab() {

		$options = array();
		$groups  = new BM_User();

		foreach ( $groups->get_all_customer_groups() as $group ) {

			foreach ( $group as $key => $value ) {

				$title = array(
					'name' => __( 'Customer Group: ', 'b2b-market' ) . ucfirst( $key ),
					'type' => 'title',
					'id'   => 'shipping_options' . $key,
				);

				array_push( $options, $title );

				foreach ( WC()->shipping()->load_shipping_methods() as $shipping_method ) {

					if ( isset( $shipping_method->enabled ) && 'yes' === $shipping_method->enabled ) {

						$default = 'on';

						if ( get_option( 'enable_' . $shipping_method->id . '_' . $key ) === false ) {
							update_option( 'enable_' . $shipping_method->id . '_' . $key, 'on' );
						}
					} else {

						$default = 'off';

						if ( get_option( 'enable_' . $shipping_method->id . '_' . $key ) === false ) {
							update_option( 'enable_' . $shipping_method->id . '_' . $key, 'off' );
						}
					}

					$shipping_option = array(
						'name'     => $shipping_method->method_title,
						'desc_tip' => $shipping_method->method_description,
						'id'       => 'enable_' . $shipping_method->id . '_' . $key,
						'type'     => 'bm_ui_checkbox',
						'default'  => $default,
					);

					array_push( $options, $shipping_option );
				}

				$end = array(
					'type' => 'sectionend',
					'id'   => 'shipping_options',
				);

				array_push( $options, $end );

			}
		}

		$options = apply_filters( 'woocommerce_bm_ui_shippings', $options );

		return $options;

	}
}
