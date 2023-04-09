(function ($, Drupal) {
  $('.modal').on('click', () => {
    $('.js-modal').hide();
  });

  $('.js-alert').on('click', () => {
    $('.js-modal').show();
  });

})(jQuery, Drupal);
