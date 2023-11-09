<?php
/**
 * Feature Name: Slack Connector - Register Custom Post Type
 * Version:      1.0
 * Author:       MarketPress
 * Author URI:   https://marketpress.com
 * Licence:      GPLv3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Register custom post type
 *
 * @wp-hook	init
 * @return	void
 */
function slack_connector_register_custom_post_type() {

	$labels = array(
		'name'               => _x( 'Slack Connectors', 'post type general name', 'slack-connector' ),
		'singular_name'      => _x( 'Slack Connector', 'post type singular name', 'slack-connector' ),
		'menu_name'          => _x( 'Slack Connector', 'admin menu', 'slack-connector' ),
		'name_admin_bar'     => _x( 'Slack Connector', 'add new on admin bar', 'slack-connector' ),
		'add_new'            => _x( 'Add New', 'slack_connector', 'slack-connector' ),
		'add_new_item'       => __( 'Add New Slack Connector', 'slack-connector' ),
		'new_item'           => __( 'New Slack Connector', 'slack-connector' ),
		'edit_item'          => __( 'Edit Slack Connector', 'slack-connector' ),
		'view_item'          => __( 'View Slack Connector', 'slack-connector' ),
		'all_items'          => __( 'Slack Connector', 'slack-connector' ),
		'search_items'       => __( 'Search Slack Connectors', 'slack-connector' ),
		'parent_item_colon'  => __( 'Parent Slack Connectors:', 'slack-connector' ),
		'not_found'          => __( 'No Slack Connectors found.', 'slack-connector' ),
		'not_found_in_trash' => __( 'No Slack Connectors found in Trash.', 'slack-connector' )
	);

	// Who can see the tools page of this plugin?
	$show_ui = apply_filters( 'slack_connector_show_ui', current_user_can( 'manage_options' ) );

	$args = array(
		'labels'             => $labels,
        'description'        => __( 'Slack Connectors for Slack.', 'slack-connector' ),
		'public'             => false,
		'show_ui'            => $show_ui,
		'show_in_menu'       => 'tools.php',
		'rewrite'            => array( 'slug' => 'slack_connector' ),
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'author' )
	);

	register_post_type( 'slack_connector', $args );
	remove_post_type_support( 'slack_connector', 'revisions' );

	if ( is_admin() ) {

		$backend_directory = MPSC_APPLICATION_DIR . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR;
		require_once( $backend_directory . 'custom-post-type-handler.php' );

		// save_post
		add_action( 'save_post', 'slack_connector_save_post' );

		// add general meta boxes
		add_action( 'add_meta_boxes', 'slack_connector_meta_bxoes' );
	}

} 
