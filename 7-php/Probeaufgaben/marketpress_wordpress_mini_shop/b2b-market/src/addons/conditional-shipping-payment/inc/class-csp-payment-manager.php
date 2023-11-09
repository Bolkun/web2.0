<?php

class CSP_PaymentManager {

	/**
	 * @var array
	 */
	public $group;

	/**
	 * CSP_PaymentManager constructor.
	 */
	public function __construct() {
		$this->group = BM_Conditionals::get_validated_customer_group();

		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'disable_payment_option_for_group' ) );
	}


	/**
	 * @param $available_gateways
	 *
	 * @return mixed
	 */
	public function disable_payment_option_for_group( $available_gateways ) {

		if ( ! is_null( $this->group ) ) {

			$group_object = get_post( $this->group );
			$slug         = $group_object->post_name;

			if ( isset( $available_gateways ) && ! empty( $available_gateways ) ) {
				foreach ( $available_gateways as $gateway ) {

					$status = get_option( 'enable_' . $gateway->id . '_' . $slug );

					if ( $status != 'on' ) {

						unset( $available_gateways[ $gateway->id ] );
					}
				}
			}
		}

		return $available_gateways;
	}

	public static function update_payment_options_for_group() {

		$gateways = new WC_Payment_Gateways();
		$options  = array();
		$groups   = new BM_User();

		foreach ( $groups->get_all_customer_groups() as $group ) {

			foreach ( $group as $key => $value ) {

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
				}
			}
		}
	}


}

new CSP_PaymentManager();
