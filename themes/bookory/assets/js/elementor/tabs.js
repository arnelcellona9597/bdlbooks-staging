(function ($) {
    "use strict";
    $(window).on('elementor/frontend/init', () => {
        elementorFrontend.hooks.addAction('frontend/element_ready/bookory-tabs.default', ($scope) => {
            let $tabs = $scope.find('.elementor-tabs');
            let $contents = $scope.find('.elementor-tabs-content-wrapper');
            let $carousel = $('.woocommerce-carousel ul, .bookory-carousel', $scope);

            // Active tab
            $contents.find('.elementor-active').show();

            var windowsize = $(window).width();

            $(window).resize(function () {
                var windowsize = $(window).width();
            });
            if (windowsize > 567) {
                $tabs.find('.elementor-tab-title').on('click', function (e) {
                    e.preventDefault();
                    $tabs.find('.elementor-tab-title').removeClass('elementor-active');
                    $contents.find('.elementor-tab-content').removeClass('elementor-active').hide();
                    $(this).addClass('elementor-active');
                    let id = $(this).attr('aria-controls');
                    $contents.find('#' + id).addClass('elementor-active').show();
                    $carousel.slick('refresh');
                });
            } else {
                $tabs.find('.elementor-tab-title').on('click', function (e) {
                    e.preventDefault();
                    if ($(this).hasClass('elementor-active')) {
                        $(this).removeClass('elementor-active');
                        let id = $(this).attr('aria-controls');
                        $contents.find('#' + id).removeClass('elementor-active').slideUp();
                    } else {
                        $tabs.find('.elementor-tab-title').removeClass('elementor-active');
                        $contents.find('.elementor-tab-content').removeClass('elementor-active').slideUp();
                        $(this).addClass('elementor-active');
                        let id = $(this).attr('aria-controls');
                        $contents.find('#' + id).addClass('elementor-active').slideToggle();
                        $carousel.slick('refresh');
                    }
                });
            }
        });
    });


})(jQuery);
