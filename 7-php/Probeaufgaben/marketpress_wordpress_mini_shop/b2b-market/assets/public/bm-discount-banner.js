jQuery(document).ready(function ($) {

    (function () {
        var notice, noticeId, storedNoticeId, dismissButton;
        notice = document.querySelector('.b2b-discount-banner');

        if (!notice) {
            return;
        }
        dismissButton = document.querySelector('.b2b-discount-banner-dismiss');
        noticeId = notice.getAttribute('data-id');
        storedNoticeId = localStorage.getItem('discount_banner_status');

        // This means that the user hasn't already dismissed
        // this specific notice. Let's display it.
        if (noticeId !== storedNoticeId) {
            notice.style.display = 'block';
        }

        dismissButton.addEventListener('click', function () {
            // Hide the notice
            notice.style.display = 'none';

            // Add the current id to localStorage
            localStorage.setItem('discount_banner_status', noticeId);
        });
    }());


});