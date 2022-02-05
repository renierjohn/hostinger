(function($) {
  "use strict";
  init();
  
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

  function getID(route){
    var id = $("input[name='"+route+"']").val()
    if(id !== undefined){
      var url = 'https://w3.cokaliongshipping.com/csl/api.html?request=schedule&route='+id;
      $.ajax({
        'url' : url ,
        'type': 'GET',
        'success' : function(data) {
          render(data)
        }
      });
    }else{
      $('.ajax-message').show();
      $('.features').css({'opacity':1});
    }
  }

  function render(data){
    var template_schedule = ``;
    var template_rates    = ``;
    data.schedules.forEach(function(dat){
      template_schedule += `<tr><td>`+dat.vessel+`</td>`+
                           `<td>`+dat.depature+`</td>`+
                           `<td>`+dat.arrival+`</td></tr>`
    });
    
    data.rates.forEach(function(dat){
      template_rates += `<tr><td>`+dat.vessel+`</td>`+
                           `<td>`+dat.accomodation+`</td>`+
                           `<td>`+dat.rooms+`</td>`+
                           `<td>`+dat.rate+`</td></tr>`
    });

    $('.ajax_schedules').html(template_schedule)
    $('.ajax_rates').html(template_rates)
    $('.features').css({'opacity':1});
  }

  function init(){
  	  var origin = $('.select-origin').find(":selected").text();
	  var dest   = $('.select-dest').find(":selected").text();
	  getID(origin+' '+dest);
  }
}(jQuery));
