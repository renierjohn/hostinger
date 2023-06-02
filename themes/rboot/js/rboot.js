/**
 * @file
 * renify_newgen behaviors.
 */
(function ($,Drupal) {

  'use strict';

  Drupal.behaviors.renifyNewgen = {
    attach: function (context, settings) {
      var toggleButton = $('.header-menu-toggle'),
      nav = $('.header-nav-wrap');

      toggleButton.on('click', (e) => {
        e.preventDefault();
        if (!toggleButton.hasClass('menu-flag')) {
          toggleButton.toggleClass('is-clicked');
          toggleButton.toggleClass('menu-flag');
          nav.slideToggle(()=>{
             toggleButton.toggleClass('menu-flag');
           });
        }
      });

      if (toggleButton.is(':visible')){
         nav.addClass('mobile')
       }
    }
  };

} (jQuery, Drupal));
