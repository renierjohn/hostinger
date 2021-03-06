/* ===================================================================
 * Kairos - Main JS
 *
 * ------------------------------------------------------------------- */

(function($) {
    "use strict";
   
    var $WIN = $(window);

    // Add the User Agent to the <html>
    // will be used for IE10 detection (Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/6.0))
    var doc = document.documentElement;
    doc.setAttribute('data-useragent', navigator.userAgent);

   /* Preloader
    * -------------------------------------------------- */
    var ssPreloader = function() {
        $("body").show();
        $("html").addClass('ss-preload');

        // $WIN.on('load', function() {
        $(document).ready(function() {
            $("#loader").fadeOut("slow", function() {
                $("#preloader").delay(300).fadeOut("slow");
            });

            $("html").removeClass('ss-preload');
            $("html").addClass('ss-loaded');
        });
    };


   /* Menu on Scrolldown
    * ------------------------------------------------------ */
    var ssMenuOnScrolldown = function() {
        var hdr    = $('.top-header');
        // var hdrTop = $('.top-header').offset().top;

        $WIN.on('scroll', function() {

            if ($WIN.scrollTop() > 100) {
                hdr.addClass('sticky');
            }
            else {
                hdr.removeClass('sticky');
            }

        });
    };


   /* Mobile Menu
    * ---------------------------------------------------- */
    var ssMobileMenu = function() {
        var toggleButton = $('.header-menu-toggle'),
            nav = $('.header-nav-wrap');
        toggleButton.on('click', function(event){
            event.preventDefault();
            toggleButton.toggleClass('is-clicked');
            nav.slideToggle();
        });
        if (toggleButton.is(':visible')) nav.addClass('mobile');
        $WIN.on('resize', function() {
            if (toggleButton.is(':visible')) nav.addClass('mobile');
            else nav.removeClass('mobile');
        });
        nav.find('a').on("click", function() {
            if (nav.hasClass('mobile')) {
                toggleButton.toggleClass('is-clicked');
                nav.slideToggle();
            }
        });
    };


   /* Highlight the current section in the navigation bar
    * ------------------------------------------------------ */
    var ssWaypoints = function() {

        var sections = $(".target-section"),
            navigation_links = $(".header-nav-wrap li a");

        sections.waypoint( {

            handler: function(direction) {

                var active_section;

                active_section = $('section#' + this.element.id);

                if (direction === "up") active_section = active_section.prevAll(".target-section").first();

                var active_link = $('.header-nav-wrap li a[href="#' + active_section.attr("id") + '"]');

                navigation_links.parent().removeClass("current");
                active_link.parent().addClass("current");

            },

            offset: '25%'

        });

    };


   /* slick slider
    * ------------------------------------------------------ */
    var ssSlickSlider = function() {

        $('.about-desc__slider').slick({
            arrows: false,
            dots: true,
            infinite: true,
            slidesToShow: 4,
            slidesToScroll: 1,
            pauseOnFocus: false,
            autoplaySpeed: 1500,
            responsive: [
                {
                    breakpoint: 1401,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 1
                    }
                },
                {
                    breakpoint: 1101,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 1
                    }
                },
                {
                    breakpoint: 701,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
            ]
        });

        $('.thumb__slider').slick({
            arrows: false,
            dots: false,
            infinite: true,
            centerMode: true,
            slidesToShow: 3,
            slidesToScroll: 1,
            pauseOnFocus: false,
            autoplaySpeed: 1500,
            responsive: [
                {
                    breakpoint: 1401,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 1
                    }
                },
                {
                    breakpoint: 1101,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 1
                    }
                },
                {
                    breakpoint: 701,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
            ]
        });

        $('.route__slider').slick({
              arrows: false,
              centerMode: true,
              centerPadding: '60px',
              slidesToShow: 3,
              responsive: [
                {
                  breakpoint: 768,
                  settings: {
                    arrows: false,
                    centerMode: true,
                    centerPadding: '40px',
                    slidesToShow: 3
                  }
                },
                {
                  breakpoint: 480,
                  settings: {
                    arrows: false,
                    centerMode: true,
                    centerPadding: '40px',
                    slidesToShow: 1
                  }
                }
              ]
            });

        $('.product-list').slick({
            arrows: false,
            dots: true,
            infinite: true,
            slidesToShow: 2,
            slidesToScroll: 1,
            pauseOnFocus: false,
            autoplaySpeed: 1500,
            responsive: [
                {
                    breakpoint: 1001,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
            ]
        });

        $('.gallery__photos__slide').slick({
            arrows: false,
            centerMode: true,
            dots: true,
            infinite: true,
            slidesToShow: 3,
            slidesToScroll: 1,
            pauseOnFocus: false,
            autoplaySpeed: 1000,
            responsive: [
                {
                    breakpoint: 1001,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 1
                    }
                },
                {
                    breakpoint: 801,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 1
                    }
                },
                {
                    breakpoint: 601,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
            ]
        });
        $('.map__photos__slide').slick({
            arrows: false,
            dots: true,
            infinite: true,
            slidesToShow: 2,
            slidesToScroll: 1,
            centerMode: true,
        });

        $('.slick-prev').click(function(){ 
            $(this).parent().find('.slick-slider').slick('slickPrev');
        });
    
        $('.slick-next').click(function(e){
            e.preventDefault(); 
            $(this).parent().find('.slick-slider').slick('slickNext');
        });

        var related_place_num = $('.related_place_number').length;
        if(related_place_num < 2){
            $('.related_place_btn').hide();
        }
    };


   /* Smooth Scrolling
    * ------------------------------------------------------ */
    // var ssSmoothScroll = function() {

    //     $('.smoothscroll').on('click', function (e) {
    //         var target = this.hash,
    //             $target = $(target);

    //             e.preventDefault();
    //             e.stopPropagation();

    //         $('html, body').stop().animate({
    //             'scrollTop': $target.offset().top
    //         }, 900, 'linear').promise().done(function () {
    //             // check if menu is open
    //             // if ($('body').hasClass('menu-is-open')) {
    //             //     $('.header-menu-toggle').trigger('click');
    //             // }
    //             window.location.hash = target;
    //         });
    //     });
    // };


   /* Alert Boxes
    * ------------------------------------------------------ */
    var ssAlertBoxes = function() {

        $('.alert-box').on('click', '.alert-box__close', function() {
            $(this).parent().fadeOut(500);
        });

    };


   /* Animate On Scroll
    * ------------------------------------------------------ */
    var ssAOS = function() {

        AOS.init( {
            offset: 200,
            duration: 600,
            easing: 'ease-in-sine',
            delay: 300,
            once: true,
            disable: 'mobile'
        });

    };

    var trunc8 = function(){
        $('.search-detail-desc').trunk8({
            lines: 4
        });

        $('.search-detail-route').trunk8({
            lines: 6
        });
    }

    /* Back to Top
    * ------------------------------------------------------ */
    var ssBackToTop = function() {

    var pxShow      = 500,
        goTopButton = $(".go-top");

        // Show or hide the button
        if ($(window).scrollTop() >= pxShow) goTopButton.addClass('link-is-visible');

        $(window).on('scroll', function() {
            if ($(window).scrollTop() >= pxShow) {
                if(!goTopButton.hasClass('link-is-visible')) goTopButton.addClass('link-is-visible')
            } else {
                goTopButton.removeClass('link-is-visible')
            }
        });
    };


   /* AjaxChimp
    * ------------------------------------------------------ */
    // var ssAjaxChimp = function() {
    //
    //     $('#mc-form').ajaxChimp({
    //         language: 'es',
    //         url: cfg.mailChimpURL
    //     });
    //
    //     // Mailchimp translation
    //     //
    //     //  Defaults:
    //     //	 'submit': 'Submitting...',
    //     //  0: 'We have sent you a confirmation email',
    //     //  1: 'Please enter a value',
    //     //  2: 'An email address must contain a single @',
    //     //  3: 'The domain portion of the email address is invalid (the portion after the @: )',
    //     //  4: 'The username portion of the email address is invalid (the portion before the @: )',
    //     //  5: 'This email address looks fake or invalid. Please enter a real email address'
    //
    //     $.ajaxChimp.translations.es = {
    //         'submit': 'Submitting...',
    //         0: '<i class="fas fa-check"></i> We have sent you a confirmation email',
    //         1: '<i class="fas fa-exclamation-triangle"></i> You must enter a valid e-mail address.',
    //         2: '<i class="fas fa-exclamation-triangle"></i> E-mail address is not valid.',
    //         3: '<i class="fas fa-exclamation-triangle"></i> E-mail address is not valid.',
    //         4: '<i class="fas fa-exclamation-triangle"></i> E-mail address is not valid.',
    //         5: '<i class="fas fa-exclamation-triangle"></i> E-mail address is not valid.'
    //     }
    // };


   /* Initialize
    * ------------------------------------------------------ */
    (function clInit() {
        jQuery('.search-detail-desc').hide();
        ssPreloader();
        ssSlickSlider();
        // ssSmoothScroll();
        ssAlertBoxes();
        ssAOS();
        ssBackToTop();
        
    })();
    
    window.onload = function(){
        if($('.search-detail-desc').length > 0){
            $('.search-detail-desc').show();
            trunc8();
        } 
        ssMobileMenu();
        ssMenuOnScrolldown();
    }

})(jQuery);
