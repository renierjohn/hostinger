(function($) {
"use strict";
	
	$(document).ready(function() {
			$('.fb--login').hide();
  		$('.fb--logout').show();
	
	    FB.init({
	      appId: '376061863589278',
	      version: 'v13.0'
	    });     
    	FB.getLoginStatus(updateStatusCallback);
  });

	$('.fb-login-btn').click(function(){
		FB.login();
		FB.getLoginStatus(updateStatusCallback);
	})

  function updateStatusCallback(data){
  	if(data.status == 'connected'){
  		$('.fb--login').show();
  		$('.fb--logout').hide();

  		FB.api(
			  '/renify.official/feed',
			  'GET',
			  {"fields":"message,full_picture,shares,likes,comments,from{username,picture{cache_key,height,width,url}},to{username,picture{cache_key,height,width,url}}","limit":"4"},
			  function(response) {
			      render(response)
			  }
			);
  	}
  	else{
  		$('.fb--login').hide();
  		$('.fb--logout').show();
  	}
  }

  function render(response){
  	console.log(response)
  	if(response){
  		var template = ``;

  		response.data.forEach(function(data){
  			var user = data.to != null ? data.to.data[0] : data.from;
  			template += `
		  			<div class="card">
		            <div>
		                <img src="`+data.full_picture+`">
		                <p>`+data.message+`</p>
		            </div>
		            <div class="fb-user">
		              <img src="`+user.picture.data.url+`" alt="image">
		              <h6>`+user.username+`</h6>
		            </div>
		          </div>`;

  		});
			$('.fb-div').append(template);
  	}
  }

}(jQuery));
