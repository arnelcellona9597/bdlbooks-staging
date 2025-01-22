(function($) {
    $(document).ready(function() {
        // Add buttons to orders screen.
        var orders_screen = $( '.post-type-shop_order' ),
            title_action = orders_screen.find( '.page-title-action:first' );

        $(title_action).after( '<a href="' + wc_sa_backend.admin_url + '?page=wc_bulk_change_status" class="add-new-h2">' + wc_sa_backend.batch_processing + '</a>' );
    });
})(jQuery, window, document)