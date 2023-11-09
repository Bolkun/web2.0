<?php

class CSP_ShippingManager {

	/**
	 * @var array
	 */
	private $group;

	/**
	 * CSP_ShippingManager constructor.
	 */
	public function __construct() {
		$this->group = BM_Conditionals::get_validated_customer_group();

		add_filter( 'woocommerce_package_rates', array( $this, 'disable_shipping_method_for_group' ), 10, 2 );
	}

	/**
	 * @param $rates
	 * @param $package
	 *
	 * @return mixed
	 */
	public function disable_shipping_method_for_group( $rates, $package ) {

		if ( ! is_null( $this->group ) ) {

			$group_object = get_post( $this->group );
			$slug         = $group_object->post_name;

			if ( isset( $rates ) && ! empty( $rates ) ) {
				foreach ( $rates as $rate ) {

					$status = get_option( 'enable_' . $rate->method_id . '_' . $slug );

					if ( $status != 'on' ) {
						unset( $rates[ $rate->id ] );

					}
				}
			}
		}
		return $rates;
	}

	public static function update_shipping_options_for_group() {

		$groups = new BM_User();

		foreach ( $groups->get_all_customer_groups() as $group ) {

			foreach ( $group as $key => $value ) {

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
				}
			}
		}
	}

}

new CSP_ShippingManager();
