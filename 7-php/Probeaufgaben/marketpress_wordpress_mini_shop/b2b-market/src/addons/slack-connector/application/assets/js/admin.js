jQuery( document ).ready(function() {

	// Test Notification
	var last_test_send = new Date().getTime();

	jQuery( '#slack-connector-send-test-message' ).click( function() {

		var now = new Date().getTime();

		if ( now - last_test_send > 500 ) {

			last_test_send = now;

			jQuery( '#slack-connector-send-test-message-error' ).fadeOut( 'fast' );

			var url = jQuery( '#_slack_connector_webhook_url' ).val();
			var data = {
				action: 'slack_connector_test_notification',
				security: slack_connector_ajax.nonce,
				slack_url: url
			};

			jQuery.post( slack_connector_ajax.ajax_url, data, function( response ) {
				console.log( response );

				if ( response == 'okay' ) {
					jQuery( '#slack-connector-send-test-message-success' ).stop().hide().fadeIn( 'fast' ).delay( 2000 ).fadeOut( 'fast' );
				} else {
					// Something went wrong
					jQuery( '#slack-connector-send-test-message-error' ).html( response );
					jQuery( '#slack-connector-send-test-message-error' ).fadeIn( 'fast' );
				}
				
			});

		}

	});

	// Select 2
	jQuery( '.slack-connector-select2-multiple' ).select2();

	// WP Color Picker
	jQuery( '.slack-connector-color-picker' ).wpColorPicker();

	// WooCommerce Product / Product Categories
	jQuery( '#_slack_connector_woocommerce_new_order_product_sale_notifications' ).change( function() {

		if ( jQuery( this ).val() == 'product_sold' ) {

			jQuery( '#slack-connector-woocommerce-products' ).slideDown( 'fast' );
			jQuery( '#slack-connector-woocommerce-product-categories' ).slideUp( 'fast' );


		} else if ( jQuery( this ).val() == 'product_category_sold' ) {

			jQuery( '#slack-connector-woocommerce-products' ).slideUp( 'fast' );
			jQuery( '#slack-connector-woocommerce-product-categories' ).slideDown( 'fast' );

		} else {

			jQuery( '#slack-connector-woocommerce-products' ).slideUp( 'fast' );
			jQuery( '#slack-connector-woocommerce-product-categories' ).slideUp( 'fast' );

		}

	});

	// Wordpress Posts / Post Categories
	jQuery( '#_slack_connector_wordpress_new_comment_notifications' ).change( function() {

		if ( jQuery( this ).val() == 'posts' ) {

			jQuery( '#slack-connector-wordpress-new-comment-posts' ).slideDown( 'fast' );
			jQuery( '#slack-connector-wordpress-new-comment-categories' ).slideUp( 'fast' );


		} else if ( jQuery( this ).val() == 'categories' ) {

			jQuery( '#slack-connector-wordpress-new-comment-posts' ).slideUp( 'fast' );
			jQuery( '#slack-connector-wordpress-new-comment-categories' ).slideDown( 'fast' );

		} else {

			jQuery( '#slack-connector-wordpress-new-comment-posts' ).slideUp( 'fast' );
			jQuery( '#slack-connector-wordpress-new-comment-categories' ).slideUp( 'fast' );

		}

	});

});
