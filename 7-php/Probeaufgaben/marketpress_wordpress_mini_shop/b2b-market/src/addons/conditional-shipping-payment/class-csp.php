<?php

if ( ! class_exists( 'CSP' ) ) {

	class CSP {
		/**
		 * initialize hooks and requirements
		 */
		public static function init() {
			if ( self::is_active() === true ) {

				require_once( B2B_ADDON_PATH . 'conditional-shipping-payment' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'class-csp-options.php' );

				if ( BM_Helper::is_rest() !== true ) {
					require_once( B2B_ADDON_PATH . 'conditional-shipping-payment' . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'class-csp-payment-manager.php' );
					require_once( B2B_ADDON_PATH . 'conditional-shipping-payment' . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'class-csp-shipping-manager.php' );
				}

				$option_page = new CSP_Options();

				add_filter( 'woocommerce_bm_ui_left_menu_items', array( $option_page, 'add_menu_item' ) );
			}
		}

		/**
		 * @return bool
		 */
		private static function is_active() {
			$status = get_option( 'bm_addon_shipping_and_payment' );

			if ( $status == 'on' ) {
				return true;
			}

			return false;
		}


	}

	CSP::init();
}