<?php

if ( ! class_exists( 'RGN' ) ) {

	class RGN {

		/**
		 * initialize addon
		 */
		public static function init() {
			if ( self::is_active() === true ) {

				$wgm_optin_active = get_option( 'wgm_double_opt_in_customer_registration' );
				$b2b_optin_active = get_option( 'bm_double_opt_in_customer_registration' );

				require_once( B2B_ADDON_PATH . 'registration' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'class-rgn-options.php' );
				require_once( B2B_ADDON_PATH . 'registration' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'class-rgn-helper.php' );
				require_once( B2B_ADDON_PATH . 'registration' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'class-rgn-user-meta.php' );
				require_once( B2B_ADDON_PATH . 'registration' . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'class-rgn-vat-validator.php' );
				require_once( B2B_ADDON_PATH . 'registration' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'class-rgn-registration.php' );

				$option_page = new RGN_Options();

				if ( 'off' === $wgm_optin_active || false === $wgm_optin_active && 'on' === $b2b_optin_active ) {
					require_once( B2B_ADDON_PATH . 'registration' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'class-rgn-double-opt-in-registration.php' );
					new RGN_Double_Opt_In_Registration();
				}

				add_filter( 'woocommerce_bm_ui_left_menu_items', array( $option_page, 'add_menu_item' ) );
				add_action( 'wp_enqueue_scripts', array( __CLASS__, 'registration_scripts' ) );
			}
		}


		/**
		 * script enqueues
		 */
		public static function registration_scripts() {

			$selectable_groups = RGN_Helper::get_net_tax_groups();
			$is_modal          = apply_filters( 'bm_rgn_is_modal', false );

			if ( is_account_page() || is_checkout() || true === $is_modal ) {
				wp_enqueue_style( 'frontend-registration-css', untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/assets/frontend.css', '1.0.3', 'all' );
				wp_enqueue_script( 'frontend-registration-js', untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/assets/frontend.js', array( 'jquery' ), '1.0.3', false );

				if ( isset( $selectable_groups ) && ! empty( $selectable_groups ) ) {
					wp_localize_script( 'frontend-registration-js', 'registration', array( 'net_tax_groups' => $selectable_groups ) );
				}
			}
		}


		/**
		 * @return bool
		 */
		private static function is_active() {
			$status = get_option( 'bm_addon_registration' );

			if ( 'on' == $status ) {
				return true;
			}

			return false;

		}
	}


	RGN::init();
}
