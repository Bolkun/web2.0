jQuery(document).ready(function ($) {

     /* button positions */
    var export_container = $( '.export-container' );
    
    $( '.export-container' ).remove();
    $( export_container ).insertAfter( "#export_options_raw_data" );

    var import_container = $('.import-container');
    
    $( '.import-container' ).remove();
    $( import_container ).insertAfter( "#import_options_raw_data" );

    /* copy export */
    $("#export_options_raw_data").click(function () {
        $(this).select();
        document.execCommand('copy');
    });

    /* start export trigger */
    $('#submit_export_groups').click(function () {
        $.ajax({
            url: exporter.ajaxurl,
            type: 'post',
            data: {
                action: 'trigger_export',
                security: exporter.nonce
            },
            success: function (response) {
                Cookies.set('export', 'done');
                window.location.href = exporter.export_url;
            }
        })
    });

    /* start import trigger */
    $('#submit_import_groups').click(function () {
        $.ajax({
            url: exporter.ajaxurl,
            type: 'post',
            data: {
                action: 'trigger_import',
                security: exporter.nonce
            },
            success: function (response) {
                 Cookies.set('import', 'done');
                window.location.href = exporter.import_url;
            }
        })
    });

    /* start migrate trigger */
    $('#submit_migrate').click(function () {
        $.ajax({
            url: exporter.ajaxurl,
            type: 'post',
            data: {
                action: 'trigger_migration',
                security: exporter.nonce
            },
            success: function (response) {
                Cookies.set('migrate', 'done');
                window.location.href = exporter.migrate_url;
            }
        })
    });

    /* modal helper */
    function modal_close() {
        $('.modal').fadeOut(1000);
    }
    function modal_destroy() {
        $('.modal').trigger('closeModal');
    }

    /* status messages */
    if ( Cookies.get('export') == 'done') {

        console.log('done');

     $('.modal').easyModal({
        top: 300,
        autoOpen: true,
        overlayOpacity: 0.2,
        overlayClose: true,
        closeOnEscape: true,
    });

    setTimeout(modal_close, 1000);
    setTimeout(modal_destroy, 2000);
    Cookies.remove('export');

    }
    if ( Cookies.get('import') == 'done') {

     $('.modal').easyModal({
        top: 300,
        autoOpen: true,
        overlayOpacity: 0.2,
        overlayClose: true,
        closeOnEscape: true,
    });

    setTimeout(modal_close, 1000);
    setTimeout(modal_destroy, 2000);
    Cookies.remove('import');

    }

    if ( Cookies.get('migrate') == 'done') {

    $('.modal').easyModal({
        top: 300,
        autoOpen: true,
        overlayOpacity: 0.2,
        overlayClose: true,
        closeOnEscape: true,
    });

    setTimeout(modal_close, 1000);
    setTimeout(modal_destroy, 2000);
    Cookies.remove('migrate');
    
    }


});
