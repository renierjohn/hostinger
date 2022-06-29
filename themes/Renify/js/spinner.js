(function($) {
  "use strict";

  init();

  $('.js-paginator-prev').click(function(e){
    e.preventDefault();
    var page = $('.js-paginator').attr('page');
    if(page == 0){
      return;
    }

    $('.card').fadeOut(500);
    $('.js-spinner').fadeIn(500);

    page = Number(page) - 1;
    $('.js-paginator').attr('page',page);
    var template = $(`.js-paginate-cache-${page}`).html();
    $('.js-paginate-block').html(template);
    refresh();
  });

  $('.js-paginator-next').click(function(e){
    e.preventDefault();
    $('.card').fadeOut(500);
    $('.js-spinner').fadeIn(500);

    var page = $('.js-paginator').attr('page');
    page     = Number(page) + 1;
    $('.js-paginator').attr('page',page);


    var template = $(`.js-paginate-cache-${page}`).html();
    if(template != undefined){
       $('.js-paginate-block').html(template);
       refresh();
      return; 
    }


    fetch(`/api/place?page=${page}`).then(function(res){
      res.json().then(function(data){
         render(data);
      });
    }).catch(function(err){
      console.log(err);
    });

  });

  function render(data){
    var page = $('.js-paginator').attr('page');

    if(data.length == 0){
       $('.card').fadeIn(500);
       $('.js-spinner').fadeOut(500);

        page    = Number(page) - 1;
        $('.js-paginator').attr('page',page);
      return;
    }

    var template = ``;
     data.forEach(function(d){
        template += `
        <div class="col-block item-article item-feature" >
                <div class="card">
                  <div class="views">
                    <a href="#."><i class="fa fa-eye"></i> <span id="view_count" class="${d.id}">4</span> view(s)</a>
                  </div>
                  <div class="item-feature-image">
                    <img src="/themes/Renify/images/lazy/lazy.jpg" data-src="${d.image}" class="img-fluid lazy" alt="">
                  </div>
                  <div class="item-feature__text">
                    <h3 class="item-title">${d.title}</h3>
                    <h5 class="item-date"><i class="fa fa-calendar"></i>${d.date}</h5>
                    <p>
                      ${d.body}
                    </p>
                  </div>
                  <div class="article-social">
                    <ul>
                      <li><i class="fa fa-share"></i></li>
                      <li><a href="#." class="share-fb" data-pageid="${d.id}" data-id="${d.id}" data-title="${d.title}" ><i class="fab fa-facebook"></i></a></li>
                      <li><a href="#." class="share-twitter" data-pageid="${d.id}" data-id="${d.id}" data-title="${d.title}"><i class="fab fa-twitter"></i></a></li>
                      <li class="pull-right"><a href="#."><i class="fa fa-eye"></i> <span id="view_count" class="${d.id}"></span> views</a></li>
                      <li class="pull-right"><a href="${d.link}#comment"><i class="fa fa-comment"></i> <span class="fb-comments-count" data-href="https://renifysite.com${d.link}"></span> Comments</a></li>
                    </ul>
                    <a href="${d.link}" class="btn btn--stroke--reverse btn-effect btn--big"><i class="fa fa-eye"></i> Explore</a>
                  </div>
                </div>
              </div>
      `;
    });

    $('.js-paginate-block').html(template);
    var template = `<div class="js-paginate-cache-${page}">${template}</div>`;
    $('.js-paginate-cache').append(template);

    refresh();
  }

  function init(){
    var items    = $('.js-paginate-block').html();
    var template = `<div class="js-paginate-cache-0">${items}</div>`;
    $('.js-paginate-cache').append(template);
  }

  function refresh(){
    $('.card').fadeIn(500);
    $('.js-spinner').fadeOut(500);
    $('.item-feature__text p').trunk8({lines:6})
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

      var url = '/api/count/views?t=';
      var query = '';
      $('.js-paginate-block [id="view_count"]').each(function(index,item){
          if(index == 0){
            query =  $(this).attr('class');
          }
          if(index > 0){
            query = query +','+ $(this).attr('class');
          }
      });
      url = url+query;
      $.ajax(url).then(function(data){      
        $('.js-paginate-block [id="view_count"]').each(function(index,item){
          var id = $(this).attr('class');
          $(this).html(data[id]);
        });
      });
  }

}(jQuery));
