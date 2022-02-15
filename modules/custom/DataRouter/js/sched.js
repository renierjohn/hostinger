(function($) {
  "use strict";
  main_init();
  init_date();

  $('.select-origin').change(function(){
    $('.features').css({'opacity':.1});
    var origin = $('.select-origin').find(":selected").text();
    var dest = $('.select-dest').find(":selected").text();
    $('.ajax-message').hide();
    getID(origin+' '+dest)
  })

  $('.select-dest').change(function(){
    $('.features').css({'opacity':.1});
    var origin = $('.select-origin').find(":selected").text();
    var dest   = $('.select-dest').find(":selected").text();
    $('.ajax-message').hide();
    getID(origin+' '+dest)
  })


  function main_init(){
    var origin = $('.select-origin').find(":selected").text();
    var dest   = $('.select-dest').find(":selected").text();
    getID(origin+' '+dest);
  }

  function init_date(){
    $('.selected-date').click(function(){
        if($(this).hasClass('disabled')){
          $('.ajax-message').show();
          return;
        }

        var date   = $(this).attr('date');
        var origin = $('.select-origin').find(":selected").text();
        var dest   = $('.select-dest').find(":selected").text();
        if(origin == dest){
          return;
        }
        $('.features').css({'opacity':0.1})
        $('.selected-date').removeClass('active');
        $(this).addClass('active');
        $('.ajax-message').hide();

        var data = $('.ajax_cache').html();
        data = JSON.parse(data)
        render(data,date);
    });
  }


  function getID(route){
    var id = $("input[name='"+route+"']").val();
    console.log(id)
    if(id !== undefined){
      var url = 'https://w3.cokaliongshipping.com/csl/api.html?request=schedule&route='+id;
      $.ajax({
        'url' : url ,
        'type': 'GET',
        'success' : function(data) {
          renderDate(data)
          render(data)
        }
      });
    }else{
      $('.ajax-message').show();
      $('.features').css({'opacity':1});
    }
  }


  function render(data){
      var temp_date = $('.selected-date.active').attr('date');
      date_selected = temp_date
      if(temp_date === undefined){
        var date_selected = getCurrentDate();
      }

      $('.ajax_cache').html(JSON.stringify(data));


      var template_schedule = ``;
      var template_rates    = ``;
      var vessel_name       = ``; 
      var date         = parseDateData(data,date_selected);
      var accomodation = parseVesselData(data);

      if(date.length > 0){
        date.forEach(function(date_val){
          if(date_val.isSelected){
            var dat     = date_val.detail;
            vessel_name = date_val.name;
            template_schedule = `<tr><td>`+dat.vessel+`</td>`+
                                    `<td>`+dat.depature+`</td>`+
                                    `<td>`+dat.arrival+`</td></tr>`
            if(dat.length == 0){
              template_schedule = `<tr><td>---</td>`+
                                    `<td>---</td>`+
                                    `<td>---</td></tr>`
            }
          }
        });


        accomodation.forEach(function(acc_val){
          if(vessel_name == acc_val.name){
            acc_val.detail.forEach(function(dat){
            template_rates += `<tr><td>`+vessel_name+`</td>`+
                               `<td>`+dat.accomodation+`</td>`+
                               `<td>`+dat.rooms+`</td>`+
                               `<td>`+dat.rates+`</td></tr>`;              
            });
          }
        })

        if(template_schedule.length == 0){
              template_schedule = `<tr><td>---</td>`+
                                    `<td>---</td>`+
                                    `<td>---</td></tr>`
        }

        if(template_rates.length == 0){
            template_rates = `<tr><td>---</td>`+
                             `<td>---</td>`+
                             `<td>---</td>`+
                             `<td>---</td></tr>`;

        }

        $('.ajax_schedules').html(template_schedule)
        $('.ajax_rates').html(template_rates)

      }

      $('.features').css({'opacity':1});
  }

  function renderDate(data){
    var template_date = ``;
    
    var temp_date = $('.selected-date.active').attr('date');
      date_selected = temp_date
      if(temp_date === undefined){
        var date_selected = getCurrentDate();
    }

    var date    = parseDateData(data,date_selected);
    date.forEach(function(dat,index){
        var arrDate  = convertDate(dat.date);
        var disabled = dat.isAvailable == true ? '' : 'disabled';
        var active   = dat.isSelected == true ? 'active' : '';

        template_date += `<div  class="ajax_date_list"  data-slick-index="0" aria-hidden="false" tabindex="0">
                          <div class="selected-date `+disabled+` `+active+`" date=`+dat.dt+`>
                          <span class="fa fa-ship ship-pos"></span>
                          <div class=""> `+ arrDate[2]+` </div>
                          <div class=""><em> `+arrDate[0]+` ,</em> `+arrDate[1]+` </div>
                          </div>
                          </div>`;
    });
                          // <div class=""><em> `+arrDate[0]+` ,</em> `+arrDate[1]+` </div>

    ;                      
    $('.ajax_date').html(template_date);
    init_date(); 
    $('.features').css({'opacity':1});
  }

  function getCurrentDate(){
    var date = new Date();
    return date.toISOString().split('T')[0];
  }

  function parseDateData(data,date_selected){
    var dates     = data.schedules;
    var dates_arr = [];
    var day_arr   = [];
    var day_next_mo_arr  = [];

    var current_date = getCurrentDate();
    var current_yr   = current_date.split('-')[0];
    var current_mo   = current_date.split('-')[1];
    var current_day  = current_date.split('-')[2];

    dates.forEach(function(date){
      var mo  = date.depature.split(' ')[0].split('-')[1];
      var day = date.depature.split(' ')[0].split('-')[2];
      
      if(mo == current_mo){
        day_arr.push(day);
      }
      else{
        day_next_mo_arr.push(day);
      }
    });

    var day_max = Math.max(...day_arr);
    var day_min = Math.min(...day_arr);
    var date_obj_arr = [];
    
    for (var i = day_min; i <= day_max; i++) {
      var dt = current_yr+'-'+current_mo+'-'+i;
      var date_obj = new Date(dt)

      date_obj_arr.push({
        'isAvailable':day_arr.indexOf(String(i)) >= 0 ,
        'isSelected':dt == date_selected,
        'date':date_obj.toDateString(),
        'dt':dt,
        'name':getVesselName(data,dt),
        'detail':getDetail(data,dt)
      });
    }


    if(day_next_mo_arr.length > 0){
      day_max = Math.max(...day_next_mo_arr);
      day_min = Math.min(...day_next_mo_arr);

      current_mo = Number(current_mo) + 1
      for (var i = day_min; i <= day_max; i++) {
        var dt = current_yr+'-'+current_mo+'-'+i;
        var date_obj = new Date(dt)
        date_obj_arr.push({
          'isAvailable':day_next_mo_arr.indexOf(String(i)) >= 0 ,
          'isSelected':dt == date_selected,
          'date':date_obj.toDateString(),
          'dt':dt,
          'name':getVesselName(data,dt),
          'detail':getDetail(data,dt)
        });
      }
    }    

    return date_obj_arr;
  }

  function getDetail(data,dt){
      var result = [];
      data.schedules.forEach(function(dat){
        if(dat.depature.split(' ')[0] == dt){
          result = dat;
        }
      })
      return result;
  }

  function getVesselName(data,dt){
      var result = [];
      data.schedules.forEach(function(dat){
        if(dat.depature.split(' ')[0] == dt){
          result = dat.vessel;
        }
      })
      return result;
  }

  function convertDate(date){
    var formatDate = new Date(date);
    var arrDate = formatDate.toDateString().split(' ');
    return arrDate;
  }


  function parseVesselData(data){
      var vessel_arr = data.rates;
      var vessel_name_arr = data.vessels;

      var data_arr = []
      data.vessels.forEach(function(data){
        data_arr.push({name:data,detail:[]})
      });

      var data_arr_copy = data_arr;

      vessel_arr.forEach(function(data){
         data_arr_copy.forEach(function(data_arr_copy_val,index){
            if(data.vessel == data_arr_copy_val.name){
              data_arr[index].detail.push({
                  'accomodation':data.accomodation,
                  'rooms':data.rooms,
                  'rates':data.rate
                });
            }
         })
      });
      return data_arr;
  }

  

  function updateQuery(id,dt){
       window.history.replaceState({
            path:''
        }, "", '?&id='+id+'&dt='+dt);
  }

  function getParameterByName(name, url = window.location.href) {
    name = name.replace(/[\[\]]/g, '\\$&');
    var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, ' '));
  }

  $('.share-clipboard').on('click', function(e) {
      $(".tooltip").show();
      
      var text = window.location.href;
      var sampleTextarea = document.createElement("textarea");
      document.body.appendChild(sampleTextarea);
      sampleTextarea.value = text; //save main text in it
      sampleTextarea.select(); //select textarea contenrs
      document.execCommand("copy");
      document.body.removeChild(sampleTextarea);
      
      $(".tooltip").delay(300).fadeOut("slow");
  });


  $('.share-dynamic-fb').on('click', function(e) {
      var u = location.href;
      var t = document.title;
      
      if(t.length == 0){
          t = 'Renify'
      }

      $.getScript('//connect.facebook.net/en_US/sdk.js', function(){
        FB.init({
          appId: '376061863589278', //replace with your app ID
          version: 'v8.0'
        });
        FB.ui({
              method: 'share',
              title: t,
              description: t,
              href: u,
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

    $('.share-dynamic-twitter').on('click', function(e) {
        var u = location.href;
        var t = document.title;
        if(t.length == 0){
          t = 'Renify'
        }
        
        window.open('http://www.twitter.com/share?url='+u+'&t='+t,'sharer',`menubar=no,
             toolbar=yes,resizable=yes,scrollbars=yes,height=600,width=600`);
        return false;
    });

    $('.share-insta').on('click', function(e) {
        var u = location.href;
        var t = document.title;
        window.open("https://www.instagram.com/renify_renier", "_blank", "location=yes");
        return false;
    });

}(jQuery));
