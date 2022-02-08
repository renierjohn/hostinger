(function($) {
  "use strict";
  
  // addBGimage();
  // main_init();
initWeather();

  // function getWeatherData(data){
  // 	// var lat = 
  // 	// var lng =

		// var settings = {
		//   "url": "https://api.openweathermap.org/data/2.5/onecall?lat="+lat+"&lon="+lng+"&exclude=alerts,hourly,minutely&appid=61db22cd24465140efa9d76bce707bc9",
		//   "method": "GET",
		//   "timeout": 0,
		//   "headers": {
		//     "Content-Type": "application/json"
		//   }
		// };

		// $.ajax(settings).done(function (data) {
		// 	if(data.length == 0){
	 //    		return;
		// 	}
		//   render(data);
		// });
  // }





///////////////////////////////////// ///////////////////////////////////// ///////////////////////////////////// 

 	// function requestWeatherPlaceID(place){
 	// 	$('.weather').html('')
		// var token = '03AGdBq27r2uV35Anr7eydvz9BUpsR7Z2ihXW8S6EgN3yVlsWzOnXkNjlNoN9anjjj-czIEzUKbsIgrXS-jFBzNa6dxlYgMjeQKaVoGCOUHhikq5Nm11gDlmf26EgrIs01LB9ps9m_e-WWkIBqFpGTsG0nV3BlDfKYRalAtGXKtADvKcrRN0kMXsb2LmgBLFOarZD9_7-pJ5MG1pTdAMYm7LOlbfHhwU8Y3noiWPAkQDRoia7Jbh5aKdngTLK8EtmhIIXJ-kiIt0OSG1gB_P5XkmNOdJ0IOwTaX54UIhdm0pCoq60_Rp8NyMcKDE73LTipvruAIBEckTHsW0ByRwfRZQB1XYI2JxcajYWuyBUwb6pvawaF5DzEDT0GnsYHo-jEv1iWNCu3FyrILCTboaE5VwPKjX6tw5-JOOw670sEMhgNWd90KQN7Btd7G2S8cMbn_9b37_kIj5nJN5hicWJF6CIyURw6iIQdMA';
		// var url_id = 'https://forecast7.com/api/autocomplete/'+place+',%20Philippines/?token='+token;
 	// 	var settings = {
		//   "url": url_id,
		//   "method": "GET",
		// };

		// $.ajax(settings).done(function (data) {
		// 	if(data.length == 0){
	 //    		return;
		// 	}
		//   requestURLid(data);
		// });

 	// }

 	// function requestURLid(data){
 	// 	var token = '03AGdBq27r2uV35Anr7eydvz9BUpsR7Z2ihXW8S6EgN3yVlsWzOnXkNjlNoN9anjjj-czIEzUKbsIgrXS-jFBzNa6dxlYgMjeQKaVoGCOUHhikq5Nm11gDlmf26EgrIs01LB9ps9m_e-WWkIBqFpGTsG0nV3BlDfKYRalAtGXKtADvKcrRN0kMXsb2LmgBLFOarZD9_7-pJ5MG1pTdAMYm7LOlbfHhwU8Y3noiWPAkQDRoia7Jbh5aKdngTLK8EtmhIIXJ-kiIt0OSG1gB_P5XkmNOdJ0IOwTaX54UIhdm0pCoq60_Rp8NyMcKDE73LTipvruAIBEckTHsW0ByRwfRZQB1XYI2JxcajYWuyBUwb6pvawaF5DzEDT0GnsYHo-jEv1iWNCu3FyrILCTboaE5VwPKjX6tw5-JOOw670sEMhgNWd90KQN7Btd7G2S8cMbn_9b37_kIj5nJN5hicWJF6CIyURw6iIQdMA';
 	// 	var url 	= 'https://forecast7.com/api/getUrl/'+data.place_id+'?token='+token;
 		
 	// 	var settings = {
		//   "url": url,
		//   "method": "GET",
		// };
		// var resul = '';
		// $.ajax(settings).done(function (data) {
		// 	if(data.length == 0){
	 //    		return;
		// 	}
		//   result = data;
		// });

 	// 	var result;
 	// 	var html 	= '<a class="weatherwidget-io" href="https://forecast7.com/en/'+result+'/" data-label_1="CEBU CITY" data-label_2="WEATHER" data-font="Roboto" data-icons="Climacons Animated" data-days="7" data-theme="original" >Tagbilaran CITY WEATHER</a>'

 	// 	$('.weather').html('')
 	// 	$('.weather').html(html)
 	// 	$('body').append(`<script src='https://weatherwidget.io/js/widget.min.js'>
  //   </script>`)
 	// }

 ///////////////////////////////////// ///////////////////////////////////// ///////////////////////////////////// 
 	

 	// function main_init(){
 	// 	// if($('.ajax_date').hasClass('date-initialized')){
 	// 	// 	return;
 	// 	// }
 	// 	console.log('main init')

 	//   var currentDate = new Date()
	 //  var date = currentDate.toISOString().split('T')[0]
		  
  // 	// var origin = $('.select-origin').find(":selected").val();
	 //  // var dest   = $('.select-dest').find(":selected").html();

	 //  $('.select-dest').change(function(){
		//   	var date   = $('.selected-date.active').attr('date');
		//     var origin = $('.select-origin').find(":selected").val();
		//     var dest   = $('.select-dest').find(":selected").html();
	 //  		// console.log(dest)
	 // 		 requestWeatherPlaceID(dest)
  // 	})

 	// }



 	 $('.select-dest').change(function(){
    	var lat = $('.select-dest').find(":selected").attr('lat');
    	var lng = $('.select-dest').find(":selected").attr('lng');
  	  requestWeather(lat,lng);
  })



  function initWeather(){
  		var lat = $('.select-dest').find(":selected").attr('lat');
  		var lng = $('.select-dest').find(":selected").attr('lng');
  		requestWeather(lat,lng);
  }

  function requestWeather(lat,lng){
  	var settings = {
		  "url": "https://api.openweathermap.org/data/2.5/onecall?lat="+lat+"&lon="+lng+"&exclude=alerts,hourly,minutely&appid=61db22cd24465140efa9d76bce707bc9",
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
  		data.daily.forEach(function(dat,index){
			                  // <div class="">`+dat.weather[0].main + `</div>
  			var active = index == 0 ?  'active' : '';
  			template +=`<div class="" style="width: 150px;">
			                <div class="selected-weather `+active+`"style="background-image: url('/themes/Renify/images/weather/`+dat.weather[0].main+`.gif');background-repeat: no-repeat;background-size: cover;color: white;">
			                  <div class="">`+(Number(Number(dat.temp.max) - 273 )).toFixed(1) +`&degC</div>
			                  <div class="">`+dat.uvi+ ` UV</div>
			                  <div class="">`+dat.humidity+ `% humidity</div>
			                  <div class="">`+ convertDate(dat.dt) + `</div>
			                </div>
					          </div>`
  		});
  		$('.selected-weather-wrap').html('')
  		$('.selected-weather-wrap').html(template);
  }

  function convertDate(tstamp){
  	var theDate = new Date(tstamp * 1000);
		return theDate.toGMTString().slice(0,17);
  }
}(jQuery));

