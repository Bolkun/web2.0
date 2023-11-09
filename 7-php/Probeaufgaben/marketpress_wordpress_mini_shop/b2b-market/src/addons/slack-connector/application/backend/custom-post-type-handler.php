<?php
/**
 * Feature Name: Slack Connector - Custom Post Type Handler
 * Version:      1.0
 * Author:       MarketPress
 * Author URI:   https://marketpress.com
 * Licence:      GPLv3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Save Meta Data for our CPT
 *
 * @wp-hook	save_post
 * @param 	Integer $post_id
 * @return	void
 */
function slack_connector_save_post( $post_ID ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( is_int( wp_is_post_revision( $post_ID ) ) ) return;
	if ( is_int( wp_is_post_autosave( $post_ID ) ) ) return;
	if ( get_post_type( $post_ID ) != 'slack_connector' ) return;
	if ( ! isset( $_REQUEST[ 'slack_connector_save_the_post' ] ) ) return;
	if ( ! wp_verify_nonce( $_REQUEST[ 'slack_connector_save_the_post' ], 'slack_connector_save_post' ) ) return;

	if ( ! defined( 'DOING_AJAX' ) ) {

		foreach ( $_REQUEST as $key => $value ) {

			// save only our post meta
			if ( str_replace( '_slack_connector', '', $key ) != $key ) {

				if ( ! is_array( $value ) ) {
					$value = esc_attr( $value );
				}

				update_post_meta( $post_ID, $key, $value );
			}

		}

		do_action( 'slack_connector_save_post', $post_ID );

	}

}

/**
 * Add General Meta Boxes to our CPT
 *
 * @wp-hook	save_post
 * @param 	Integer $post_id
 * @return	void
 */
function slack_connector_meta_bxoes() {

	add_meta_box (	'slack_connector_webhook_url',
					__( 'Webhook URL', 'slack-connector' ),
					'slack_connector_meta_box_webhook_url',
					'slack_connector'
	);

	do_action ( 'slack_connector_meta_boxes' );

}

/**
 * Webhook URL Metabox
 *
 * @add_meta_box
 * @param 	WP_Post $post
 * @param 	Array $metabox
 * @return	void
 */
function slack_connector_meta_box_webhook_url( $post, $metabox ) {

	wp_nonce_field( 'slack_connector_save_post', 'slack_connector_save_the_post' );

	$url = esc_url( get_post_meta( $post->ID, '_slack_connector_webhook_url', true ) );

	?>

	<label for="_slack_connector_webhook_url" class="slack-connector-label">
		<?php echo __( 'Webhook URL', 'slack-connector' ); ?>:
	</label>

	<input type="text" class="slack-connector-input slack-connector-input-text" name="_slack_connector_webhook_url" id="_slack_connector_webhook_url" value="<?php echo $url; ?>"/>

	<span class="slack-connector-description">
		<?php echo __( 'Firstly, go to <a target="_blank" href="https://slack.com/services/new/incoming-webhook">https://slack.com/services/new/incoming-webhook</a> and create a new webhook. Next, set a channel to receive notifcations, copy the URL for the webhook and insert this URL in the field above.', 'slack-connector' ); ?>
	</span>

	<button type="button" class="slack-connector-send-test-message button-primary" id="slack-connector-send-test-message">
		<?php echo __( 'Send a test notification', 'slack-connector' ); ?>
	</button>

	<span class="slack-connector-send-test-message-success" id="slack-connector-send-test-message-success">
		<?php echo __( 'Test notification has been sent.', 'slack-connector' ); ?>
	</span>

	<span class="slack-connector-send-test-message-error" id="slack-connector-send-test-message-error"></span>

	<?php

}

/**
 * Makes the general input fields for the meta box of a module.
 * Every module should use it.
 * Includes "Slack Username", "Emoji"
 *
 * @param 	String $module_slug
 * @return	void
 */
function slack_connector_general_input_fiels_for_meta_boxes_of_modules( $module_slug, $module_name, $post ) {

	?>

	<span class="slack-connector-section-title"><?php echo __( 'General Settings', 'slack-connector' ); ?></span>

	<label for="_slack_connector_<?php echo $module_slug; ?>_slack_username" class="slack-connector-label slack-connector-label-one-row">
		<?php echo __( 'Slack Username',  'slack-connector' ); ?>:
	</label>

	<?php
		$slack_username = get_post_meta( $post->ID, '_slack_connector_' . $module_slug . '_slack_username', true );
		if ( $slack_username == '' ) {
			$slack_username = get_bloginfo( 'name' );
		}
	?>

	<input type="text" class="slack-connector-input slack-connector-input-text slack-connector-input-text-one-row" name="_slack_connector_<?php echo $module_slug; ?>_slack_username" id="_slack_connector_<?php echo $module_slug; ?>_slack_username" value="<?php echo $slack_username; ?>"/>

	<span class="slack-connector-description slack-connector-description-one-row">
		<?php echo __( 'Choose a username for your notifications. You can\'t leave this field empty.', 'slack-connector' ); ?>
	</span>

	<br class="slack-connector-clear"/>

	<label for="_slack_connector_<?php echo $module_slug; ?>_slack_emoji" class="slack-connector-label slack-connector-label-one-row">
		<?php echo __( 'Emoji',  'slack-connector' ); ?>:
	</label>

	<?php $slack_emoji = get_post_meta( $post->ID, '_slack_connector_' . $module_slug . '_slack_emoji', true ); ?>

	<input type="text" class="slack-connector-input slack-connector-input-text slack-connector-input-text-one-row" name="_slack_connector_<?php echo $module_slug; ?>_slack_emoji" id="_slack_connector_<?php echo $module_slug; ?>_slack_emoji" placeholder="<?php echo __( 'Emoji Code, example: :mailbox_with_mail:', 'slack-connector' ); ?>" value="<?php echo $slack_emoji; ?>"/>

	<span class="slack-connector-description slack-connector-description-one-row">
		<?php echo sprintf( __( 'Choose an emoji that will be included in every %s notification. You can find all emoji codes in the <a href="http://www.emoji-cheat-sheet.com/" target="_blank">Emoji Cheat Sheet</a>.', 'slack-connector' ), $module_name ); ?>
	</span>

	<br class="slack-connector-clear"/>

	<?php do_action( 'slack_connector_after_general_meta_boxes' ); ?>

	<hr />

	<?php

}
