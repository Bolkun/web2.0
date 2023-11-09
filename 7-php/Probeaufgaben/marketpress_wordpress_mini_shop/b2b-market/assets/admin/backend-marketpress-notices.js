jQuery( document ).ready( function() { 

	jQuery( '.marketpress-atomion-gm-b2b-notice-in-b2b button.notice-dismiss' ).ready( function() {

		jQuery( '.marketpress-atomion-gm-b2b-notice-in-b2b button.notice-dismiss' ).on( 'click', function() {
			
			var data = {
				'action': 'b2b_dismiss_marketprss_notice',
			};

			jQuery.post( b2b_marketpress_ajax_object.ajax_url, data, function( response ) {
			});

		});

	});

});
