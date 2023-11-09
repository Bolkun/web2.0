jQuery(document).ready(function ($) {

    var group = $('#b2b_role').find(":selected").val();

    if ( jQuery.inArray( group, registration.net_tax_groups ) != -1 ) {
        $('#b2b_uid_field label span').html('<abbr class="required" title="erforderlich">*</abbr>');
        $('#b2b_uid_field').css("display", "block");
        $('#b2b_company_registration_number_field').css("display", "block");
    }


    $('#b2b_role').on('change', function () {

        var group = $(this).find(":selected").val();

        if ( group == 'customer' ) {
            $('#b2b_uid_field').css("display", "none");
            $('#b2b_company_registration_number_field').css("display", "none");
        } else {
            if ( jQuery.inArray( group, registration.net_tax_groups ) != -1 ) {
                $('#b2b_uid_field label span').html('<abbr class="required" title="erforderlich">*</abbr>');
                $('#b2b_uid_field').css("display", "block");
                $('#b2b_company_registration_number_field').css("display", "block");
            } else {
                $('#b2b_uid_field').css("display", "none");
                $('#b2b_company_registration_number_field').css("display", "none");
            }
        }
    });
});
