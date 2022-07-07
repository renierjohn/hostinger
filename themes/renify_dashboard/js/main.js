(function($) {
    "use strict";

    init();
    setTotalViews();

    $('.layer').click(function(e){
      e.preventDefault();
      $('.users-item-dropdown').removeClass('active');
    })

    $('.js-show-more').click(async function(e){
      e.preventDefault();
      var page = $(this).attr('data');
      var uid  = $('[name="js-current-user"]').val();
      var url  = `/api/dashboard/paginate/${uid}?page=${page}`;
      
      const response  = await fetch(url, {
        method: 'GET', 
        headers: {
          'Content-Type': 'application/json',
        }
      });

      var data = await response.json();

      $(this).attr('data',Number(page) + 1);
      render_loadMore(data);
    });

    async function setTotalViews(){
      var uid = $('[name="js-current-user"]').val();
      
      var api_url  = `/api/dashboard/place/${uid}`;
      const api_response  = await fetch(api_url, { // get the nid belong to current user
        method: 'GET', 
        headers: {
          'Content-Type': 'application/json',
        }
      });
      var api_data  = await api_response.json();
      var ids = [];
      api_data.forEach(function(dat){
        ids.push(dat.id);
      })

      var view_url = `/api/count/views?t=${ids.join()}`;
      const response  = await fetch(view_url, {  // get the total view count
        method: 'GET', 
        headers: {
          'Content-Type': 'application/json',
        }
      });

      var data  = await response.json();

      var id    = Object.keys(data);
      var views = Object.values(data);
      var total = views.reduce(function (a, b) {
          return a + b;
      });

      $('[name="cache-view-count"]').val(JSON.stringify(data));
      $('[id="view_count"]').each(function(index,item){
          var id = $(this).attr('class');
          $(this).html(String(data[id]));
      });

      $('.js-total-views').html(total.toLocaleString());
      $('.js-total-posts').html(views.length);
      renderChart(id.reverse(),views.reverse(),Math.max(...views));
    }

    async function init(){
        var points_new  = $('.js-total-points').html();
        var points_old = $('[prev-points]').attr('prev-points');
        if(points_old > 0){
          var percentage = (Number(points_old)/Number(points_new))*100;
              percentage = Number(percentage).toFixed(2);
          $('.js-total-points-percent').html(`${percentage}%`);
          $('[prev-points]').removeClass('warning');
          $('[prev-points]').addClass('success');
          console.log(percentage);
        }

        $('.dropdown-btn').click(function(e){
            e.preventDefault();
            var dropDown = $(this).parent().find('.users-item-dropdown');
            if(dropDown.hasClass('active')){
              $('.users-item-dropdown').removeClass('active');
              return;
            }
            $('.users-item-dropdown').removeClass('active');
            $(this).parent().find('.users-item-dropdown').addClass('active');
        });

        
        const response  = await fetch('/api/dashboard/user', {  // get the total view count
          method: 'GET', 
          headers: {
            'Content-Type': 'application/json',
          }
        });

        var data  = await response.json();
        render_user_block(data);
    }

    function render_loadMore(data){
        var template      = '';
        var js_option_num = $('.js-show-more').attr('data');
        data.forEach(function(d){
          var status_class = d.status ? 'badge-active' : 'badge-pending';
          var status_label = d.status ? 'Active' : 'Pending';
          template += `
            <tr>
              <td>
                <label class="users-table__checkbox">
                        <div class="categories-table-img">
                    <a href="${d.link}" title="${d.title}" target="_blank">
                      <picture>
                          <img src="${d.image}" loading="lazy" alt="">
                      </picture>
                    </a>
                  </div>
                </label>
              </td>
              <td>
                ${d.id}
              </td>
              <td>
                ${d.title}
              </td>
              <td><span id="view_count" class="${d.id}"></span></td>
              <td><span class="${status_class}">${status_label}</span></td>
              <td>${d.date}</td>
              <td>
                <span class="p-relative">
                  <button class="dropdown-btn transparent-btn js-option-${js_option_num}" type="button" title="More info">
                    <div class="sr-only">More info</div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-horizontal" aria-hidden="true"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                  </button>
                  <ul class="users-item-dropdown dropdown js-option">
                    <li><a href="${d.link}?destination=/dashboard" target="_blank">View </a></li>
                    <li><a href="/node/${d.id}/edit?destination=/dashboard" target="_blank">Edit </a></li>
                    <li><a href="/node/${d.id}/delete?destination=/dashboard" target="_blank">Trash </a></li>
                  </ul>
                </span>
              </td>
            </tr>
          `;
        });

        $('.js-ajax-tbody').append(template);
        refresh_js();
    }

    function refresh_js(){
       var js_option_num = $('.js-show-more').attr('data');
       $(`.js-option-${js_option_num}`).click(function(e){
          e.preventDefault();
          var dropDown = $(this).parent().find('.users-item-dropdown');
          if(dropDown.hasClass('active')){
            $('.users-item-dropdown').removeClass('active');
            return;
          }
          $('.users-item-dropdown').removeClass('active');
          $(this).parent().find('.users-item-dropdown').addClass('active');
        });

        var cache_view_count_json = $('[name="cache-view-count"]').val();
        var data                  = JSON.parse(cache_view_count_json);
        $('[id="view_count"]').each(function(index,item){
          var id = $(this).attr('class');
          $(this).html(String(data[id]));
        });
    }

    function render_user_block(data){
      var template   = '';
      var rank_type = '';
      data.forEach(function(d,index){
        var earn_points  = Number(d.points_new) - Number(d.points_old)
        var status_points = 'warning';
        if(earn_points > 0 && d.points_old > 0){
          earn_points  = `+${earn_points}`;
          status_points = 'success'
        }else{
          earn_points = `0`;
        }

        rank_type = '';
        if(index == 0){
          rank_type = `<span  aria-hidden="true" class="icon star" style="background-color:#ffc107"></span>`
        }
        if(index == 1){
          rank_type = `<span  aria-hidden="true" class="icon star" style="background-color:#9e9e9e"></span>`
        }
        if(index == 2){
          rank_type = `<span  aria-hidden="true" class="icon star" style="background-color:#c59143"></span>`
        }

        template += `
          <li>
            <a href="##">
              <div class="top-cat-list__title">
                ${rank_type} ${d.name} <span>${d.points_new}</span>
              </div>
              <div class="top-cat-list__subtitle">
                Total Points Earned <span class="${status_points}">${earn_points}</span>
              </div>
            </a>
          </li>
        `;
      });
      $('.js-top-user').html(template);
    }

    function renderChart(labels,data,max){
       var ctx = $('#myChart').get(0);
        var charts = {};
        var gridLine;
        var titleColor;

        if (ctx) {
          var myCanvas = ctx.getContext('2d');
          var myChart = new Chart(myCanvas, {
            type: 'bar',
            data: {
              labels: labels,
              datasets: [{
                label: 'View Counts',
                data: data,
                cubicInterpolationMode: 'monotone',
                tension: 0.4,
                backgroundColor: ['rgba(95, 46, 234, 1)'],
                borderColor: ['rgba(95, 46, 234, 1)'],
                borderWidth: 2
              }]
            },
            options: {
              scales: {
                y: {
                  min: 0,
                  max: max,
                  ticks: {
                    stepSize: 10
                  },
                  grid: {
                    display: false
                  }
                },
                x: {
                  grid: {
                    color: gridLine
                  }
                }
              },
              elements: {
                point: {
                  radius: 2
                }
              },
              plugins: {
                legend: {
                  position: 'top',
                  align: 'end',
                  labels: {
                    boxWidth: 8,
                    boxHeight: 8,
                    usePointStyle: true,
                    font: {
                      size: 12,
                      weight: '500'
                    }
                  }
                },
                title: {
                  display: true,
                  text: ['Visitor statistics'],
                  align: 'start',
                  color: '#171717',
                  font: {
                    size: 16,
                    family: 'Inter',
                    weight: '600',
                    lineHeight: 1.4
                  }
                }
              },
              tooltips: {
                mode: 'index',
                intersect: true
              },
              hover: {
                mode: 'nearest',
                intersect: true
              }
            }
          });
          charts.visitors = myChart;
        }
    }

}(jQuery));
