(function($) {
  "use strict";
  $('.lazy').Lazy({
        scrollDirection: 'vertical',
        effect: 'fadeIn',
        effectTime:1000,
        threshold:0,
        visibleOnly: true,
        onError: function(element) {
            console.log('error loading ' + element.data('src'));
        }
    });
  var js = '<script src="/assets/js/custom.js"></script>';
  var loader = `<div id="loader" class="article_loader dots-jump">
                  <div></div>
                  <div></div>
                  <div></div>
                </div>`;
  var aos = `<script>
                AOS.init();
            </script>`;
  var tmp_image = `<img src="">`
  $('#next').bind('click', function(e) {
    var pager = $('.articles')[0].attributes.pager.value;
    pager = Number(pager);
    var data = JSON.stringify({pager:pager});
    $('.item-feature__text').children().remove();
    // $('.item-feature-image').children().remove();
    $('.item-feature-image').html('');
    $.ajax({
      'url' : '/api/blogs/next' ,
      'type': 'POST',
      'data': data,
      'success' : function(data) {
          $('.blogs-wrapper').children().remove();
          $('.blogs-wrapper').append(data+js+aos);
            }
          });
        });
    $('#next2').bind('click', function(e) {
      var pager = $('.articles')[0].attributes.pager.value;
      pager = Number(pager);
      var data = JSON.stringify({pager:pager});
      $('.item-feature__text').children().remove();
      $('.item-feature-image').children().remove();
      $('.item-feature-image').append(loader);
      $.ajax({
        'url' : '/api/blogs/next' ,
        'type': 'POST',
        'data': data,
        'success' : function(data) {
            $('.blogs-wrapper').children().remove();
            $('.blogs-wrapper').append(data+js+aos);
              }
            });
          });
  $('#prev').bind('click', function(e) {
    var pager = $('.articles')[0].attributes.pager.value;
    if(pager != 0){
      pager = Number(pager) - 1;
    }
    var data = JSON.stringify({pager:pager});
    $('.item-feature__text').children().remove();
    $('.item-feature-image').children().remove();
    $('.item-feature-image').append(loader);
    $.ajax({
      'url' : '/api/blogs/prev' ,
      'type': 'POST',
      'data': data,
      'success' : function(data) {
          $('.blogs-wrapper').children().remove();
          $('.blogs-wrapper').append(data+js+aos);
      }
    });
  });
  $('#prev2').bind('click', function(e) {
    var pager = $('.articles')[0].attributes.pager.value;
    if(pager != 0){
      pager = Number(pager) - 1;
    }
    var data = JSON.stringify({pager:pager});
    $('.item-feature__text').children().remove();
    $('.item-feature-image').children().remove();
    $('.item-feature-image').append(loader);
    $.ajax({
      'url' : '/api/blogs/prev' ,
      'type': 'POST',
      'data': data,
      'success' : function(data) {
          $('.blogs-wrapper').children().remove();
          $('.blogs-wrapper').append(data+js+aos);
      }
    });
  });
    $(document).ready(function() {
      $.getScript('https://www.google.com/recaptcha/api.js');
    });

    $('.btn-effect').bind('click', function(e) {
      var loader = `<div id="loader" class="btn_loader dots-jump">
                      <div></div>
                      <div></div>
                      <div></div>
                    </div>`;
        $(this).css('width','18rem');
        $(this).html(loader)
    });

  $('.route_list').trunk8({lines:6})

  }(jQuery));
