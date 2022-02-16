(function($) {
  "use strict";
  
	initWeather();



 	 $('.select-dest').change(function(){
 	 	  $('.selected-weather').css({'opacity':0.1});
    	var lat = $('.select-dest').find(":selected").attr('lat');
    	var lng = $('.select-dest').find(":selected").attr('lng');
  	  if(lat == undefined){
  			var dest   = $('.select-dest').find(":selected").text();
  			geoCode(dest);
  		}else{
  			requestWeather(lat,lng);
  		}
  })



  function initWeather(){
  		var lat = $('.select-dest').find(":selected").attr('lat');
  		var lng = $('.select-dest').find(":selected").attr('lng');
  		if(lat == undefined){
  			var dest   = $('.select-dest').find(":selected").text();
  			geoCode(dest);
  		}else{
  			requestWeather(lat,lng);
  		}
  }

  function geoCode(place){
  	var settings = {
		  "url": "http://api.openweathermap.org/geo/1.0/direct?q="+place+",PH&appid=61db22cd24465140efa9d76bce707bc9",
		  "method": "GET"
		};
		$.ajax(settings).done(function (data) {
			if(data.length == 0){
	    		return;
			}
			console.log(data)
			var lat = data[0].lat
			var lng = data[0].lon
		  requestWeather(lat,lng);
		});
  }

  function requestWeather(lat,lng){
  	var settings = {
		  "url": "https://api.openweathermap.org/data/2.5/onecall?lat="+lat+"&lon="+lng+"&exclude=alerts,daily,minutely&appid=61db22cd24465140efa9d76bce707bc9",
		  "method": "GET"
		};

		$.ajax(settings).done(function (data) {
			if(data.length == 0){
	    		return;
			}
			console.log(data)
		  render_weather(data);
		});
  }

  function render_weather(data){
  		var template = '';
  		var dest = $('.select-dest').find(":selected").text();
  		template +=`<div style="color: white;width:20rem;text-align:center;padding-top:4rem;background-color:aliceblue">
			                  <div class="" style="font-size:1.5rem;">Youre Destination</div>
			                  <div class="" style="font-size:2.5rem;">`+dest+`</div>
			                  <div class="" style="font-size:1.5rem;">Weather</div>
			                 </div>`;

  		data.hourly.forEach(function(dat,index){
			                  // <div class="">`+dat.weather[0].main + `</div>
  			var active  = index == 0 ?  'active' : '';
				var dayName =	convertDate(dat.dt)[0];
				var mo   = convertDate(dat.dt)[1];
				var day  = convertDate(dat.dt)[2];
				var time = convertDate(dat.dt)[4];

				var hour = time.split(':')[0];
				var ampm = 'AM';
				if(hour > 12){
					hour = hour - 12;
					ampm = 'PM';
				}

				template +=`<div class="selected-weather `+active+`"style="background-image: url('/themes/Renify/images/weather/`+dat.weather[0].main+`.gif');background-repeat: no-repeat;background-size: cover;color: white;width:15rem">
			                  <div class="" style="font-size:1rem;">`+dat.weather[0].description.toUpperCase()+ `</div>
			                  <div class="">`+(Number(Number(dat.temp) - 273 )).toFixed(1) +`&degC</div>
			                  <div class="">`+ hour+` `+ampm+` </div>
			                  <div class=""> `+ day+` </div>
                    		<div class=""><em> `+dayName+` ,</em> `+mo+` </div>
			                </div>`
					          	          
  		});
 console.log(template);
  		
  		$('.selected-weather-wrap').html('')
  		$('.selected-weather-wrap').html(template);
  		$('.selected-weather').css({'opacity':1});
  }

  function convertDate(tstamp){
  	var theDate = new Date(tstamp * 1000);
		return theDate.toString().split(' ');
  }

}(jQuery));

