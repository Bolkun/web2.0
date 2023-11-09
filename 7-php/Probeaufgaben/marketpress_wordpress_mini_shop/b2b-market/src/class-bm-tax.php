<?php
/**
 * Class which handles the tax status
 */
class BM_Tax {
	/**
	 * Shop tax setting
	 *
	 * @var string
	 */
	public $shop_tax;

	/**
	 * Cart tax setting
	 *
	 * @var string
	 */
	public $cart_tax;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->shop_tax = get_option( 'woocommerce_tax_display_shop' );
		$this->cart_tax = get_option( 'woocommerce_tax_display_cart' );
	}

	/**
	 * Filter the tax status based on user group settings
	 *
	 * @param string $value
	 * @return void
	 */
	public function filter_tax_display( $value ) {

		$current_group = BM_Conditionals::get_validated_customer_group();

		if ( ! is_null( $current_group ) && ! is_admin() ) {

			/* get group data */
			$tax_type = get_post_meta( $current_group, 'bm_tax_type', true );

			if ( 'incl' === $this->shop_tax || 'incl' === $this->cart_tax ) {

				if ( 'on' == $tax_type ) {
					$value = 'excl';
				}
			}
		}
		return $value;
	}
	/**
	 * Add a hash to the current user session to apply tax settings for variations
	 *
	 * @param string $hash
	 * @return void
	 */
	public function tax_display_add_hash_user_id( $hash ) {
		$hash[] = get_current_user_id();
		return $hash;
	}

}
