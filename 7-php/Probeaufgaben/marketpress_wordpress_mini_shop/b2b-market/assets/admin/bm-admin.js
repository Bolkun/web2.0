jQuery(document).ready(function ($) {

    /* toggle if product max is set */

    if ( autocomplete_data['product_max'] == 1 ) {
        $('.b2b-third.selection-products').css('display', 'none');
    }

    /* autocomplete for products */

    var product_data = [];

    $(autocomplete_data['products']).each(function () {
        var product = {
            id: this[1],
            text: this[0] + ' (ID: ' + this[1] + ')',
        }
        product_data.push(product);
    });

    $('#searchable-products').selectWoo({
        data: product_data,
        multiple: true,
    });

    $('#searchable-conditional-products').selectWoo({
        data: product_data,
        multiple: true,
    });

    $('#discount-products').selectWoo({
        data: product_data,
        multiple: true,
    });

    $('#bm_guest_users_product_blacklist').selectWoo({
        data: product_data,
        multiple: true,
    });

    /* autocomplete for categories */

    var cat_data = [];

    $(autocomplete_data['categories']).each(function () {

        var cat = {
            id: this[1],
            text: this[0] + ' (ID: ' + this[1] + ')',
        }

        cat_data.push(cat);

    });

    $('#searchable-categories').selectWoo({
        data: cat_data,
        multiple: true,
    });

    $('#searchable-conditional-categories').selectWoo({
        data: cat_data,
        multiple: true,
    });

    $('#searchable-discount-categories').selectWoo({
        data: cat_data,
        multiple: true,
    });

    $('#discount-categories').selectWoo({
        data: cat_data,
        multiple: true,
    });
        
    $('#bm_guest_users_category_blacklist').selectWoo({
        data: cat_data,
        multiple: true,
    });


    /* check from against to for bulk_prices */

    /* global */

    $('input.save-bm-options.bottom').bind('click', function (event) {

        var from_arr = [];
        var to_arr = [];
        var warning = false;

        event.preventDefault();

        $('.bulk-row').each(function () {

            var from = parseInt($(this).find(".bulk_price_from").val());
            var to = parseInt($(this).find(".bulk_price_to").val());

            from_arr.push(from);
            to_arr.push(to);
        });

        var len = from_arr.length;

        for (i = 0; i < len; i++) {

            if (!$.inArray(from_arr[i], to_arr)) {
                warning = true;
            }
        }
        if (warning == true) {
            $("<p class='warning-b2b'>" + autocomplete_data['bulk_valid_message'] + "</p>").insertBefore('thead');
        } else if (warning == false) {
            $('input.save-bm-options.bottom').unbind('click');
            $('input.save-bm-options.bottom').trigger('click');
        }

    });

    /* customer group */

    $('.group-box input#submit').bind('click', function (event) {

        var from_arr = [];
        var to_arr = [];
        var warning = false;

        event.preventDefault();

        $('.bulk-row').each(function () {

            var from = parseInt($(this).find(".bulk_price_from").val());
            var to = parseInt($(this).find(".bulk_price_to").val());

            from_arr.push(from);
            to_arr.push(to);
        });

        var len = from_arr.length;

        for (i = 0; i < len; i++) {

            if (!$.inArray(from_arr[i], to_arr)) {
                warning = true;
            }
        }
        if (warning == true) {
            $("<p class='warning-b2b'>" + autocomplete_data['bulk_valid_message'] + "</p>").insertBefore('table#repeatable-fieldset-one');
        } else if (warning == false) {
            $('.group-box input#submit').unbind('click');
            $('.group-box input#submit').trigger('click');
        }

    });

    /* custom bm checkbox */

    var do_nothing = false;

    jQuery( '.bm-ui-checkbox.switcher' ).click( function() {

        if ( ! jQuery( this ).hasClass( 'clickable' ) ) {
            return;
        }

        jQuery( this ).parent().find( '.bm-ui-checkbox.switcher' ).toggleClass( 'active' );
        jQuery( this ).parent().find( '.bm-ui-checkbox.switcher' ).toggleClass( 'clickable' );
        do_nothing = true;
        jQuery( this ).parent().parent().find( '.slider' ).trigger( 'click' );
        do_nothing = false;
    });

    jQuery( '.bm-slider' ).click( function() {

        if ( ! do_nothing ) {
             jQuery( this ).parent().parent().find( '.bm-ui-checkbox.switcher' ).toggleClass( 'active' );
             jQuery( this ).parent().parent().find( '.bm-ui-checkbox.switcher' ).toggleClass( 'clickable' );
        }

    });

    /* group tabs in product screen */
      var $beefup = $('.beefup').beefup({
        openSingle: true
      });

     $beefup.click($('#group-price'));
     $beefup.click($('#group-quantity'));

    /* function to check if get parameters set */
    var getUrlParameter = function getUrlParameter(sParam) {
        var sPageURL = decodeURIComponent(window.location.search.substring(1)),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : sParameterName[1];
            }
        }
    };

    /* modify screen if edit group */
    $('.customer_group span.edit a').click(function (event) {
        event.preventDefault();
        var id = $(this).data("group");

        if ( 'on' === autocomplete_data['nocache'] ) {
            window.location.replace(autocomplete_data.admin_url + "&group_id=" + id + '&nocache=' + new Date().getTime() );
        } else {
            window.location.replace(autocomplete_data.admin_url + "&group_id=" + id );
        }
    });

    /* modify screen if new group */
    $('.new-group').click(function (event) {
        event.preventDefault();
        if ( 'on' === autocomplete_data['nocache'] ) {
            window.location.replace(autocomplete_data.admin_url + "&group_id=new" + '&nocache=' + new Date().getTime() );
        } else {
            window.location.replace(autocomplete_data.admin_url + "&group_id=new" );
        }
    });


    $(window).on("load", function (e) {
        var group_id = getUrlParameter('group_id');

        if (group_id != undefined) {
            $('.b2b-group-table').toggle();
            $('.group-box').toggle();
        }
    });

    if ( 'on' === autocomplete_data['nocache'] ) {
        $('.groups a').on('click', function(e){
        e.preventDefault();
        window.location.replace( this.href + '&nocache=' + new Date().getTime() );
        });

        $('#backtogroups').on('click', function(e){
            e.preventDefault();
            window.location.replace( this.href + '&nocache=' + new Date().getTime() );
        });
    }

    
});
