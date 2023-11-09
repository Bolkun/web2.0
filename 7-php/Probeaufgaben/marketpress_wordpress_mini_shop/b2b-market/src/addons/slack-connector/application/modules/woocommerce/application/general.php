<?php
/**
 * Feature Name:  Slack Connector - WooCommerce General Functions
 * Version:       1.0
 * Author:        MarketPress
 * Author URI:    https://marketpress.com
 * Licence:       GPLv3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Save Meta Data for our CPT - WooCommerce
 *
 * @wp-hook	slack_connector_save_post
 * @param 	Integer $post_id
 * @return	void
 */
function slack_connector_save_post_woocommerce( $post_ID ) {

	// Save Multiple Select
	if ( ! isset( $_REQUEST[ '_slack_connector_woocommerce_products' ] ) ) {
		update_post_meta( $post_ID, '_slack_connector_woocommerce_products', array() );
	}

	// Save Multiple Select
	if ( ! isset( $_REQUEST[ '_slack_connector_woocommerce_product_categories' ] ) ) {
		update_post_meta( $post_ID, '_slack_connector_woocommerce_product_categories', array() );
	}
	
}

/**
 * New Order: Detect if we have to send a notifcation, check all cases, distribute to other functions
 *
 * @wp-hook	woocommerce_checkout_order_processed
 * @param 	Integer $order_id
 * @param 	Array $posted
 * @return	void
 */
function slack_connector_woocommerce_checkout_order_processed( $order_id, $posted ) {

	// Get all Slack Channels
	$channels_args = apply_filters( 'slack_connector_woocommerce_get_channels_args', array(
		'posts_per_page'	=> -1,
		'post_type'			=> 'slack_connector'
		) 
	);

	$channels = get_posts( $channels_args );

	foreach ( $channels as $channel ) {

		$slack_url = esc_url( get_post_meta( $channel->ID, '_slack_connector_webhook_url', true ) );
			
		// Don't send if url is not set
		if ( $slack_url == '' ) {
			continue;
		}

		$notification_option = get_post_meta( $channel->ID, '_slack_connector_woocommerce_new_order_product_sale_notifications', true );

		if ( $notification_option == 'new_order' ) {

			$order = wc_get_order( $order_id );
			$items = $order->get_items();
			slack_connector_woocommerce_new_order_product_sale( $channel, $order, $items );

		} else if ( $notification_option == 'product_sold' || $notification_option == 'product_category_sold' ) {

			$order = wc_get_order( $order_id );
			$items = $order->get_items();

			foreach ( $items as $item_key => $item ) {

				if ( $notification_option == 'product_sold' ) {
					slack_connector_woocommerce_checkout_order_processed_new_order_product_sale( $channel, $item, $order );
				} else if ( $notification_option == 'product_category_sold' ) {
					slack_connector_woocommerce_checkout_order_processed_new_order_product_category_sale( $channel, $item, $order );
				}

			}

		} else {

			do_action( 'slack_connector_woocommerce_checkout_order_processed_' . $notification_option, $channel, $order_id );

		}

	}

}

/**
 * Send Notification New Order / Product Sale
 *
 * @param 	WP_Post $channel
 * @param 	WC_Order $order
 * @param 	Array $items
 * @return	void
 */
function slack_connector_woocommerce_new_order_product_sale( $channel, $order, $items ) {

	// Load options and defaults if necessary
	$args = array();
	$notification_option = get_post_meta( $channel->ID, '_slack_connector_woocommerce_new_order_product_sale_notifications', true );

	// Slack User Name
	$args[ 'username' ] = ( get_post_meta( $channel->ID, '_slack_connector_woocommerce_slack_username', true ) ) == '' ? get_bloginfo( 'name' ) : get_post_meta( $channel->ID, '_slack_connector_woocommerce_slack_username', true );
	$args[ 'username' ] = slack_connector_prepare_string_for_slack( $args[ 'username' ] );
	$args[ 'username' ] = apply_filters( 'slack_connector_woocommerce_username', $args[ 'username' ], $channel, $order, $items );

	// Emoji
	$emoji = get_post_meta( $channel->ID, '_slack_connector_woocommerce_slack_emoji', true );
	if ( $emoji != '' ) {
		$args[ 'icon_emoji' ] = slack_connector_prepare_string_for_slack( $emoji );
		$args[ 'icon_emoji' ] = apply_filters( 'slack_connector_bbpress_emoji', $args[ 'icon_emoji' ], $channel, $order, $items );
	}

	// Text
	$args[ 'text' ] = get_post_meta( $channel->ID, '_slack_connector_woocommerce_new_order_product_sale_title', true );
	
	// Replace placeholders, do a little trick to handle url replacement and formatting
	$search = array( '[order-number]', '[order-link]', '[first-name]', '[last-name]' );
	$replace = array( '#' . $order->get_order_number(), '__MP_SC_ORDER_LINK__MP_SC_', $order->billing_first_name, $order->billing_last_name );
	
	if ( $notification_option == 'new_order' ) {
		$search[] = '[order-total]';
		$search[] = '[order-items]';
		$replace[] = $order->get_formatted_order_total();
		$replace[] = slack_connector_woocommerce_list_items_light_version( $order->get_items(), $channel, $order );
	} else {
		$search[] = '[product-name]';
		$replace[] = slack_connector_woocommerce_list_items_light_version( $items, $channel, $order ); // items consists only of one item
	}

	$args[ 'text' ] = str_replace( $search, $replace, $args[ 'text' ] );

	$args[ 'text' ] = slack_connector_prepare_string_for_slack( $args[ 'text' ] );
	$args[ 'text' ] = str_replace( '__MP_SC_ORDER_LINK__MP_SC_', slack_connector_get_slack_url( esc_url( admin_url( 'post.php?post=' . $order->id . '&action=edit' ) ), __( 'View Order', 'slack-connector' ) ), $args[ 'text' ] );

	// Attachment
	$attachment = array();
	
	// Color
	$attachment[ 'color' ] = get_post_meta( $channel->ID, '_slack_connector_woocommerce_color', true );

	// Attachment fields
	$fields = array();
	$show_price = get_post_meta( $channel->ID, '_slack_connector_woocommerce_new_order_product_sale_field_items', true ) == 'activated';
	
	// All core backend fields with title, value and short attribute. See Slack documentation.
	$possible_fields = array ( 
		
		'order'			=> array(
								'title'	=> slack_connector_prepare_string_for_slack( __( 'Order', 'slack-connector' ) ),
								'value'	=> slack_connector_get_slack_url( esc_url( admin_url( 'post.php?post=' . $order->id . '&action=edit' ) ), '#' . $order->get_order_number() ),
								'short'	=> true
							),

		'customer'		=> array(
								'title'	=> slack_connector_prepare_string_for_slack( __( 'Customer', 'slack-connector' ) ),
								'value'	=> slack_connector_prepare_string_for_slack( $order->billing_first_name . ' ' . $order->billing_last_name ),
								'short'	=> true

							),
		
		'order_total'	=> array(
								'title'	=> slack_connector_prepare_string_for_slack( __( 'Order Total', 'slack-connector' ) ),
								'value'	=> slack_connector_prepare_string_for_slack( $order->get_formatted_order_total() ),
								'short'	=> true
							),
		
		'items' 		=> array(
								'title'	=> count( $items ) > 1 ? slack_connector_prepare_string_for_slack( __( 'Order Items', 'slack-connector' ) ) : slack_connector_prepare_string_for_slack( __( 'Order Item', 'slack-connector' ) ),
								'value'	=> slack_connector_woocommerce_list_items( $items, $channel, $order, $show_price ),
								'short'	=> false
							)
	);

	$possible_fields = apply_filters( 'slack_connector_woocommerce_attachment_fields', $possible_fields, $channel, $order );
	
	foreach ( $possible_fields as $possible_field_key => $possible_field ) {

		$option = get_post_meta( $channel->ID, '_slack_connector_woocommerce_new_order_product_sale_field_' . $possible_field_key, true );
		if ( $option != 'deactivated' ) {
			$fields[] = $possible_field;
		}

	}

	// Add attachment only if $fields is not empty
	if ( ! empty( $fields ) ) {
		$attachment[ 'fields' ] = $fields;
	}
	
	// Set Attachments to args
	$attachment = apply_filters( 'slack_connector_woocommerce_attachment', $attachment, $channel, $order );
	$args[ 'attachments' ] = array( $attachment );

	// Slack url (already checked if set)
	$slack_url = esc_url( get_post_meta( $channel->ID, '_slack_connector_webhook_url', true ) );

	// Send the Slack Notification
	slack_connector_send_message( $slack_url, $args );

}

/**
 * Check if we have to send a notification because the order item is one of the chosen products
 *
 * @param 	WP_Post $channel
 * @param 	Array $item
 * @param 	WC_Order $order
 * @return	void
 */
function slack_connector_woocommerce_checkout_order_processed_new_order_product_sale( $channel, $item, $order ) {
	
	$products_option = get_post_meta( $channel->ID, '_slack_connector_woocommerce_products', true );
	$item_product_id = $item[ 'product_id' ];

	if ( in_array( $item_product_id, $products_option ) ) {
		$items = array ( $item );
		slack_connector_woocommerce_new_order_product_sale( $channel, $order, $items );
	}

}

/**
 * Check if we have to send a notification because the order item is one of the chosen product categories
 *
 * @param 	WP_Post $channel
 * @param 	Array $item
 * @param 	WC_Order $order
 * @return	void
 */
function slack_connector_woocommerce_checkout_order_processed_new_order_product_category_sale( $channel, $item, $order ) {
	
	$product_categories_of_item = wp_get_post_terms( $item[ 'product_id' ], 'product_cat' );
	$product_categories_option = get_post_meta( $channel->ID, '_slack_connector_woocommerce_product_categories', true );
	$send_notification = false;

	if ( $product_categories_of_item && ! is_wp_error ( $product_categories_of_item ) ) {

		foreach ( $product_categories_of_item as $product_category_of_item ) {

			if ( in_array( $product_category_of_item->term_id, $product_categories_option ) ) {
				$send_notification = true;
				break;
			}

		}

	}

	if ( $send_notification ) {
		$items = array ( $item );
		slack_connector_woocommerce_new_order_product_sale( $channel, $order, $items );
	}

}

/**
 * List all order items with url and optional price
 *
 * @param 	WC_Order $order
 * @return	String
 */
function slack_connector_woocommerce_list_items( $items, $channel, $order, $price = false ) {
	
	$item_names = array();
	
	foreach ( $items as $item ) {
		
		$price_string = '';
		if ( $price) {
			$price_string = wc_price( $order->get_line_total( $item, true ) );
			$price_string = apply_filters( 'slack_connector_woocommerce_list_item_price', ' - ' . $price_string, $order, $item, $channel );
		}
		
		$product_name	= slack_connector_prepare_string_for_slack( $item[ 'name' ] );
		$attributes 	= slack_connector_woocommerce_list_items_get_attributs( $item, $order );
		$product_url	= get_permalink( $item[ 'product_id' ] );
		$product_link 	= slack_connector_get_slack_url( $product_url, $product_name );
		$proudct_amount_and_price = $attributes . ' &times; ' . $item[ 'qty' ] . $price_string;
		
		// You can filter whether to show a url to the product
		if ( apply_filters( 'slack_connector_woocommerce_list_items_show_url', true ) ) {
			$item_name = $product_link . slack_connector_prepare_string_for_slack( $proudct_amount_and_price );
		} else {
			$item_name = slack_connector_prepare_string_for_slack( $product_name . ' ' . $proudct_amount_and_price );
		}

		$item_names[]	= apply_filters( 'slack_connector_woocommerce_list_item', $item_name, $order, $item, $channel );
	}

	return apply_filters( 'slack_connector_woocommerce_list_items', implode( slack_connector_prepare_string_for_slack( '\n' ), $item_names ), $items, $channel );
}

/**
 * List all order items without url and without price
 *
 * @param 	WC_Order $order
 * @return	String
 */
function slack_connector_woocommerce_list_items_light_version( $items, $channel, $order) {
	
	$item_names = array();
	
	foreach ( $items as $item ) {
		$attributes 	= slack_connector_woocommerce_list_items_get_attributs( $item, $order );
		$item_name 		= $item[ 'name' ] . $attributes . ' &times; ' . $item[ 'qty' ];
		$item_names[] 	= apply_filters( 'slack_connector_woocommerce_list_item_light_version', $item_name, $order, $item, $channel );
	}

	return apply_filters( 'slack_connector_woocommerce_list_items', implode( ', ', $item_names ), $items, $channel );
}

/**
 * Get Attributes for variations
 *
 * @param 	Array $item
 * @param 	WC_Order $order
 * @return	String
 */
function slack_connector_woocommerce_list_items_get_attributs( $item, $order ) {
	
	$_product 	= apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
	$item_meta = new WC_Order_Item_Meta( $item, $_product );
	$attributes = $item_meta->display( true, true, '_', ', ' );
	if ( $attributes != '' ) {
		$attributes = ' (' . $attributes . ')';
	}
	
	return apply_filters( 'slack_connector_woocommerce_list_items_get_attributs', $attributes, $item, $order );

}

/**
 * Low Stock Notification
 *
 * @wp-hook	woocommerce_low_stock
 * @param 	WC_Product $product
 * @return	void
 */
function slack_connector_woocommerce_low_stock( $product ) {

	// Get all Slack Channels
	$channels_args = apply_filters( 'slack_connector_woocommerce_get_channels_args', array(
		'posts_per_page'	=> -1,
		'post_type'			=> 'slack_connector'
		) 
	);

	$channels = get_posts( $channels_args );

	foreach ( $channels as $channel ) {

		$slack_url = esc_url( get_post_meta( $channel->ID, '_slack_connector_webhook_url', true ) );
			
		// Don't send if url is not set
		if ( $slack_url == '' ) {
			continue;
		}

		$notification_option = get_post_meta( $channel->ID, '_slack_connector_woocommerce_low_stock_notification', true );

		if ( $notification_option != 'activated' ) {
			continue;
		}

		$args = array();

		// Slack User Name
		$args[ 'username' ] = ( get_post_meta( $channel->ID, '_slack_connector_woocommerce_slack_username', true ) ) == '' ? get_bloginfo( 'name' ) : get_post_meta( $channel->ID, '_slack_connector_woocommerce_slack_username', true );
		$args[ 'username' ] = slack_connector_prepare_string_for_slack( $args[ 'username' ] );
		$args[ 'username' ] = apply_filters( 'slack_connector_woocommerce_username', $args[ 'username' ], $channel, $product );

		// Emoji
		$emoji = get_post_meta( $channel->ID, '_slack_connector_woocommerce_slack_emoji', true );
		if ( $emoji != '' ) {
			$args[ 'icon_emoji' ] = slack_connector_prepare_string_for_slack( $emoji );
			$args[ 'icon_emoji' ] = apply_filters( 'slack_connector_woocommerce_emoji', $args[ 'icon_emoji' ], $channel, $product );
		}

		// Attachment
		$attachment = array();
		
		// Color
		$attachment[ 'color' ] = get_post_meta( $channel->ID, '_slack_connector_woocommerce_low_stock_color', true );

		// Title
		$title = get_post_meta( $channel->ID, '_slack_connector_woocommerce_low_stock_title', true );
		$search = array( '[product-name]', '[product-id]' );
		$replace = array( $product->get_title(), $product->get_id() );
		$title = str_replace( $search, $replace, $title );
		$attachment[ 'title' ] = slack_connector_prepare_string_for_slack( $title );

		// Title Link
		$attachment[ 'title_link' ] = $product->get_permalink();

		// Set Attachments to args
		$attachment = apply_filters( 'slack_connector_woocommerce_low_stock_attachment', $attachment, $channel, $product );
		$args[ 'attachments' ] = array( $attachment );

		// Send the Slack Notification
		slack_connector_send_message( $slack_url, $args );

	}

}

/**
 * Out of Stock Notification
 *
 * @wp-hook	woocommerce_no_stock
 * @param 	WC_Product $product
 * @return	void
 */
function slack_connector_woocommerce_no_stock( $product ) {

	// Get all Slack Channels
	$channels_args = apply_filters( 'slack_connector_woocommerce_get_channels_args', array(
		'posts_per_page'	=> -1,
		'post_type'			=> 'slack_connector'
		) 
	);

	$channels = get_posts( $channels_args );

	foreach ( $channels as $channel ) {

		$slack_url = esc_url( get_post_meta( $channel->ID, '_slack_connector_webhook_url', true ) );
			
		// Don't send if url is not set
		if ( $slack_url == '' ) {
			continue;
		}

		$notification_option = get_post_meta( $channel->ID, '_slack_connector_woocommerce_out_of_stock_notification', true );

		if ( $notification_option != 'activated' ) {
			continue;
		}

		$args = array();

		// Slack User Name
		$args[ 'username' ] = ( get_post_meta( $channel->ID, '_slack_connector_woocommerce_slack_username', true ) ) == '' ? get_bloginfo( 'name' ) : get_post_meta( $channel->ID, '_slack_connector_woocommerce_slack_username', true );
		$args[ 'username' ] = slack_connector_prepare_string_for_slack( $args[ 'username' ] );
		$args[ 'username' ] = apply_filters( 'slack_connector_woocommerce_username', $args[ 'username' ], $channel, $product );

		// Emoji
		$emoji = get_post_meta( $channel->ID, '_slack_connector_woocommerce_slack_emoji', true );
		if ( $emoji != '' ) {
			$args[ 'icon_emoji' ] = slack_connector_prepare_string_for_slack( $emoji );
			$args[ 'icon_emoji' ] = apply_filters( 'slack_connector_woocommerce_emoji', $args[ 'icon_emoji' ], $channel, $product );
		}

		// Attachment
		$attachment = array();
		
		// Color
		$attachment[ 'color' ] = get_post_meta( $channel->ID, '_slack_connector_woocommerce_out_of_stock_color', true );

		// Title
		$title = get_post_meta( $channel->ID, '_slack_connector_woocommerce_out_of_stock_title', true );
		$search = array( '[product-name]', '[product-id]' );
		$replace = array( $product->get_title(), $product->get_id() );
		$title = str_replace( $search, $replace, $title );
		$attachment[ 'title' ] = slack_connector_prepare_string_for_slack( $title );

		// Title Link
		$attachment[ 'title_link' ] = $product->get_permalink();

		// Set Attachments to args
		$attachment = apply_filters( 'slack_connector_woocommerce_out_of_stock_attachment', $attachment, $channel, $product );
		$args[ 'attachments' ] = array( $attachment );

		// Send the Slack Notification
		slack_connector_send_message( $slack_url, $args );

	}

}

/**
 * New Customer Notification
 *
 * @wp-hook	woocommerce_created_customer
 * @param 	Integer $customer_id
 * @param 	Array $new_customer_data
 * @param 	Boolean $password_generated
 * @return	void
 */
function slack_connector_woocommerce_new_customer( $customer_id, $new_customer_data, $password_generated ) {

	// Get all Slack Channels
	$channels_args = apply_filters( 'slack_connector_woocommerce_get_channels_args', array(
		'posts_per_page'	=> -1,
		'post_type'			=> 'slack_connector'
		) 
	);

	$channels = get_posts( $channels_args );

	foreach ( $channels as $channel ) {

		$slack_url = esc_url( get_post_meta( $channel->ID, '_slack_connector_webhook_url', true ) );
			
		// Don't send if url is not set
		if ( $slack_url == '' ) {
			continue;
		}

		$notification_option = get_post_meta( $channel->ID, '_slack_connector_woocommerce_new_customer_notification', true );

		if ( $notification_option != 'activated' ) {
			continue;
		}

		$args = array();

		// Slack User Name
		$args[ 'username' ] = ( get_post_meta( $channel->ID, '_slack_connector_woocommerce_slack_username', true ) ) == '' ? get_bloginfo( 'name' ) : get_post_meta( $channel->ID, '_slack_connector_woocommerce_slack_username', true );
		$args[ 'username' ] = slack_connector_prepare_string_for_slack( $args[ 'username' ] );
		$args[ 'username' ] = apply_filters( 'slack_connector_woocommerce_username', $args[ 'username' ], $channel, $customer_id, $new_customer_data, $password_generated );

		// Emoji
		$emoji = get_post_meta( $channel->ID, '_slack_connector_woocommerce_slack_emoji', true );
		if ( $emoji != '' ) {
			$args[ 'icon_emoji' ] = slack_connector_prepare_string_for_slack( $emoji );
			$args[ 'icon_emoji' ] = apply_filters( 'slack_connector_bbpress_emoji', $args[ 'icon_emoji' ], $channel, $customer_id, $new_customer_data, $password_generated );
		}

		// Attachment
		$attachment = array();
		
		// Init User Data
		$user = get_user_by( 'id', $customer_id );

		// Color
		$attachment[ 'color' ] = get_post_meta( $channel->ID, '_slack_connector_woocommerce_new_customer_color', true );

		// Title
		$title = get_post_meta( $channel->ID, '_slack_connector_woocommerce_new_customer_title', true );
		$search = array( '[customer-name]' );
		$replace = array( $user->display_name );
		$title = str_replace( $search, $replace, $title );
		$attachment[ 'title' ] = slack_connector_prepare_string_for_slack( $title );

		// Title Link
		$attachment[ 'title_link' ] = esc_url( admin_url( 'user-edit.php?user_id=' . $customer_id ) );

		// Set Attachments to args
		$attachment = apply_filters( 'slack_connector_woocommerce_new_customer_attachment', $attachment, $channel, $customer_id, $new_customer_data );
		$args[ 'attachments' ] = array( $attachment );

		// Send the Slack Notification
		slack_connector_send_message( $slack_url, $args );

	}


}

/**
 * New Review Notification
 *
 * @wp-hook	comment_post
 * @param 	Integer $comment_ID
 * @param 	Integer $comment_approved
 * @param 	Array $commentdata since WordPress 4.5
 * @return	void
 */
function slack_connector_woocommerce_new_review( $comment_ID, $comment_approved, $comment_data ) {

	// Do not send notifications for spam
	if ( $comment_approved == 'spam' ) {
		return;
	}

	// Only for product reviews
	if ( get_post_type( $comment_data[ 'comment_post_ID' ] ) != 'product' ) {
		return;
	}

	// Get all Slack Channels
	$channels_args = apply_filters( 'slack_connector_woocommerce_get_channels_args', array(
		'posts_per_page'	=> -1,
		'post_type'			=> 'slack_connector'
		) 
	);

	$channels = get_posts( $channels_args );

	foreach ( $channels as $channel ) {

		$slack_url = esc_url( get_post_meta( $channel->ID, '_slack_connector_webhook_url', true ) );
			
		// Don't send if url is not set
		if ( $slack_url == '' ) {
			continue;
		}

		$notification_option = get_post_meta( $channel->ID, '_slack_connector_woocommerce_new_review_notification', true );

		if ( $notification_option != 'activated' ) {
			continue;
		}

		$args = array();

		// Slack User Name
		$args[ 'username' ] = ( get_post_meta( $channel->ID, '_slack_connector_woocommerce_slack_username', true ) ) == '' ? get_bloginfo( 'name' ) : get_post_meta( $channel->ID, '_slack_connector_woocommerce_slack_username', true );
		$args[ 'username' ] = slack_connector_prepare_string_for_slack( $args[ 'username' ] );
		$args[ 'username' ] = apply_filters( 'slack_connector_woocommerce_username', $args[ 'username' ], $channel, $comment_ID, $comment_approved, $comment_data );

		// Emoji
		$emoji = get_post_meta( $channel->ID, '_slack_connector_woocommerce_slack_emoji', true );
		if ( $emoji != '' ) {
			$args[ 'icon_emoji' ] = slack_connector_prepare_string_for_slack( $emoji );
			$args[ 'icon_emoji' ] = apply_filters( 'slack_connector_bbpress_emoji', $args[ 'icon_emoji' ], $channel, $comment_ID, $comment_approved, $comment_data );
		}

		// Attachment
		$attachment = array();
		
		// Color
		$attachment[ 'color' ] = get_post_meta( $channel->ID, '_slack_connector_woocommerce_new_review_color', true );

		// Title
		$title = get_post_meta( $channel->ID, '_slack_connector_woocommerce_new_review_title', true );
		$search = array( '[reviewer-name]', '[product-name]' );
		$replace = array( $comment_data[ 'comment_author' ], get_the_title( $comment_data[ 'comment_post_ID' ] ) );
		$title = str_replace( $search, $replace, $title );
		$attachment[ 'title' ] = slack_connector_prepare_string_for_slack( $title );

		// Title Link
		$attachment[ 'title_link' ] = get_comment_link( $comment_ID );

		// Rating
		$show_rating = get_post_meta( $channel->ID, '_slack_connector_woocommerce_new_review_rating', true );
		$star_string = '';
		if ( $show_rating == 'activated' ) {
			$number_of_stars = intval( get_comment_meta( $comment_ID, 'rating', true ) );
			$emoji_for_stars = apply_filters( 'slack_connector_woocommerce_new_review_star_emoji', ':star:' );
			$star_string = str_repeat( $emoji_for_stars, $number_of_stars ) . '\n';
		}

		// Review Text
		$show_review_text = get_post_meta( $channel->ID, '_slack_connector_woocommerce_new_review_review_text', true );
		$review_text = '';
		if ( $show_review_text == 'activated' ) {
			$review_text = slack_connector_prepare_string_for_slack( $comment_data[ 'comment_content' ] );
		}

		$stars_and_review_text = slack_connector_prepare_string_for_slack( $star_string ) . $review_text;
		if ( $stars_and_review_text != '' ) {
			$attachment[ 'text' ] = $stars_and_review_text;
		}

		// Set Attachments to args
		$attachment = apply_filters( 'slack_connector_woocommerce_new_review_attachment', $attachment, $channel, $comment_ID, $comment_approved, $comment_data );
		$args[ 'attachments' ] = array( $attachment );

		// Send the Slack Notification
		slack_connector_send_message( $slack_url, $args );

	}

}
