(function($) {
  // "use strict";
  // init_slick();
  // return
  main_init();
  init_date_selectors();

  // select origin
  $('.select-origin').change(function(){
  	var date   = $('.selected-date.active').attr('date');
    var origin = $('.select-origin').find(":selected").val();
    var dest   = $('.select-dest').find(":selected").val();
 
    $('#edit-origin').val($('.select-origin').find(":selected").html());
    $('#edit-destination').val($('.select-dest').find(":selected").html());
    
    if(origin == dest){
    	return;
    }
  	$('.features').css({'opacity':0.1})
    $('.ajax-message').hide();
    getID(origin,dest,date);
    getDateRange(origin,dest,getCurrentDate());
	  updateQuery(origin,dest,getCurrentDate());
    initBookingButton();
  })

  // select destination
  $('.select-dest').change(function(){
  	var date   = $('.selected-date.active').attr('date');
    var origin = $('.select-origin').find(":selected").val();
    var dest   = $('.select-dest').find(":selected").val();
    $('.ajax-message').hide();

    $('#edit-origin').val($('.select-origin').find(":selected").html());
    $('#edit-destination').val($('.select-dest').find(":selected").html());

    if(origin == dest){
    	return;
    }
  	$('.features').css({'opacity':0.1})
    getID(origin,dest,date);
    getDateRange(origin,dest,getCurrentDate());
    updateQuery(origin,dest,getCurrentDate());
    initBookingButton();
  })

  function getID(origin,dest,date){
    var uuid = $("input[name='uuid']").val()
    var url  = "https://renifysite.com/api/vessel/";
		var uri = `${url}?o=${origin}&d=${dest}&date=${date}&uuid=${uuid}`;
		var data =  fetch(uri).then(function(data){
			  return data.json()
			}).then(function(data){
				if(data.length == 0){
					$('.ajax-message').show();
  				return;
  			}
				renderTable(data);
			})
  }

  function getDateRange(origin,dest,date){
  	var uuid = $("input[name='uuid']").val()
  	var url  ='https://vessel.renier.workers.dev/dates';
		var uri = `${url}?o=${origin}&d=${dest}&date=${date}&uuid=${uuid}`;
		var data =  fetch(uri).then(function(data){
			  return data.json()
			}).then(function(data){
					if(data.length == 0){
	    		return;
			}
				renderDate(data);
			  return data;
			})
  }

  function renderTable(data){
    var template_schedule = ``;
    var template_rates    = ``;
    var ajax_table_origin = `<i>from</i><br>`+data[0].voyage.port.origin;
    var ajax_table_dest   = `<i>to</i><br>`+data[0].voyage.port.destination;
    var hashData          = $('[data-drupal-selector="edit-hash"]').attr('hash');
    
    data.forEach(function(dat){
    	if(dat.accommodations.length > 0){
	    		var template_acc = '';
		    	dat.accommodations.forEach(function(content){
            var hashed = $.md5(content.price+hashData)
		    		var color = content.isAvailable == true ? 'color:green' : 'color:red';
	          template_acc +=`<li style="${color}" class="js-data">
                                <a href="#" 
                                  js-data-acc   = "${content.name}"
                                  js-data-hash  = "${hashed}"
                                  js-data-price = "${content.price}" 
                                  js-data-date  = "${dat.voyage.departureDateTime}"
                                  js-data-name  = "${dat.voyage.vesselName}"
                                  style="color:green;">
                                      ${content.name} - ${content.price}
                                </a>
                            </li>`
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
    initBookingButton();
  }

  function initBookingButton(){ // INIT PRICE BTN
    $('.js-data a').click(function(e){
      e.preventDefault();
      var origin        = $('.select-origin').find(":selected").html();
      var dest          = $('.select-dest').find(":selected").html();
      var accommodation = $(this).attr('js-data-acc');
      var price         = $(this).attr('js-data-price');
      var vesselName    = $(this).attr('js-data-name');
      var dateTime_     = $(this).attr('js-data-date');
      var price_hashed  = $(this).attr('js-data-hash');
      var hashData      = $('[data-drupal-selector="edit-hash"]').attr('hash');
      var gen_hashed    = $.md5(price+hashData);

      if(gen_hashed != price_hashed){
         Swal.fire({
            title:  `Oops... . Please Dont Change the Rate`,
            width: 600,
            padding: '3em',
            color: '#7066e0',
            icon:'error',
            confirmButtonText: 'Im not a hacker',
          })
         return;
      }

      $('[data-drupal-selector="edit-hash"]').val($.md5(price+hashData));
      console.log(origin,dest,accommodation,price,vesselName,setDateTimeForm(dateTime_),hashData);

      dateTime = setDateTimeForm(dateTime_);
      date     = dateTime[0];
      time     = dateTime[1];
      $('#edit-vessel').val(vesselName);
      $('#edit-origin').val(origin);
      $('#edit-destination').val(dest);
      $('#edit-accomodation').val(accommodation);
      $('#edit-datetime-date').val(date);
      $('#edit-datetime-time').val(time);
      $('[data-drupal-selector="edit-price"]').val(price);

      $('.js-data-price-total').html(`<i>(Php ${price}.00)</i>`);
      $('.js-data-summary').html(`
        <b>${vesselName}</b> <br> <i>${origin} -> <b>${dest}</b></i>  <br> ${dateTime_}
      `);
      
      alertSweet(vesselName,accommodation,dateTime_);
    })
  }

  function alertSweet(vesselName,accommodation,dateTime_){
    Swal.fire({
      title:  `You Selected <i><b>${vesselName}</b></i><br>
               ${accommodation} - ${dateTime_} <br>
               Please Proceed Booking`,
      width: 600,
      padding: '3em',
      color: '#7066e0',
      icon:'success',
      confirmButtonText: 'BOOK TICKET',
      // background: '#fff url(/themes/Renify/favicon.ico)',
      backdrop: `
        rgba(0,0,123,0.4)
        url("/images/nyan-cat.gif")
        left top
        no-repeat
      `
    }).then(function(){
       $('[for="tab3"]').click();
    })
  }

  function setDateTimeForm(dateTime){
    // yyyy-MM-dd // 
    // current MM/dd/yyyy //
    var date = dateTime.split(' ')[0];
    var d    = date.split('/');
    date     = `${d[2]}-${d[0]}-${d[1]}`;
    var time = dateTime.split(' ')[1];
    var ampm = dateTime.split(' ')[2];

    H = Number(time.split(':')[0]);
    M = Number(time.split(':')[1]);
    
    if(ampm == 'pm'){
      H += 12;
    }

    if(H<10){
      H = '0'+String(H);
    }

    if(M<10){
      M = '0'+String(M);
    }
    
    time = `${H}:${M}`;

   
    return [date,time];
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

	  	var date   = $(this).attr('date');
	  	var origin = $('.select-origin').find(":selected").val();
			var dest   = $('.select-dest').find(":selected").val();
			if(origin == dest){
    		return;
    	}
	  	$('.features').css({'opacity':0.1})
	  	$('.selected-date').removeClass('active');
	  	$(this).addClass('active');
			$('.ajax-message').hide();
			getID(origin,dest,date);
			updateQuery(origin,dest,date);
      initBookingButton();
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

	  origin =  getParameterByName('o')  != null ? getParameterByName('o') : origin;
	  dest   =  getParameterByName('d')  != null ? getParameterByName('d') : dest;
	  date   =  getParameterByName('dt') != null ? getParameterByName('dt') : date;


	  getID(origin,dest,date);
	  getDateRange(origin,dest,date);

	  $('.select-origin option[value="'+origin+'"]').attr('selected','selected');
	  $('.select-dest option[value="'+dest+'"]').attr('selected','selected');
 		
 	}


 	function updateQuery(o,d,dt){
 		   window.history.replaceState({
            path:''
        }, "", '?o='+o+'&d='+d+'&dt='+dt);
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
          appId: '395967462029410', //replace with your app ID
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
        // window.open('http://www.twitter.com/share?url='+u+'&t='+t,'sharer','toolbar=0,status=0,width=626,height=436');
        window.open('http://www.twitter.com/share?url='+u+'&t='+t,'sharer',`menubar=no,
             toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600`);
        return false;
    });

    $('.share-insta').on('click', function(e) {
        var u = location.href;
        var t = document.title;
        window.open("https://www.instagram.com/renify_renier", "_blank", "location=yes");
        return false;
    });



}(jQuery));

