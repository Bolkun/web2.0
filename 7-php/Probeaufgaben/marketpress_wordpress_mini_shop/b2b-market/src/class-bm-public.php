<?php

/**
 * Class which handles the frontend pricing display
 */
class BM_Public {

	/**
	 * BM_Public constructor.
	 */
	public function __construct() {

		$message_option = get_option( 'bm_global_discount_message' );
		$global_active  = get_option( 'bm_global_activate_prices' );

		add_action( 'wp_enqueue_scripts', array( $this, 'add_frontend_assets' ) );

		if ( ! empty( $message_option ) ) {
			add_action( 'wp_head', array( $this, 'global_discount_message' ) );
		}
	}


	/**
	 * Render global discount message box
	 */
	public function global_discount_message() {

		$message_option     = get_option( 'bm_global_discount_message' );
		$message_bg_color   = get_option( 'bm_global_discount_message_background_color' );
		$message_font_color = get_option( 'bm_global_discount_message_font_color' );

		if ( is_admin() || empty( $message_option ) ) {
			return;
		}

		$message  = '<div class="b2b-discount-banner" style="background-color:' . $message_bg_color . ';" data-id="' . esc_attr( md5( $message_option ) ) . '">';
		$message .= '  <p style="color:' . $message_font_color . ';">' . esc_html( $message_option ) . '</p>';
		$message .= '  <button aria-label="' . __( 'Dismiss site notice', 'b2b-market' ) . '" class="b2b-discount-banner-dismiss">×</button>';
		$message .= '</div>';

		echo $message;

	}

	/**
	 * Handler for enqueue frontend scripts
	 */
	public function add_frontend_assets() {

		$message_option = get_option( 'bm_global_discount_message' );
		$live_calculation = get_option( 'enable_total_price_calculation' );

		$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : 'min.';

		if ( ! is_admin() && ! empty( $message_option ) || '' !== $message_option ) {
			wp_enqueue_script( 'bm-discount-banner', B2B_PLUGIN_URL . '/assets/public/bm-discount-banner.' . $min . 'js', array( 'jquery' ), '1.0.2', true );
		}
	}
}

new BM_Public();
