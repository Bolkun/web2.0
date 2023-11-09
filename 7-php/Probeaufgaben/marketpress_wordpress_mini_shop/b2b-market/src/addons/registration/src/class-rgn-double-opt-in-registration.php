<?php

class RGN_Double_Opt_In_Registration {

	/**
	 * RGN_Double_Opt_In_Registration constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Init Hooks and filters
	 *
	 * @static
	 * @return void
	 */
	public function init() {

		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		// Only if Double Opt-in Customer Registration has been activated
		if ( self::double_opt_in_is_activated() ) {

			// Deactivate WooCommerce 'created customer notification' email
			add_action( 'woocommerce_email', array( __CLASS__, 'deactive_woocommerce_created_customer_notification' ) );

			// Activate BM 'created customer notification' email
			add_action( 'woocommerce_created_customer_notification', array(
				__CLASS__,
				'woocommerce_created_customer_notification'
			), 10, 3 );

			// Check activation action, if user access activation url
			add_action( 'template_redirect', array( __CLASS__, 'check_activation_action' ) );

			// No user login for users without activaiton
			add_filter( 'wp_authenticate_user', array( __CLASS__, 'wp_authenticate_user' ), 10, 2 );

			// No logged in user after order process is finished
			add_action( 'woocommerce_thankyou', array( __CLASS__, 'logout_user' ), 10, 2 );

			// No logged in user after regristration on my account page
			add_action( 'wp', array( __CLASS__, 'logout_user_my_account_page' ) );
		}

	}

	/**
	 * User is logged in after registration on my account page => logout user if activation status is not activated
	 *
	 * @since 3.5.1
	 * @static
	 * @wp-hook wp
	 * @return void
	 */
	public static function logout_user_my_account_page() {

		if ( is_user_logged_in() && is_account_page() ) {

			$user              = wp_get_current_user();
			$activation_status = get_user_meta( $user->ID, '_bm_double_opt_in_activation_status', true );

			if ( $activation_status == 'waiting' ) {

				wp_logout();
				wp_safe_redirect( wp_get_referer() . '?bm_double_opt_in_message=true' );
				exit();

			}

		} else if ( isset( $_REQUEST['bm_double_opt_in_message'] ) ) {

			$text = __( 'Please activate your account through clicking on the activation link received via email.', 'b2b-market' );
			wc_add_notice( $text, 'success' );

		}

	}

	/**
	 * User is logged in after registration => logout user if activation status is not activated
	 *
	 * @static
	 * @wp-hook woocommerce_thankyou
	 * @return void
	 */
	public static function logout_user() {

		if ( is_user_logged_in() ) {

			$user              = wp_get_current_user();
			$activation_status = get_user_meta( $user->ID, '_bm_double_opt_in_activation_status', true );

			if ( $activation_status == 'waiting' ) {

				wp_logout();

			}

		}

	}

	/**
	 * Check if Douple Opt-in Customer Registration is activated
	 *
	 * @static
	 * @return Bool
	 */
	private static function double_opt_in_is_activated() {
		return ( get_option( 'bm_double_opt_in_customer_registration' ) == 'on' );
	}

	/**
	 * Deactivate WooCommerce 'created customer notification' email
	 *
	 * @param $object
	 *
	 * @static
	 * @hook woocommerce_email
	 * @return void
	 */
	public static function deactive_woocommerce_created_customer_notification( $object ) {
		remove_action( 'woocommerce_created_customer_notification', array( $object, 'customer_new_account' ), 10, 3 );
	}

	/**
	 * Activate BM 'created customer notification' email
	 *
	 * @static
	 * @hook woocommerce_created_customer_notification
	 * @return void
	 */
	public static function woocommerce_created_customer_notification( $customer_id, $new_customer_data = array(), $password_generated = false ) {

		if ( ! $customer_id ) {
			return;
		}

		$user_email = $new_customer_data['user_email'];
		$user_pass  = ! empty( $new_customer_data['user_pass'] ) ? $new_customer_data['user_pass'] : '';

		// add user meta
		$activation_code = wp_create_nonce( '_bm_double_opt_in_activation' ) . md5( rand( 1, 100000 ) );
		add_user_meta( $customer_id, '_bm_double_opt_in_activation_status', 'waiting' );
		add_user_meta( $customer_id, '_bm_double_opt_in_activation', $activation_code );

		// build activation link
		$activation_link = untrailingslashit( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ) . '?account-activation=' . $activation_code;
		$activation_link = apply_filters( 'bm_double_opt_in_activation_link', $activation_link, $customer_id );

		$mail = include( 'class-rgn-double-opt-in-email.php' );
		$mail->trigger( $customer_id, $activation_link, $user_email, $new_customer_data['user_login'] );

	}

	/**
	 * What happens if an user wants to activate the new user account
	 *
	 * @static
	 * @hook woocommerce_created_customer_notification
	 * @return void
	 */
	public static function check_activation_action() {

		if ( is_account_page() && isset( $_GET['account-activation'] ) ) {

			// get activation code
			$activation_code = $_GET['account-activation'];

			// get the user

			/* removed user role from get_users */
			$users = get_users(
				array(
					'meta_key'   => '_bm_double_opt_in_activation',
					'meta_value' => $activation_code
				)
			);

			if ( ! empty( $users ) ) {

				// get user
				$user = array_shift( $users );

				// get status
				$status = get_user_meta( $user->ID, '_bm_double_opt_in_activation_status', true );

				if ( $status == 'waiting' ) {

					// activate the account
					update_user_meta( $user->ID, '_bm_double_opt_in_activation_status', 'activated' );
					wc_add_notice( __( 'Your account has been successfully activated.', 'b2b-market' ), 'success' );

					// send WC-mail
					WC()->mailer()->customer_new_account( $user->ID );

					// login user
					if ( ! is_user_logged_in() ) {
						wc_set_customer_auth_cookie( $user->ID );
					}

					do_action( 'bm_double_opt_in_activation_user_activated' );

				} else if ( $status == 'activated' ) {

					// account has already been activated
					wc_add_notice( __( 'Your account has already been activated.', 'b2b-market' ), 'notice' );

				} else {

					// sth strange happend
					do_action( 'bm_double_opt_in_activation_user_activated_status_' . $status );

				}

			} else {

				// Something went wrong
				wc_add_notice( __( 'Your account cannot be activated. The activation code cannot be found.', 'b2b-market' ), 'error' );
			}

		} else if ( is_account_page() && isset( $_GET['resend-account-activation'] ) && isset( $_GET['user'] ) ) {

			// send new activation link
			if ( wp_verify_nonce( $_GET['resend-account-activation'], '_bm_double_opt_in_activation_again' . md5( $_GET['user'] ) ) ) {

				$activation_code = wp_create_nonce( '_bm_double_opt_in_activation' ) . md5( rand( 1, 100000 ) );
				add_user_meta( $_GET['user'], '_bm_double_opt_in_activation_status', 'waiting' );
				add_user_meta( $_GET['user'], '_bm_double_opt_in_activation', $activation_code );

				// build activation link
				$activation_link = untrailingslashit( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ) . '?account-activation=' . $activation_code;
				$activation_link = apply_filters( 'bm_double_opt_in_activation_link', $activation_link, $_GET['user'] );

				$user_data = get_userdata( $_GET['user'] );

				$mail = include( 'RGN_Double_Opt_In_Email.php' );
				$mail->trigger( $_GET['user'], $activation_link, $user_data->user_email, $user_data->user_login );

				wc_add_notice( __( 'A new activation link has been sent to your e-mail.', 'b2b-market' ), 'success' );

			}

		}

	}

	/**
	 * No user login for users without activation
	 *
	 * @static
	 * @hook wp_authenticate_user
	 *
	 * @param WP_User $user
	 * @param String $password
	 *
	 * @return WP_User (or throws an error)
	 */
	public static function wp_authenticate_user( $user, $password ) {

		$activation_status = get_user_meta( $user->ID, '_bm_double_opt_in_activation_status', true );

		$nonce       = wp_create_nonce( '_bm_double_opt_in_activation_again' . md5( $user->ID ) );
		$resend_link = untrailingslashit( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ) . '?resend-account-activation=' . $nonce . '&user=' . $user->ID;

		if ( $activation_status == 'waiting' ) {
			return new WP_Error( 'bm_user_login_without_activation', sprintf( __( 'Please activate your account through clicking on the activation link received via email. Click <a href="%s">here</a> if you need a new activation link.', 'b2b-market' ), $resend_link ) );
		}

		return $user;
	}

}
