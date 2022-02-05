(function($) {
  "use strict";
  main_init();
  init_date_selectors();

  // select origin
  $('.select-origin').change(function(){
  	$('.features').css({'opacity':0.1})
  	var date   = $('.selected-date.active').attr('date');
    var origin = $('.select-origin').find(":selected").val();
    var dest   = $('.select-dest').find(":selected").val();
    $('.ajax-message').hide();
    getID(origin,dest,date);
    getDateRange(origin,dest,getCurrentDate());
  })

  // select destination
  $('.select-dest').change(function(){
  	$('.features').css({'opacity':0.1})
  	var date   = $('.selected-date.active').attr('date');
    var origin = $('.select-origin').find(":selected").val();
    var dest   = $('.select-dest').find(":selected").val();
    $('.ajax-message').hide();
    getID(origin,dest,date);
    getDateRange(origin,dest,getCurrentDate());
  })

  function getID(origin,dest,date){
    var uuid = $("input[name='uuid']").val()
		var settings = {
			  "url": "https://barkota-reseller-php-prod-4kl27j34za-uc.a.run.app/ob/voyages/search/bylocation",
			  "method": "POST",
			  "timeout": 0,
			  "headers": {
			    "Content-Type": "application/json"
			  },
			  "data": JSON.stringify({
			    "origin": origin,
			    "destination": dest,
			    "departureDate": date,
			    "passengerCount": 1,
			    "shippingCompany": uuid,
			    "cargoItemId": null,
			    "withDriver": 1
		  }),
		};

			$.ajax(settings).done(function (data) {
				if(data.length == 0){
		    		$('.ajax-message').show();
				}
			  renderTable(data);	
			});
  }

  function getDateRange(origin,dest,date){
  	var uuid = $("input[name='uuid']").val()
		var settings = {
		  "url": "https://barkota-reseller-php-prod-4kl27j34za-uc.a.run.app/ob/voyages/available-dates/passageandcargo",
		  "method": "POST",
		  "timeout": 0,
		  "headers": {
		    "Content-Type": "application/json"
		  },
		  "data": JSON.stringify({
		    "origin": origin,
		    "destination": dest,
		    "departureDate": date,
		    "passengerCount": 1,
		    "shippingCompany": uuid,
		    "cargoItemId": null,
		    "withDriver": 1
		  }),
		};

		$.ajax(settings).done(function (data) {
			if(data.length == 0){
	    		return;
			}
		  renderDate(data);
		});
  }

  function renderTable(data){
    var template_schedule = ``;
    var template_rates    = ``;
    var ajax_table_origin = `<i>from</i> `+data[0].voyage.port.origin;
    var ajax_table_dest   = `<i>to</i> `+data[0].voyage.port.destination;

    data.forEach(function(dat){
    	if(dat.accommodations.length > 0){
	    		var template_acc = '';
		    	dat.accommodations.forEach(function(content){
		    		var color = content.isAvailable == true ? 'color:green' : 'color:red';
	          template_acc += `<li style="`+ color+`">`+content.name+`  - `+content.price+`</li>`
		    	});
    	}else{
    		var template_acc = 'No Longer Available'
    	}

      template_schedule += `<tr>
							                <td>`+dat.voyage.vesselName+`</td>
							                <td>`+dat.voyage.departureDateTime+`</td>
							                <td>`+dat.voyage.duration+`</td>
							                <td>
							                  <ul style="padding: 0 1rem;">
							                     `+template_acc+`
							                  </ul>
							                </td>
							              </tr>`;
    });

    $('.ajax_schedules').html(template_schedule)
    $('.ajax_table_origin').html(ajax_table_origin)
    $('.ajax_table_dest').html(ajax_table_dest)
    $('.features').css({'opacity':1});
  }

  function renderDate(data){
  	var template_date = ``;
  	data.forEach(function(dat,index){
  		var arrDate   =  convertDate(dat.date);
  		var disabled = dat.isAvailable == false ? 'disabled' : '';
  		var active    = index == 0 ? 'active' : '';
  		template_date += `<div  class=""  data-slick-index="0" aria-hidden="false" tabindex="0">
                      		<div class="selected-date `+disabled+` `+active+`" date=`+dat.date+`>
                    			<span class="fa fa-ship ship-pos"></span>
                    			<div class=""> `+ arrDate[2]+` </div>
                    			<div class=""><em> `+arrDate[0]+` ,</em> `+arrDate[1]+` </div>
                  				</div>
                				</div>`;
  	});
  	$('.ajax_date').html(template_date);
  	init_slick();
  	init_date_selectors();
  	$('.features').css({'opacity':1});
  }

  function convertDate(date){
  	var formatDate = new Date(date);
  	var arrDate = formatDate.toDateString().split(' ');
  	return arrDate;
  }

  function getCurrentDate(){
  	var date = new Date();
		return date.toISOString().split('T')[0];
  }

  function refresh(){
  	var path = '/modules/custom/DataRouter/js/brkta.js';
  	return `<script src=`+path+`></script>`
  }

  function init_slick(){
  	if($('.ajax_date').hasClass('date-initialized') == true){
  		$('.selected-date-wrap').slick('unslick');
 		}
 		$('.ajax_date').addClass('date-initialized');

  	$('.selected-date-wrap').slick({
            arrows: true,
            dots: false,
            infinite: false,
            slidesToShow: 10,
            slidesToScroll: 3,
            pauseOnFocus: false,
            autoplaySpeed: 1500,
            prevArrow: '<div class="slick-prev"><i class="fa fa-angle-left" aria-hidden="true"></i></div>',
		   		  nextArrow: '<div class="slick-next"><i class="fa fa-angle-right" aria-hidden="true"></i></div>',
            responsive: [
                {
                    breakpoint: 800,
                    settings: {
                        slidesToShow: 5,
                        slidesToScroll: 2,
                        arrows: false,
                    }
                }
            ]
        });
  }

  function init_date_selectors(){
  	 // select date
	  $('.selected-date').click(function(){
	  	if($(this).hasClass('disabled')){
	  		$('.ajax-message').show();
	  		return;
	  	}
	  	$('.features').css({'opacity':0.1})
	  	$('.selected-date').removeClass('active');
	  	$(this).addClass('active');

	  	var date   = $(this).attr('date');
	  	var origin = $('.select-origin').find(":selected").val();
			var dest   = $('.select-dest').find(":selected").val();
			$('.ajax-message').hide();
			getID(origin,dest,date);
	  })
 	}

 	function main_init(){
 		if($('.ajax_date').hasClass('date-initialized')){
 			return;
 		}

 	  var currentDate = new Date()
	  var date = currentDate.toISOString().split('T')[0]
		  
  	var origin = $('.select-origin').find(":selected").val();
	  var dest   = $('.select-dest').find(":selected").val();
	  getID(origin,dest,date);
	  getDateRange(origin,dest,date);
 	}

}(jQuery));

