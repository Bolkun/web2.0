<?php
/**
 * Feature Name:  Slack Connector - WooCommerce Meta Boxes
 * Version:       1.0
 * Author:        MarketPress
 * Author URI:    https://marketpress.com
 * Licence:       GPLv3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Add meta boxes for WooCommerce Module
 *
 * @wp-hook	slack_connector_meta_boxes
 * @return	void
 */
function slack_connector_meta_boxes_woocommerce() {

	add_meta_box (	'slack_connector_woocommerce',
					__( 'WooCommerce', 'slack-connector' ),
					'slack_connector_meta_box_woocommerce',
					'slack_connector'
	);

}

/**
 * WooCommerce Metabox
 *
 * @add_meta_box
 * @param 	WP_Post $post
 * @param 	Array $metabox
 * @return	void
 */
function slack_connector_meta_box_woocommerce( $post, $metabox ) {

	// General Input Fields
	slack_connector_general_input_fiels_for_meta_boxes_of_modules( 'woocommerce', 'WooCommerce', $post );

	// Possible Notification Options
	$notification_options = array(
		'deactivated'			=> __( 'Deactivated', 'slack-connector' ),
		'new_order'				=> __( 'New Order', 'slack-connector' ),
		'product_sold'			=> __( 'Product Sale', 'slack-connector' ),
		'product_category_sold'	=> __( 'Product Category Sale' , 'slack-connector'),
	);

	// May change this options
	$notification_options = apply_filters( 'slack_connector_woocommerce_new_order_product_sale_notification_options', $notification_options );

	// Get saved option
	$notification_option = get_post_meta( $post->ID, '_slack_connector_woocommerce_new_order_product_sale_notifications', true );

	?>

	<?php
		/****************************************************************************************/
		/************************ New Order / Product Sale: Notification ************************/
		/****************************************************************************************/
	?>

	<span class="slack-connector-section-title"><?php echo __( 'New Order Settings', 'slack-connector' ); ?></span>

	<label for="_slack_connector_woocommerce_new_order_product_sale_notifications" class="slack-connector-label slack-connector-label-one-row">
		<?php echo __( 'Notifications', 'slack-connector' ); ?>:
	</label>

	<select class="slack-connector-select slack-connector-select-one-row slack-connector-select-notification-options" name="_slack_connector_woocommerce_new_order_product_sale_notifications" id="_slack_connector_woocommerce_new_order_product_sale_notifications">
		<?php

			foreach ( $notification_options as $option_key => $option_string ) {

				// Select string
				$select = ( $notification_option == $option_key ) ? ' selected="selected"' : '';

				?><option value="<?php echo $option_key; ?>"<?php echo $select;?>><?php echo $option_string; ?></option><?php
			}

		?>
	</select>

	<span class="slack-connector-description slack-connector-description-one-row">
		<?php echo __( 'You can choose either to get a notifiaction when you have a new order or when certain products are sold. You can also choose to get notification when products of certain product categories are sold.', 'slack-connector' ); ?>
	</span>

	<br class="slack-connector-clear"/>

	<?php
		/****************************************************************************************/
		/************************ New Order / Product Sale: Products ****************************/
		/****************************************************************************************/
	?>

	<?php $display_none = $notification_option == 'product_sold' ? '' : 'style="display: none;"'; ?>
	<div id="slack-connector-woocommerce-products"<?php echo $display_none;?>>

		<label for="_slack_connector_woocommerce_products" class="slack-connector-label slack-connector-label-one-row slack-connector-cursor-default">
			<?php echo __( 'Products', 'slack-connector' ); ?>:
		</label>

		<?php
			$products_option = get_post_meta( $post->ID, '_slack_connector_woocommerce_products', true );
			if ( $products_option == '' ) {
				$products_option = array();
			}
		?>

		<select multiple class="slack-connector-select slack-connector-select2-multiple slack-connector-select-one-row" name="_slack_connector_woocommerce_products[]" id="_slack_connector_woocommerce_products[]">

			<?php
				// Set args for get_posts to get all products, you can filter these args
				$product_args = apply_filters( 'slack_connector_woocommerce_get_product_args', array(
					'posts_per_page'	=> -1,
					'orderby'			=> 'title',
					'order'				=> 'ASC',
					'post_type'			=> 'product',
					'post_status' 		=> 'any'
					)
				);

				$products = get_posts( $product_args );

				foreach ( $products as $product ) {

					// Select string
					$select = ( in_array( $product->ID, $products_option ) ) ? ' selected="selected"' : '';

					?><option value="<?php echo $product->ID; ?>"<?php echo $select; ?>><?php echo $product->post_title . ' (' . $product->ID .')'; ?></option><?php
				}
			?>

		</select>

	</div>

	<?php
		/**********************************************************************************************/
		/************************ New Order / Product Sale: Product Categories ************************/
		/**********************************************************************************************/
	?>

	<?php $display_none = $notification_option == 'product_category_sold' ? '' : 'style=" display: none;"'; ?>
	<div id="slack-connector-woocommerce-product-categories"<?php echo $display_none;?>>

		<label for="_slack_connector_woocommerce_products" class="slack-connector-label slack-connector-label-one-row">
			<?php echo __( 'Product Categories', 'slack-connector' ); ?>:
		</label>

		<?php
			$product_categories_option = get_post_meta( $post->ID, '_slack_connector_woocommerce_product_categories', true );
			if ( $product_categories_option == '' ) {
				$product_categories_option = array();
			}
		?>

		<select multiple class="slack-connector-select slack-connector-select2-multiple slack-connector-select-one-row" name="_slack_connector_woocommerce_product_categories[]" id="_slack_connector_woocommerce_product_categories[]">
			<?php
				// Set args for get_categories to get all product categories, you can filter these args
				$product_categories_args = apply_filters( 'slack_connector_woocommerce_get_product_categories_args', array(
					'taxonomy'		=> 'product_cat',
					'hide_empty'	=> false,
					)
				);

				$product_categories = get_categories( $product_categories_args );

				foreach ( $product_categories as $product_category ) {

					// Select string
					$select = ( in_array( $product_category->term_id, $product_categories_option ) ) ? ' selected="selected"' : '';

					?><option value="<?php echo $product_category->term_id; ?>"<?php echo $select; ?>><?php echo $product_category->name; ?></option><?php
				}
			?>

		</select>

	</div>

	<br class="slack-connector-clear"/>

	<?php
		/**********************************************************************************************/
		/************************ New Order / Product Sale: Notification Title ************************/
		/**********************************************************************************************/
	?>

	<label for="_slack_connector_woocommerce_new_order_product_sale_title" class="slack-connector-label slack-connector-label-one-row">
		<?php echo __( 'Notification Title', 'slack-connector' ); ?>:
	</label>

	<?php
		$new_order_product_sale_title = get_post_meta( $post->ID, '_slack_connector_woocommerce_new_order_product_sale_title', true );
		if ( $new_order_product_sale_title == '' ) {
			$new_order_product_sale_title = __( 'New sale!', 'slack-connector' );
		}
	?>

	<input type="text" class="slack-connector-input slack-connector-input-text slack-connector-input-text-one-row" name="_slack_connector_woocommerce_new_order_product_sale_title" id="_slack_connector_woocommerce_new_order_product_sale_title" value="<?php echo esc_attr( $new_order_product_sale_title ); ?>"/>

	<span class="slack-connector-description slack-connector-description-one-row">
		<?php echo __( 'Choose a title for your the notification "New Order / Product Sale".', 'slack-connector' ); ?>
		<?php echo ' ' . __( 'You can use the following placeholders', 'slack-connector' ); ?>:
		<code>[order-number]</code>, <code>[order-link]</code>, <code>[first-name]</code>, <code>[last-name]</code>.
		<?php echo __( 'When you set the option to "New Order", you can also use', 'slack-connector' ); ?>:
		<code>[order-total]</code>, <code>[order-items]</code>.
		<?php echo __( 'When you set the option to "Product Sale" or "Product Category Sale", you can also use', 'slack-connector' ); ?>:
		<code>[product-name]</code>.
		<?php echo __ ( 'You can use valid Slack markup, for more details, see <a href="https://get.slack.help/hc/en-us/articles/202288908-How-can-I-add-formatting-to-my-messages-" target="_blank">here</a>', 'slack-connector' ); ?>
	</span>

	<br class="slack-connector-clear"/>

	<?php
		/**********************************************************************************************/
		/************************ New Order / Product Sale: Notification Color ************************/
		/**********************************************************************************************/
	?>

	<label for="_slack_connector_woocommerce_color" class="slack-connector-label slack-connector-label-one-row slack-connector-cursor-default">
		<?php echo __( 'Notification Color', 'slack-connector' ); ?>:
	</label>

	<?php
		$new_order_product_sale_color = get_post_meta( $post->ID, '_slack_connector_woocommerce_color', true );
		if ( $new_order_product_sale_color == '' ) {
			$new_order_product_sale_color = '#27a64b';
			update_post_meta( $post->ID, '_slack_connector_woocommerce_color', $new_order_product_sale_color );
		}
	?>

	<input type="text" class="slack-connector-input slack-connector-input-text slack-connector-input-text-one-row slack-connector-color-picker" name="_slack_connector_woocommerce_color" id="_slack_connector_woocommerce_color" value="<?php echo esc_attr( $new_order_product_sale_color ); ?>"/>

	<span class="slack-connector-description slack-connector-description-one-row slack-connector-description-after-color-picker">
		<?php echo __( 'Choose a color for this kind of notification.', 'slack-connector' ); ?>
	</span>

	<br class="slack-connector-clear"/>

	<?php
		/**********************************************************************************************/
		/************************ New Order / Product Sale: Field: Order ******************************/
		/**********************************************************************************************/
	?>

	<label for="_slack_connector_woocommerce_new_order_product_sale_field_order" class="slack-connector-label slack-connector-label-one-row">
		<?php echo __( 'Field - Order', 'slack-connector' ); ?>:
	</label>

	<?php
		$new_order_product_sale_field_order = get_post_meta( $post->ID, '_slack_connector_woocommerce_new_order_product_sale_field_order', true );
		if ( $new_order_product_sale_field_order == '' ) {
			$new_order_product_sale_field_order = 'activated';
		}
	?>

	<select class="slack-connector-select slack-connector-select-one-row" name="_slack_connector_woocommerce_new_order_product_sale_field_order" id="_slack_connector_woocommerce_new_order_product_sale_field_order">
		<option value="deactivated"><?php echo __( 'Deactivated', 'slack-connector' ); ?></option>
		<option value="activated" <?php echo ( $new_order_product_sale_field_order == 'activated' ) ? 'selected="selected"' : ''; ?>><?php echo __( 'Activated', 'slack-connector' ); ?></option>
	</select>

	<span class="slack-connector-description slack-connector-description-one-row">
		<?php echo __( 'Activate or deactiate a field in your notification that shows the order number with a link to view the order.', 'slack-connector' ); ?>
	</span>

	<br class="slack-connector-clear"/>

	<?php
		/**********************************************************************************************/
		/************************ New Order / Product Sale: Field: Order Total ************************/
		/**********************************************************************************************/
	?>

	<label for="_slack_connector_woocommerce_new_order_product_sale_field_order_total" class="slack-connector-label slack-connector-label-one-row">
		<?php echo __( 'Field - Order Total', 'slack-connector' ); ?>:
	</label>

	<?php
		$new_order_product_sale_field_order_total = get_post_meta( $post->ID, '_slack_connector_woocommerce_new_order_product_sale_field_order_total', true );
		if ( $new_order_product_sale_field_order_total == '' ) {
			$new_order_product_sale_field_order_total = 'activated';
		}
	?>

	<select class="slack-connector-select slack-connector-select-one-row" name="_slack_connector_woocommerce_new_order_product_sale_field_order_total" id="_slack_connector_woocommerce_new_order_product_sale_field_order_total">
		<option value="deactivated"><?php echo __( 'Deactivated', 'slack-connector' ); ?></option>
		<option value="activated" <?php echo ( $new_order_product_sale_field_order_total == 'activated' ) ? 'selected="selected"' : ''; ?>><?php echo __( 'Activated', 'slack-connector' ); ?></option>
	</select>

	<span class="slack-connector-description slack-connector-description-one-row">
		<?php echo __( 'Activate or deactiate a field in your notification that shows the order total.', 'slack-connector' ); ?>
	</span>

	<br class="slack-connector-clear"/>

	<?php
		/**********************************************************************************************/
		/************************ New Order / Product Sale: Field: Customer ***************************/
		/**********************************************************************************************/
	?>

	<label for="_slack_connector_woocommerce_new_order_product_sale_field_customer" class="slack-connector-label slack-connector-label-one-row">
		<?php echo __( 'Field - Customer', 'slack-connector' ); ?>:
	</label>

	<?php
		$new_order_product_sale_field_customer = get_post_meta( $post->ID, '_slack_connector_woocommerce_new_order_product_sale_field_customer', true );
		if ( $new_order_product_sale_field_customer == '' ) {
			$new_order_product_sale_field_customer = 'activated';
		}
	?>

	<select class="slack-connector-select slack-connector-select-one-row" name="_slack_connector_woocommerce_new_order_product_sale_field_customer" id="_slack_connector_woocommerce_new_order_product_sale_field_customer">
		<option value="deactivated"><?php echo __( 'Deactivated', 'slack-connector' ); ?></option>
		<option value="activated" <?php echo ( $new_order_product_sale_field_customer == 'activated' ) ? 'selected="selected"' : ''; ?>><?php echo __( 'Activated', 'slack-connector' ); ?></option>
	</select>

	<span class="slack-connector-description slack-connector-description-one-row">
		<?php echo __( 'Activate or deactiate a field in your notification that shows the name of the customer.', 'slack-connector' ); ?>
	</span>

	<br class="slack-connector-clear"/>

	<?php
		/**********************************************************************************************/
		/************************ New Order / Product Sale: Field: Items ******************************/
		/**********************************************************************************************/
	?>

	<label for="_slack_connector_woocommerce_new_order_product_sale_field_items" class="slack-connector-label slack-connector-label-one-row">
		<?php echo __( 'Field - Items', 'slack-connector' ); ?>:
	</label>

	<?php
		$new_order_product_sale_field_items = get_post_meta( $post->ID, '_slack_connector_woocommerce_new_order_product_sale_field_items', true );
		if ( $new_order_product_sale_field_items == '' ) {
			$new_order_product_sale_field_items = 'activated';
		}
	?>

	<select class="slack-connector-select slack-connector-select-one-row" name="_slack_connector_woocommerce_new_order_product_sale_field_items" id="_slack_connector_woocommerce_new_order_product_sale_field_items">
		<option value="deactivated"><?php echo __( 'Deactivated', 'slack-connector' ); ?></option>
		<option value="activated" <?php echo ( $new_order_product_sale_field_items == 'activated' ) ? 'selected="selected"' : ''; ?>><?php echo __( 'Activated with Price', 'slack-connector' ); ?></option>
		<option value="activated_without_price" <?php echo ( $new_order_product_sale_field_items == 'activated_without_price' ) ? 'selected="selected"' : ''; ?>><?php echo __( 'Activated without Price', 'slack-connector' ); ?></option>
	</select>

	<span class="slack-connector-description slack-connector-description-one-row">
		<?php echo __( 'Activate or deactiate a field in your notification that shows the items of the order, respectively the product name if you set the option "Notifications" to "Product Sale" or "Product Category Sale". You can choose whether the price is shown or not.', 'slack-connector' ); ?>
	</span>

	<br class="slack-connector-clear"/>

	<?php do_action( 'slack_connector_woocommerce_after_meta_boxes_new_order' ); ?>

	<hr />

	<span class="slack-connector-section-title"><?php echo __( 'Low Stock Settings', 'slack-connector' ); ?></span>

	<br class="slack-connector-clear"/>

	<?php
		/****************************************************************************************/
		/************************ Low On Stock: Notification ************************/
		/****************************************************************************************/
	?>

	<label for="_slack_connector_woocommerce_low_stock_notification" class="slack-connector-label slack-connector-label-one-row">
		<?php echo __( 'Notifications', 'slack-connector' ); ?>:
	</label>

	<?php $low_stock_notification = get_post_meta( $post->ID, '_slack_connector_woocommerce_low_stock_notification', true ); ?>

	<select class="slack-connector-select slack-connector-select-one-row" name="_slack_connector_woocommerce_low_stock_notification" id="_slack_connector_woocommerce_low_stock_notification">
		<option value="deactivated"><?php echo __( 'Deactivated', 'slack-connector' ); ?></option>
		<option value="activated" <?php echo ( $low_stock_notification == 'activated' ) ? 'selected="selected"' : ''; ?>><?php echo __( 'Activated', 'slack-connector' ); ?></option>
	</select>

	<span class="slack-connector-description slack-connector-description-one-row">
		<?php echo __( 'You can get a notification when a product is low on stock.', 'slack-connector' ); ?>
	</span>

	<br class="slack-connector-clear"/>

	<?php
		/**********************************************************************************************/
		/************************ Low On Stock: Notification Title ************************************/
		/**********************************************************************************************/
	?>

	<label for="_slack_connector_woocommerce_low_stock_title" class="slack-connector-label slack-connector-label-one-row">
		<?php echo __( 'Notification Title', 'slack-connector' ); ?>:
	</label>

	<?php
		$low_stock_title = get_post_meta( $post->ID, '_slack_connector_woocommerce_low_stock_title', true );
		if ( $low_stock_title == '' ) {
			$low_stock_title = __( 'Low on stock: The product [product-name] ([product-id]) is low on stock!', 'slack-connector' );
		}
	?>

	<input type="text" class="slack-connector-input slack-connector-input-text slack-connector-input-text-one-row" name="_slack_connector_woocommerce_low_stock_title" id="_slack_connector_woocommerce_low_stock_title" value="<?php echo esc_attr( $low_stock_title ); ?>"/>

	<span class="slack-connector-description slack-connector-description-one-row">
		<?php echo __( 'Choose a title for the notification "Low on Stock".', 'slack-connector' ); ?>
		<?php echo ' ' . __( 'You can use the following placeholders', 'slack-connector' ); ?>:
		<code>[product-id]</code>, <code>[product-name]</code>.
	</span>

	<br class="slack-connector-clear"/>

	<?php
		/**********************************************************************************************/
		/************************ Low On Stock: Notification Color ************************/
		/**********************************************************************************************/
	?>

	<label for="_slack_connector_woocommerce_low_stock_color" class="slack-connector-label slack-connector-label-one-row slack-connector-cursor-default">
		<?php echo __( 'Notification Color', 'slack-connector' ); ?>:
	</label>

	<?php
		$low_stock_color = get_post_meta( $post->ID, '_slack_connector_woocommerce_low_stock_color', true );
		if ( $low_stock_color == '' ) {
			$low_stock_color = '#ffcc00';
			update_post_meta( $post->ID, '_slack_connector_woocommerce_low_stock_color', $low_stock_color );
		}
	?>

	<input type="text" class="slack-connector-input slack-connector-input-text slack-connector-input-text-one-row slack-connector-color-picker" name="_slack_connector_woocommerce_low_stock_color" id="_slack_connector_woocommerce_low_stock_color" value="<?php echo esc_attr( $low_stock_color ); ?>"/>

	<span class="slack-connector-description slack-connector-description-one-row slack-connector-description-after-color-picker">
		<?php echo __( 'Choose a color for this kind of notification.', 'slack-connector' ); ?>
	</span>

	<br class="slack-connector-clear"/>

	<?php do_action( 'slack_connector_woocommerce_after_meta_boxes_low_stock' ); ?>

	<hr />

	<span class="slack-connector-section-title"><?php echo __( 'Out of Stock Settings', 'slack-connector' ); ?></span>

	<br class="slack-connector-clear"/>

	<?php
		/****************************************************************************************/
		/************************ Out of Stock: Notification ************************************/
		/****************************************************************************************/
	?>

	<label for="_slack_connector_woocommerce_out_of_stock_notification" class="slack-connector-label slack-connector-label-one-row">
		<?php echo __( 'Notifications', 'slack-connector' ); ?>:
	</label>

	<?php $out_of_stock_notification = get_post_meta( $post->ID, '_slack_connector_woocommerce_out_of_stock_notification', true ); ?>

	<select class="slack-connector-select slack-connector-select-one-row" name="_slack_connector_woocommerce_out_of_stock_notification" id="_slack_connector_woocommerce_out_of_stock_notification">
		<option value="deactivated"><?php echo __( 'Deactivated', 'slack-connector' ); ?></option>
		<option value="activated" <?php echo ( $out_of_stock_notification == 'activated' ) ? 'selected="selected"' : ''; ?>><?php echo __( 'Activated', 'slack-connector' ); ?></option>
	</select>

	<span class="slack-connector-description slack-connector-description-one-row">
		<?php echo __( 'You can get a notification when a product is out of stock.', 'slack-connector' ); ?>
	</span>

	<br class="slack-connector-clear"/>

	<?php
		/**********************************************************************************************/
		/************************ Out of Stock: Notification Title ************************************/
		/**********************************************************************************************/
	?>

	<label for="_slack_connector_woocommerce_out_of_stock_title" class="slack-connector-label slack-connector-label-one-row">
		<?php echo __( 'Notification Title', 'slack-connector' ); ?>:
	</label>

	<?php
		$out_of_stock_title = get_post_meta( $post->ID, '_slack_connector_woocommerce_out_of_stock_title', true );
		if ( $out_of_stock_title == '' ) {
			$out_of_stock_title = __( 'Out of stock: The product [product-name] ([product-id]) is out of stock!', 'slack-connector' );
		}
	?>

	<input type="text" class="slack-connector-input slack-connector-input-text slack-connector-input-text-one-row" name="_slack_connector_woocommerce_out_of_stock_title" id="_slack_connector_woocommerce_out_of_stock_title" value="<?php echo esc_attr( $out_of_stock_title ); ?>"/>

	<span class="slack-connector-description slack-connector-description-one-row">
		<?php echo __( 'Choose a title for the notification "Out of Stock".', 'slack-connector' ); ?>
		<?php echo ' ' . __( 'You can use the following placeholders', 'slack-connector' ); ?>:
		<code>[product-id]</code>, <code>[product-name]</code>.
	</span>

	<br class="slack-connector-clear"/>

	<?php
		/**********************************************************************************************/
		/************************ Out of Stock: Notification Color ************************************/
		/**********************************************************************************************/
	?>

	<label for="_slack_connector_woocommerce_out_of_stock_color" class="slack-connector-label slack-connector-label-one-row slack-connector-cursor-default">
		<?php echo __( 'Notification Color', 'slack-connector' ); ?>:
	</label>

	<?php
		$out_of_stock_color = get_post_meta( $post->ID, '_slack_connector_woocommerce_out_of_stock_color', true );
		if ( $out_of_stock_color == '' ) {
			$out_of_stock_color = '#ff0000';
			update_post_meta( $post->ID, '_slack_connector_woocommerce_out_of_stock_color', $out_of_stock_color );
		}
	?>

	<input type="text" class="slack-connector-input slack-connector-input-text slack-connector-input-text-one-row slack-connector-color-picker" name="_slack_connector_woocommerce_out_of_stock_color" id="_slack_connector_woocommerce_out_of_stock_color" value="<?php echo esc_attr( $out_of_stock_color ); ?>"/>

	<span class="slack-connector-description slack-connector-description-one-row slack-connector-description-after-color-picker">
		<?php echo __( 'Choose a color for this kind of notification.', 'slack-connector' ); ?>
	</span>

	<br class="slack-connector-clear"/>

	<?php do_action( 'slack_connector_woocommerce_after_meta_boxes_out_of_stock' ); ?>

	<hr />

	<span class="slack-connector-section-title"><?php echo __( 'New Customer Settings', 'slack-connector' ); ?></span>

	<br class="slack-connector-clear"/>

	<?php
		/****************************************************************************************/
		/************************ New Customer: Notification ************************************/
		/****************************************************************************************/
	?>

	<label for="_slack_connector_woocommerce_new_customer_notification" class="slack-connector-label slack-connector-label-one-row">
		<?php echo __( 'Notifications', 'slack-connector' ); ?>:
	</label>

	<?php $new_customer_notification = get_post_meta( $post->ID, '_slack_connector_woocommerce_new_customer_notification', true ); ?>

	<select class="slack-connector-select slack-connector-select-one-row" name="_slack_connector_woocommerce_new_customer_notification" id="_slack_connector_woocommerce_new_customer_notification">
		<option value="deactivated"><?php echo __( 'Deactivated', 'slack-connector' ); ?></option>
		<option value="activated" <?php echo ( $new_customer_notification == 'activated' ) ? 'selected="selected"' : ''; ?>><?php echo __( 'Activated', 'slack-connector' ); ?></option>
	</select>

	<span class="slack-connector-description slack-connector-description-one-row">
		<?php echo __( 'You can get a notification when a new customer has been created by WooCommerce.', 'slack-connector' ); ?>
	</span>

	<br class="slack-connector-clear"/>

	<?php
		/**********************************************************************************************/
		/************************ New Customer: Notification Title ************************************/
		/**********************************************************************************************/
	?>

	<label for="_slack_connector_woocommerce_new_customer_title" class="slack-connector-label slack-connector-label-one-row">
		<?php echo __( 'Notification Title', 'slack-connector' ); ?>:
	</label>

	<?php
		$new_customer_title = get_post_meta( $post->ID, '_slack_connector_woocommerce_new_customer_title', true );
		if ( $new_customer_title == '' ) {
			$new_customer_title = __( 'New Customer: [customer-name]', 'slack-connector' );
		}
	?>

	<input type="text" class="slack-connector-input slack-connector-input-text slack-connector-input-text-one-row" name="_slack_connector_woocommerce_new_customer_title" id="_slack_connector_woocommerce_new_customer_title" value="<?php echo esc_attr( $new_customer_title ); ?>"/>

	<span class="slack-connector-description slack-connector-description-one-row">
		<?php echo __( 'Choose a title for the notification "New Customer".', 'slack-connector' ); ?>
		<?php echo ' ' . __( 'You can use the following placeholder', 'slack-connector' ); ?>:
		<code>[customer-name]</code>.
	</span>

	<br class="slack-connector-clear"/>

	<?php
		/**********************************************************************************************/
		/************************ New Customer: Notification Color ************************************/
		/**********************************************************************************************/
	?>

	<label for="_slack_connector_woocommerce_new_customer_color" class="slack-connector-label slack-connector-label-one-row slack-connector-cursor-default">
		<?php echo __( 'Notification Color', 'slack-connector' ); ?>:
	</label>

	<?php
		$new_customer_color = get_post_meta( $post->ID, '_slack_connector_woocommerce_new_customer_color', true );
		if ( $new_customer_color == '' ) {
			$new_customer_color = '#27a64b';
			update_post_meta( $post->ID, '_slack_connector_woocommerce_new_customer_color', $new_customer_color );
		}
	?>

	<input type="text" class="slack-connector-input slack-connector-input-text slack-connector-input-text-one-row slack-connector-color-picker" name="_slack_connector_woocommerce_new_customer_color" id="_slack_connector_woocommerce_new_customer_color" value="<?php echo esc_attr( $new_customer_color ); ?>"/>

	<span class="slack-connector-description slack-connector-description-one-row slack-connector-description-after-color-picker">
		<?php echo __( 'Choose a color for this kind of notification.', 'slack-connector' ); ?>
	</span>

	<br class="slack-connector-clear"/>

	<?php do_action( 'slack_connector_woocommerce_after_meta_boxes_new_customer' ); ?>

	<hr />

	<span class="slack-connector-section-title"><?php echo __( 'New Review Settings', 'slack-connector' ); ?></span>

	<br class="slack-connector-clear"/>

	<?php
		/****************************************************************************************/
		/************************ New Review: Notification **************************************/
		/****************************************************************************************/
	?>

	<label for="_slack_connector_woocommerce_new_review_notification" class="slack-connector-label slack-connector-label-one-row">
		<?php echo __( 'Notifications', 'slack-connector' ); ?>:
	</label>

	<?php $new_review = get_post_meta( $post->ID, '_slack_connector_woocommerce_new_review_notification', true ); ?>

	<select class="slack-connector-select slack-connector-select-one-row" name="_slack_connector_woocommerce_new_review_notification" id="_slack_connector_woocommerce_new_review_notification">
		<option value="deactivated"><?php echo __( 'Deactivated', 'slack-connector' ); ?></option>
		<option value="activated" <?php echo ( $new_review == 'activated' ) ? 'selected="selected"' : ''; ?>><?php echo __( 'Activated', 'slack-connector' ); ?></option>
	</select>

	<span class="slack-connector-description slack-connector-description-one-row">
		<?php echo __( 'You can get a notification when new customer has been created by WooCommerce.', 'slack-connector' ); ?>
	</span>

	<br class="slack-connector-clear"/>

	<?php
		/**********************************************************************************************/
		/************************ New Review: Notification Title **************************************/
		/**********************************************************************************************/
	?>

	<label for="_slack_connector_woocommerce_new_review_title" class="slack-connector-label slack-connector-label-one-row">
		<?php echo __( 'Notification Title', 'slack-connector' ); ?>:
	</label>

	<?php
		$new_review = get_post_meta( $post->ID, '_slack_connector_woocommerce_new_review_title', true );
		if ( $new_review == '' ) {
			$new_review = __( 'New Review: by [reviewer-name] for [product-name]', 'slack-connector' );
		}
	?>

	<input type="text" class="slack-connector-input slack-connector-input-text slack-connector-input-text-one-row" name="_slack_connector_woocommerce_new_review_title" id="_slack_connector_woocommerce_new_review_title" value="<?php echo esc_attr( $new_review ); ?>"/>

	<span class="slack-connector-description slack-connector-description-one-row">
		<?php echo __( 'Choose a title for the notification "New Review".', 'slack-connector' ); ?>
		<?php echo ' ' . __( 'You can use the following placeholders', 'slack-connector' ); ?>:
		<code>[reviewer-name]</code>, <code>[product-name]</code>.
	</span>

	<br class="slack-connector-clear"/>

	<?php
		/**********************************************************************************************/
		/************************ New Review: Notification Color **************************************/
		/**********************************************************************************************/
	?>

	<label for="_slack_connector_woocommerce_new_review_color" class="slack-connector-label slack-connector-label-one-row slack-connector-cursor-default">
		<?php echo __( 'Notification Color', 'slack-connector' ); ?>:
	</label>

	<?php
		$new_review_color = get_post_meta( $post->ID, '_slack_connector_woocommerce_new_review_color', true );
		if ( $new_review_color == '' ) {
			$new_review_color = '#0bd7e7';
			update_post_meta( $post->ID, '_slack_connector_woocommerce_new_review_color', $new_review_color );
		}
	?>

	<input type="text" class="slack-connector-input slack-connector-input-text slack-connector-input-text-one-row slack-connector-color-picker" name="_slack_connector_woocommerce_new_review_color" id="_slack_connector_woocommerce_new_review_color" value="<?php echo esc_attr( $new_review_color ); ?>"/>

	<span class="slack-connector-description slack-connector-description-one-row slack-connector-description-after-color-picker">
		<?php echo __( 'Choose a color for this kind of notification.', 'slack-connector' ); ?>
	</span>

	<br class="slack-connector-clear"/>

	<?php
		/**********************************************************************************************/
		/************************ New Review: Review Text *********************************************/
		/**********************************************************************************************/
	?>

	<label for="_slack_connector_woocommerce_new_review_review_text" class="slack-connector-label slack-connector-label-one-row">
		<?php echo __( 'Review Text', 'slack-connector' ); ?>:
	</label>

	<?php
		$new_review_review_text = get_post_meta( $post->ID, '_slack_connector_woocommerce_new_review_review_text', true );
		if ( $new_review_review_text == '' ) {
			$new_review_review_text = 'activated';
		}
	?>

	<select class="slack-connector-select slack-connector-select-one-row" name="_slack_connector_woocommerce_new_review_review_text" id="_slack_connector_woocommerce_new_order_product_sale_field_order">
		<option value="deactivated"><?php echo __( 'Deactivated', 'slack-connector' ); ?></option>
		<option value="activated" <?php echo ( $new_review_review_text == 'activated' ) ? 'selected="selected"' : ''; ?>><?php echo __( 'Activated', 'slack-connector' ); ?></option>
	</select>

	<span class="slack-connector-description slack-connector-description-one-row">
		<?php echo __( 'Choose whether the review text is shown in the notification.', 'slack-connector' ); ?>
	</span>

	<br class="slack-connector-clear"/>

	<?php
		/**********************************************************************************************/
		/************************ New Review: Review Rating *******************************************/
		/**********************************************************************************************/
	?>

	<label for="_slack_connector_woocommerce_new_review_rating" class="slack-connector-label slack-connector-label-one-row">
		<?php echo __( 'Rating', 'slack-connector' ); ?>:
	</label>

	<?php
		$new_review_rating = get_post_meta( $post->ID, '_slack_connector_woocommerce_new_review_rating', true );
		if ( $new_review_rating == '' ) {
			$new_review_rating = 'activated';
		}
	?>

	<select class="slack-connector-select slack-connector-select-one-row" name="_slack_connector_woocommerce_new_review_rating" id="_slack_connector_woocommerce_new_review_rating">
		<option value="deactivated"><?php echo __( 'Deactivated', 'slack-connector' ); ?></option>
		<option value="activated" <?php echo ( $new_review_rating == 'activated' ) ? 'selected="selected"' : ''; ?>><?php echo __( 'Activated', 'slack-connector' ); ?></option>
	</select>

	<span class="slack-connector-description slack-connector-description-one-row">
		<?php echo __( 'Choose whether the review rating is shown in the notification. A 5-star rating system is used to display the rating.', 'slack-connector' ); ?>
	</span>

	<br class="slack-connector-clear"/>

	<?php do_action( 'slack_connector_woocommerce_after_meta_boxes_new_review' ); ?>

	<?php

	do_action( 'slack_connector_woocommerce_after_meta_boxes' );

}
