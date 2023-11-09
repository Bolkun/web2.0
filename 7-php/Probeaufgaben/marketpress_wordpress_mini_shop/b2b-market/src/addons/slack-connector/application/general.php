<?php
/**
 * Feature Name: Slack Connector - General Functions
 * Version:      1.0
 * Author:       MarketPress
 * Author URI:   https://marketpress.com
 * Licence:      GPLv3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Most important function in this plugin
 * Send the slack notification
 * See slack documentation for $args
 *
 * @param 	string $url
 * @param 	array $args
 * @return	array | WP_Error
 */
function slack_connector_send_message( $url, $args ) {

	$url 	= apply_filters( 'slack_connector_send_message_prepare_url_before_send', $url, $args );
	$args 	= apply_filters( 'slack_connector_send_message_prepare_args_before_send', $args, $url );

	$output	= 'payload=' . json_encode( $args );

	do_action( 'slack_connector_send_message_before_send' );

	$response = wp_remote_post( $url, array(
		'body' => $output,
	) );

	do_action( 'slack_connector_send_message_after_send' );

	return $response;

}


/**
 * Adds a conditional ".min" suffix to the
 * file name when SCRIPT_DEBUG is NOT set to TRUE.
 *
 * @return	string
 */
function slack_connector_get_script_suffix() {

	$script_debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
	$suffix = $script_debug ? '' : '.min';

	return $suffix;
}

/**
 * Check if our admin page is shown
 *
 * @return boolean
 */
function slack_connector_is_admin() {
	return ( isset( $_REQUEST[ 'post_type' ] ) && $_REQUEST[ 'post_type' ] == 'slack_connector' ) || ( isset( $_REQUEST[ 'post' ] ) && get_post_type( $_REQUEST[ 'post' ] ) == 'slack_connector' ) && is_admin();
}

/**
 * Check $plugin is activated or (!) network activated
 *
 * @param 	string $plugin
 * @return 	boolean
 */
function slack_connector_is_plugin_activated( $plugin ) {

	if ( ! function_exists( 'is_plugin_activated' ) ) {
		include_once( ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'plugin.php' );
	}

	return ( is_plugin_active( $plugin ) || is_plugin_active_for_network( $plugin) );
}

/**
 * See Slack documentation
 * It's quite difficult to get the correct string format so that slack will send your message
 *
 * @param 	string $string
 * @return 	string
 */
function slack_connector_prepare_string_for_slack( $param_string ) {

	// New variable, so we can have $param_string as parameter for filter applied at the end of this function
	$string = $param_string;

	// Remove HTML Markup
	$string = strip_tags( $string );

	// How to escape characters for Slack. Not as described as in documentation, but working
	$string = html_entity_decode( $string, ENT_QUOTES );
	$string = urlencode( $string );
	$string = str_replace( '%22', '"', $string );

	return apply_filters( 'slack_connector_prepare_string_for_slack', $string, $param_string );
}

/**
 * See Slack documentation
 * Format a link
 * Do this after Calling slack_connector_prepare_string_for_slack!
 *
 * @param 	string $url
 * @return 	string $title
 */
function slack_connector_get_slack_url( $url, $title ) {
	return '<' . urlencode( str_replace( '&#038;', '&', $url ) ) . '|' . $title . '>';
}

/**
 * Send test notification, called via ajax
 *
 *
 * @wp-hook wp_ajax_slack_connector_test_notification
 * @param 	string $url
 * @return 	void
 */
function slack_connector_test_notification() {

	check_ajax_referer( 'slack-connector-ajax', 'security' );

	$slack_url = esc_url( $_POST[ 'slack_url' ] );

	if ( $slack_url == '' ) {
		echo __( 'Please enter a webhook URL', 'slack-connector' );
		exit();
	}

	$args = array();

	// Slack User Name
	$args[ 'username' ] = slack_connector_prepare_string_for_slack( get_bloginfo( 'name' ) . ': ' . __( 'Slack Connector', 'slack-connector' ) );
	$args[ 'username' ] = apply_filters( 'slack_connector_test_username',  $args[ 'username' ] );

	// Emoji
	$args[ 'icon_emoji' ] = apply_filters( 'slack_connector_test_emoji', ':smiley:' );

	// Text
	$args[ 'text' ] = apply_filters( 'slack_connector_test_text', slack_connector_prepare_string_for_slack( __( '*This is a test notification*', 'slack-connector' ) ) );

	// Attachment
	$attachment = array();
	$attachment[ 'color' ] 			= 'good';
	$attachment[ 'author_name' ] 	= __( 'MarketPress', 'slack-connector' );
	$attachment[ 'author_link' ] 	= __( 'https://marketpress.com', 'slack-connector' );
	$attachment[ 'title' ]			= __( 'Sending test notification succeeded', 'slack-connector' );
	$attachment[ 'text' ] 			= slack_connector_prepare_string_for_slack( __( 'This is a test message from Slack Connector. We hope you enjoy this plugin. \nLovely regards\n\nYour MarketPress Team!', 'slack-connector' ) );
	$attachment[ 'thumb_url' ]		= esc_url( MPSC_PLUGIN_URL . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'marketpress-logo.png' );

	// Set Attachments to args
	$attachment = apply_filters( 'slack_connector_test_attachment', $attachment );
	$args[ 'attachments' ] = array( $attachment );

	// Send the Slack Notification
	$respond = slack_connector_send_message( $slack_url, $args );

	if ( is_array( $respond ) ) {
		// Everything is okay
		echo "okay";
	} else {
		// We have an WP_Error
		echo __( 'ERROR: Something went wrong. Please, check your webhook URL.', 'slack-connector' );
	}

	exit();
}
