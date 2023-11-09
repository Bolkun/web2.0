jQuery(document).ready(function ($) {

    /* repeatable bulkprice fields */
    $('#add-row').on('click', function () {
        var row = $('.empty-row.screen-reader-text').clone(true);
        row.removeClass('empty-row screen-reader-text');
        row.insertBefore('#repeatable-fieldset-one tbody>tr:last');
        return false;
    });

    $('.remove-row').on('click', function () {
        $(this).parents('tr').remove();
        return false;
    });

});

//