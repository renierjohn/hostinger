(function($,drupalSettings) {

 var drupalSettings = {path:{currentPath:'/cart'}};

  console.log(drupalSettings)
  "use strict";
  setCartBadge();
  initButtons();
  initSidebar();
  initCartUrl();
  setTotalPrie();

  $('.p-list-img-thumb img').hover(function(){
    var img = $(this).attr('src');
    $('.p-list-img-main img').attr('src',img)
  })

  $('.p-news-exit').click(function(){
    $('.p-news').fadeOut('fast');
  });

  $('.p-store-product-list a').hover(function(){
    $(this).children().show();
  });

  $('.p-td-close a').click(function(){
    $(this).parent().parent().fadeOut(200);
    var pid = $(this).parent().attr('pid');
    $(`.p-sidebar-list[pid=${pid}]`).fadeOut(200);
    cookieCartHandlerRemover('cart',pid);
    setTotalPrie();
    setCartBadge();
  })

  $('.a-plus').click(function(){
    var pid = $(this).parent().children('input').attr('pid');
    var prevQty = $(this).parent().children('input').val();
    $(this).parent().children('input').val(Number(prevQty) + 1);
    updateSummary(pid,Number(prevQty) + 1);
    setTotalPrie();
    cookieCartHandler('cart',pid,true);
  })

  $('.a-minus').click(function(){
    var pid = $(this).parent().children('input').attr('pid');
    var prevQty = $(this).parent().children('input').val();
    if(prevQty < 2){
      return;
    }
    $(this).parent().children('input').val(Number(prevQty) - 1);
    updateSummary(pid,Number(prevQty) - 1);
    setTotalPrie();
    cookieCartHandler('cart',pid,false);
  })

  $('.sidebar-btn').click(async function(e){
    $('.p-txt-wrapper').removeClass('active');
    $('.p-txt-wrapper',this).addClass('active');
    e.preventDefault();
    var pid    = $(this).attr('pid');
    var title  = $(this).attr('title');
    const data =  await fetch('/api/products/category/'+pid)
                    .then(function(data){
                      return data.json();
                  }).then(function(data){
                      return data;
                  })
    
    var html = data.map(function(data){
        return `
          <div class="col-block p-list-main">
                <div class="p-list-price">
                  Php ${data.price}
                </div>
                <div class="p-list-img">
                  <a href="${data.link}" title="${data.title}">
                  <img src="${data.image}" alt="${data.title}">
                  </a>
                </div>
                <div class="p-list-info">
                  <div class="p-list-info-brand center">${data.title}</div>
                  <div class="p-list-info-rate center">
                    5/10 Bought This
                  </div>
                  <div class="p-list-btn">
                    <a href="#" class="p-add-wishlist" title="wishlist" pid='${data.pid}' ><i class="fas fa-check-circle"></i></a>
                    <button class="p-add-cart" pid='${data.pid}'><i class="fa fa-shopping-cart"></i> ADD</button>
                  </div>
                </div>
              </div>
        `
    })
    $('.p-list-wrapper').html(' ');
    $('.p-list-wrapper').html(html);
    window.history.replaceState(null, null, `/products/${title}/`);
    initButtons();
  })

  function initButtons(){  
    // ADD CART
    $('.p-add-cart').click(function(e){
      e.preventDefault();
      var pid = $(this).attr('pid');
      cookieCartHandler('cart',pid);
      setCartBadge();
    })
    
    // ADD WISHLIST
    $('.p-add-wishlist').click(function(e){
      e.preventDefault();
      var pid = $(this).attr('pid');
      cookieCartHandler('wishlist',pid);
      setCartBadge();
    })
  }

  function updateSummary(pid,qty){
    $(`.p-sidebar-list[pid=${pid}] .p-sidebar-quantity`).fadeOut(0,function(){
      $(this).html(qty);
    }).fadeIn(0);
  }

  function setTotalPrie(){
    var total = 0;
    $(`.p-sidebar-list`).each(function(index,data){
        if($(this).css('display') == 'none'){
          return;
        }
        var price = $('.p-sidebar-price',this).html();
        var qty = $('.p-sidebar-quantity',this).html();
        total += Number(price) * Number(qty);
    })

    var formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'Php',
    });
    var total_formated = formatter.format(total)
    $(`.p-sidebar-total span`).fadeOut(100,function(){
      $(this).html(String(total_formated));
    }).fadeIn(500); 
  }

  function setCookie(key, value) {
    var expiry = 60;
    var expires = new Date();
    expires.setTime(expires.getTime() + (expiry * 24 * 60 * 60 * 1000));
    document.cookie = key + '=' + value + ';path=/' + ';expires=' + expires.toUTCString();
  }

  function readCookie(name) {
    var nameEQ = encodeURIComponent(name) + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ')
            c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0)
            return decodeURIComponent(c.substring(nameEQ.length, c.length));
    }
    return null;  
  }

  function cookieCartHandler(key,pid,inc = true){
    // var pid    = $(this).attr('pid');
    var cookie = readCookie(key);
    var data   = cookie ? JSON.parse(cookie) : '';
    if(data == ''){
      data = [{pid:pid,qty:1}];
      setCookie(key,JSON.stringify(data));
      console.log(data,pid);
      return;
    }
    
    var isExists = false;
    data.forEach(function(data){
      if(data.pid == pid){
        isExists = true;
      }
    });

    var cart_res;
    if(isExists){
      cart_res = data.map(function(data){
                  if(data.pid == pid){
                    return {pid:data.pid,qty: inc ? Number(data.qty)+1 : Number(data.qty)-1};
                  }
                  return {pid:data.pid,qty:data.qty};
                });
    }else{
      data.push({pid:pid,qty:1});
      cart_res = data;
    }

    setCookie(key,JSON.stringify(cart_res));
    console.log(cart_res,pid);
  }

  function cookieCartHandlerRemover(key,pid){
    var cookie = readCookie(key);
    var data   = cookie ? JSON.parse(cookie) : '';
    if(data == ''){
      console.log('not detected');
      return;
    }
    
    var isExists = false;
    data.forEach(function(data){
      if(data.pid == pid){
        isExists = true;
      }
    });

    var cart_arr = [];
    if(isExists){
      data.forEach(function(data){
                  if(data.pid != pid){
                    cart_arr.push({pid:data.pid,qty:data.qty});
                  }
                });
    }

    setCookie(key,JSON.stringify(cart_arr));
    console.log(cart_arr,pid);
  }

  function setCartBadge(){
    var cart = readCookie('cart')
    if(cart == null){
      $('.badge').html(0);
      $('.badge-menu').html(0);
      return;
    }
    var cart_count = JSON.parse(cart).length;
    $('.badge').html(cart_count).fadeOut(100).fadeIn(500);
    $('.badge-menu').html(cart_count).fadeOut(100).fadeIn(500);
  }

  function initSidebar(){
    var path = drupalSettings.path.currentPath;
    if(path.includes('node')){
      return;
    }
    var category_name = path.split('/')[1];
    if(category_name == undefined){
      $('.p-sidebar-menu-all .p-txt-wrapper').addClass('active');
      return;
    }
    $(`.sidebar-btn[title=${category_name}] .p-txt-wrapper`).addClass('active');
  }

  function initCartUrl(){
    var path = drupalSettings.path.currentPath;
    var ts   = new Date().getTime();
    if(path == 'cart'){
      window.history.replaceState(null, null, `?${ts}`);
    }
    $('.cart').attr('href',`/cart/?${ts}`);
  }
  // function renderSideBar(data){
  //   var template = '';
  //   $('.p-sidebar-list-wrapper').html('');
  //   data.forEach(function(data){
  //     template += `
  //       <li class="p-sidebar-list" pid="111">
  //         <div class="p-sidebar-price">
  //           ${data.price} 
  //         </div>
  //         <div>
  //           x
  //         </div>
  //         <div class="p-sidebar-quantity">
  //           ${data.qty} 
  //         </div>
  //       </li>
  //     `;
  //   });
  //   $('.p-sidebar-list-wrapper').html(template);
  //   setTotalPrie();
  // }


}(jQuery,drupalSettings));
