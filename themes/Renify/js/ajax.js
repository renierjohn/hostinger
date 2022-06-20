(function($) {
  var domain = 'http://renify.local';
  "use strict";
  $('.add-to-cart').bind('click', function(e) {
    var pid = $(this).attr('data-id');
    var quantity = $('.quantity').val();
    var li = "";
    if(quantity > 0){
      var url = $(this).attr('href'); //no need of this line
      e.preventDefault(); // stop the browser from following the link
      var data = JSON.stringify({pid:pid,qty:quantity});
      $.ajax({
        'url' : '/api/cart/add' ,
        'type': 'POST',
        'data': data,
        'success' : function(data) {
          data.cart.forEach(function(data,index){
            li +=`<li>
                <a href="/product/`+data.id+`" class="photo"><img src="/images/`+data.image[0]+`.jpg" class="cart-thumb" alt="" /></a>
                <h6><a href="/product/`+data.id+`" >`+data.title+`</a></h6>
                <p>`+data.qty+` x - <span class="price">`+data.currency+` `+data.price+`</span></p>
            </li>`
          });
          var total = `<li class="total">
                      <a href="/cart" class="btn btn-default hvr-hover btn-cart">VIEW CART</a>
                      <span class="float-right"><strong>Total</strong>: Php `+data.total+`</span>
                  </li>`
          var html = `<ul class="cart-list">`+li+total+`</ul>`;
          $('.cart-wrapper').children().remove();
          $('.cart-wrapper').append(html);
          console.log(html);
        }
      });
    }
  });

  $('.delete-cart').on('click', function(e) {
    var pid = $(this).attr('data-id');
    var tr = "";
    var li = "";
      var url = $(this).attr('href'); //no need of this line
      e.preventDefault(); // stop the browser from following the link
      var data = JSON.stringify({pid:pid});
      $.ajax({
        'url' : '/api/cart/delete' ,
        'type': 'POST',
        'data': data,
        'success' : function(data) {
          data.cart.forEach(function(data,index){
              tr += `<tr>
                      <td class="thumbnail-img"><a href="/product/`+data.id+`"><img class="img-fluid" src="/images/`+data.image[0]+`.jpg" alt="" /></a></td>
                      <td class="name-pr"><a href="/product/`+data.id+`">`+data.title+`</a></td>
                      <td class="price-pr"><p>`+data.currency+` `+data.price+`</p></td>
                      <td class="quantity-box"><input type="number" size="4" value=`+data.qty+` min="0" step="1" class="c-input-text qty text"></td>
                      <td class="total-pr"><p>`+data.currency+` `+data.subtotal+`</p></td>
                      <td class="remove-pr">
                        <a href="#">
                          <i class="fas fa-times delete-cart" data-id=`+data.id+`></i>
                          </a>
                      </td>
                    </tr>`

              li +=`<li>
                    <a href="/product/`+data.id+`" class="photo"><img src="/images/`+data.image[0]+`.jpg" class="cart-thumb" alt="" /></a>
                    <h6><a href="/product/`+data.id+`" >`+data .title+`</a></h6>
                    <p>`+data.qty+` x - <span class="price">`+data.currency+` `+data.price+`</span></p>
                    </li>`

          });
          var total = `<li class="total">
          <a href="/cart" class="btn btn-default hvr-hover btn-cart">VIEW CART</a>
          <span class="float-right"><strong>Total</strong>: Php `+data.total+`</span>
          </li>`
          var html = `<ul class="cart-list">`+li+total+`</ul>`;
          var summary = `
              <h3>Order summary</h3>
              <div class="d-flex">
                <h4>Sub Total</h4>
                <div class="ml-auto font-weight-bold"> Php `+data.total+`</div>
              </div>
              <hr class="my-1">
              <div class="d-flex">
                <h4>Coupon Discount</h4>
                <div class="ml-auto font-weight-bold"> Php 0 </div>
              </div>
              <div class="d-flex">
                <h4>Shipping Fee</h4>
                <div class="ml-auto font-weight-bold">Free </div>
              </div>
              <hr>
              <div class="d-flex gr-total">
                <h5>Total</h5>
                <div class="ml-auto h5">Php `+data.total+`</div>
              </div>
              <hr>
          `
          $('.cart-wrapper').children().remove();
          $('.cart-item-wrapper').children().remove();
          $('.order-box-wrapper').children().remove();
          $('.cart-wrapper').append(html);
          $('.cart-item-wrapper').append(tr);
          $('.order-box-wrapper').append(summary);
        }
      });
  });
  $('#form_submit').bind('click', function(e) {
    e.preventDefault();
    // e.stopPropagation();
    var email   = $('#form_email').val();
    var message = $('#form_message').val();
    var token;
    if(email == false){
      document.getElementById('form_email').style.borderColor = 'red';
      return;
    }
    if(message == false){
      document.getElementById('form_message').style.borderColor = 'red';
      return;
    }
    if(grecaptcha){
       token = grecaptcha.getResponse();
    }

    var data    = JSON.stringify({email:email,message:message,token:token});
    var loader  = `<button type="submit" class="btn--primary full-width "><svg viewBox="0 0 16 16" fill="none" class="m-3" width="32" height="32" style="padding-top: 1rem;">
                    <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-opacity="0.25" stroke-width="2" vector-effect="non-scaling-stroke"></circle>
                    <path d="M15 8a7.002 7.002 0 00-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" vector-effect="non-scaling-stroke">
                      <animateTransform attributeName="transform" type="rotate" from="0 8 8" to="360 8 8" dur="1s" repeatCount="indefinite"></animateTransform>
                    </path>
                    </svg></button>`;
    var btn_succes =   `<input class="btn--primary full-width" type="submit" value="Sent" id="form_submit">`;
    var btn_failed =   `<input class="btn--primary full-width" type="submit" value="Failed" id="form_submit">`;
    $('.message-wrapper').children().remove();
    $('.message-wrapper').append(loader);
    $.ajax({
      'url' : '/api/message' ,
      'type': 'POST',
      'data': data,
      'success' : function(data) {
        if(data.status == 'OK'){
          $('.message-wrapper').children().remove();
          $('.message-wrapper').append(btn_succes);
          window.location.href = '/';
        }
        if(data.status == 'NG'){
          $('.message-wrapper').children().remove();
          $('.message-wrapper').append(btn_failed);
          window.location.href = '/';
        }
      }
    });
  });

  $('.share-fb').on('click', function(e) {
      var pageid = $(this).attr('data-pageid');
      var title  = $(this).attr('data-title');
      var u = location.href;
      var t = document.title;
      
      if(title.length == 0){
          title = 'Renify'
      }

      $.getScript('//connect.facebook.net/en_US/sdk.js', function(){
        FB.init({
          appId: '376061863589278', //replace with your app ID
          version: 'v8.0'
        });
        FB.ui({
              method: 'share',
              title: title,
              description: title,
              href: domain+pageid,
            },
            function(response) {
              if (response && !response.error_code) {
                alert('Posting completed.');
              } else {
                alert('Error while posting.');
              }
          });
      });
    });

    $('.share-twitter').on('click', function(e) {
        var pageid = $(this).attr('data-pageid');
        var title  = $(this).attr('data-title');
        var u = location.href;
        var t = document.title;
        if(title.length == 0){
          title = 'Renify'
        }
        window.open('http://www.twitter.com/share?url='+domain+pageid+'&t='+title,'sharer','toolbar=1,status=1,width=626,height=436');
        return false;
    });

    $('.share-insta').on('click', function(e) {
        var pageid = $(this).attr('data-pageid');
        var title  = $(this).attr('data-title');
        var u = location.href;
        var t = document.title;
        window.open("https://www.instagram.com/renify_official", "_blank", "location=yes");
        return false;
    });

    $('#login-facebook').on('click',function(e){
        FB.login();
    });

    var scope         = "https://www.googleapis.com/auth/userinfo.email";
    var scope_nolink  = "openid profile email";
    var clientID      = "902176944767-2ul1lh81t998833bmnsjah6sg2k9e9p2.apps.googleusercontent.com";
    var redirect_url  = domain+"/google";
    var map_id = "8ebbb0159f03160b";
    var state  = "12345678987654321";
    var promt  = "select_account";
    var response_type = 'code';
    $('#login-google').on('click',function(e){
      document.location = 'https://accounts.google.com/o/oauth2/v2/auth?client_id='+clientID+'&redirect_uri='+redirect_url+'&state='+state+'&prompt='+promt+'&response_type='+response_type+'&include_granted_scopes=true&scope='+scope;
        // window.open('https://accounts.google.com/o/oauth2/v2/auth?client_id='+clientID+'&redirect_uri='+redirect_url+'&state='+state+'&prompt='+promt+'&response_type='+response_type+'&include_granted_scopes=true&scope='+scope);
    });

}(jQuery));