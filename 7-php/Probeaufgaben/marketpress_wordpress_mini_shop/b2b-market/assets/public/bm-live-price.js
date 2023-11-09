jQuery(document).ready(function( $ ) {
	
  /* ajax request for add to cart */

  var qty_element = '.qty';

  /* avada theme */
  if( $( '.custom-qty' ).length > 0 ) {
      qty_element = '.custom-qty';
  }

  /* erado theme */
  if( $( '.tc' ).length > 0 ) {
    qty_element = '.tc';
  }
  /* luxwine theme */
  if( ! $( '.b2b-single-price' ).length ) {
    $('.summary .price').append('<span class="b2b-single-price"></span>');
  }

  $( ".single_variation_wrap" ).on( "show_variation", function ( event, variation ) {
    $(qty_element).change();
  } );

  $( qty_element ).on('change', function () {

      var id = $('#current_id').data('id');
      
      if ( true == ajax['variable'] ) {
        id = $('.variation_id').val();
      }

      var qty = $(this).val();

      $.ajax({
        type: 'POST',
        url: ajax.ajax_url,
        data: {'action' : 'update_live_price', 'id': id, 'qty' : qty },
        dataType: 'json',
        success: function(data) {
          if ( 0 != data ) {
            /* germanized base price fix */
            if ( $('.summary .price').length > 1 ) {
              var price =  $('.summary .price:first-child');
              price.remove();
              /* remove old b2b price */
              $('.summary .price .b2b-single-price').replaceWith(data['price']);
            } else {
                if ( $('.summary .price').length == 0 ) {
                   /* shopkeeper theme */
                  $('.product_infos .price .b2b-single-price').replaceWith(data['price']);
                } else {
                  $('.summary .price .b2b-single-price').replaceWith(data['price']);
                }
            }
          }
        }
      });     
  
      return false;
  });
});

