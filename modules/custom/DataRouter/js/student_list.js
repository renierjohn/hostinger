(function ($,Drupal,drupalSettings) {
    firebase.initializeApp(drupalSettings.firebase);
    var firestore = firebase.firestore();
    var db        = firebase.database();
    var date      = getCurrentDate();

  // $('.s-js-select-attendance').on('change', function() {
  //   var level = $('.s-js-select-level').val();

  //   $('[s-level]').parent().parent().hide();
  //   if(this.value == 'all'){
  //     if(level != 'all'){
  //       $('.s-present').parent().find('[s-level='+level+']').parent().parent().show();
  //       $('.s-not-present').parent().find('[s-level='+level+']').parent().parent().show();
  //     }
  //     else{
  //       $('.s-present').parent().show();
  //       $('.s-not-present').parent().show(); 
  //     }

  //   }
  //   if(this.value == '1'){
  //     if(level == 'all'){
  //       $('.s-present').parent().show();
  //       $('.s-not-present').parent().hide(); 
  //     }
  //     else{
  //       $('.s-present').parent().find('[s-level='+level+']').parent().parent().show();
  //       $('.s-not-present').parent().find('[s-level='+level+']').parent().parent().hide();
  //     }
  //   }
  //   if(this.value == '0'){
  //     if(level == 'all'){
  //       $('.s-present').parent().hide();
  //       $('.s-not-present').parent().show();
  //     }
  //     else{
  //      $('.s-present').parent().find('[s-level='+level+']').parent().parent().hide();
  //      $('.s-not-present').parent().find('[s-level='+level+']').parent().parent().show(); 
  //     }
  //   }
  // });


  // $('.s-js-select-level').on('change', function() {
  //   var att = $('.s-js-select-attendance').val();
    
  //   if($('[s-level='+this.value+']').length > 0 && att == 'all'){
  //     $('[s-level]').parent().parent().hide();
  //     $('[s-level='+this.value+']').parent().parent().show();
  //   }

  //   if($('[s-level='+this.value+']').length > 0 && att == '1'){
  //     $('[s-level]').parent().parent().hide();
  //     $('[s-level='+this.value+']').parent().parent().find('.s-present').parent().show();
  //   }

  //   if($('[s-level='+this.value+']').length > 0 && att == '0'){
  //     $('[s-level]').parent().parent().hide();
  //     $('[s-level='+this.value+']').parent().parent().find('.s-not-present').parent().show();
  //   }

  //   if(this.value == 'all' && att == '1'){
  //     $('.s-present').parent().show(); 
  //   }
    
  //   if(this.value == 'all' && att == '0'){
  //     $('.s-not-present').parent().show();
  //   }

  //   if(this.value == 'all' && att == 'all'){
  //     $('.s-not-present').parent().show();
  //     $('.s-present').parent().show(); 
  //   }

  //   if($('[s-level='+this.value+']:visible').length == 0 && this.value != 'all'){
  //     alert('No Result Level'+this.value)
  //   }

  // });

  // $('[s-level]').each(function(){
  //     var level = $(this).attr('s-level');
  //     if($(".s-js-select-level:has(option[value='"+level+"'])").length == 0){
        
  //       $('.s-js-select-level').append('<option value="'+level+'">'+level+'</option>');
  //       $('.summary-list').append(
  //         `<div class="col-block">
  //                <h3 class="center">Grade `+level+`</h3>
  //                 <div class="block-1-2">
  //                   <div class="col-block boys">
  //                     <h5>BOYS</h5>
  //                     <p>12</p>
  //                   </div>
  //                   <div class="col-block girls">
  //                     <h5>Girls</h5>
  //                     <p>15</p>
  //                   </div>
  //                 </div>
  //              </div>`
  //         );
  //     }
  // });
    db.ref('/students/'+date).on('value', (snapshot) => {
        snapshot.forEach((childSnapshot) => {
          var childKey  = childSnapshot.key;
          var childData = childSnapshot.val();
          console.log(childKey);
          console.log(childData);
          setRealTimeRender(childData);
        });
    });

    $('.s-js-select-attendance').on('change', function() {
       $('.s-more').attr('start',0);
       request(true);
    });

    $('.s-js-select-level').on('change', function() {
       $('.s-more').attr('start',0);
       request(true);
    });

    $('.s-js-select-gender').on('change', function() {
       $('.s-more').attr('start',0);
       request(true);
    });

    $('.s-more').click(function(){
        request(false);
    });

    function request(dom_state){
       var state = $('.s-js-select-attendance').val();
       var level = $('.s-js-select-level').val();
       var gender= $('.s-js-select-gender').val();
       var limit = $('.s-more').attr('limit');
       var start = parseInt($('.s-more').attr('start'));

       var params = `/api/student/list/?l=${limit}&s=${start}`;
       
       if(level != 'all'){
        params = params + `&lvl=${level}`;
       }

       if(gender != 'all'){
        params = params + `&g=${gender}`;
       }

       if(state != 'all'){
        params = params + `&p=${state}`;
       }

        var start = parseInt(limit) + parseInt($('.s-more').attr('start'));
        $('.s-more').attr('start',start);

        $.ajax({
            'url' : params,
            'type': 'GET',
            'success' : function(data) {
              appendList(data,dom_state)
            }
        })
    }

    function appendList(data,dom_state){
      console.log(dom_state)
      dom = '';
      data.more_flag == true ? $('.s-more').show() : $('.s-more').hide(); 
      data.students.forEach(function(student){
        var state_class = student.flag == true ? 's-present' : 's-not-present';
        dom += `
          <div class="col-block" s-hash="${student.hash}" >
                <div class="s-image-wrapper ${state_class}"> 
                  <div class="s-image">
                      <img src="${student.image}" alt="">
                  </div>
                  <div class="s-image s-image-hash">

                  </div>
                </div>
                <div class="s-data-wrapper">
                     <h3>Name : ${student.name}</h3>
                     <h3 s-gender="${student.gender}" s-level="${student.level}">Level : ${student.level}</h3>
                </div>
              </div>
        `;
      });

      if(dom_state == true){
        $('.student-list').html(dom); 
      }
      else{
        $('.student-list').append(dom);
      }
      renderQr();
    }

    function renderQr(){
      $('[s-hash]').each(function(index,value){
        var hash = $(this).attr('s-hash')
        var hash_id = "s-image-hash-"+index;
        var qr_image = $(this).find('.s-image-hash');
        qr_image.html(' ');
        new QRCode(qr_image[0],hash)
      });
    }

    function setRealTimeRender(childData){
      var hash = childData.hash;
      console.log(hash);
      $(`[s-hash=${hash}]`).find('.s-image-wrapper').removeClass('s-not-present');
      $(`[s-hash=${hash}]`).find('.s-image-wrapper').addClass('s-present');
      $(`[s-hash=${hash}]`).find('.s-image-wrapper').fadeOut('fast');
      $(`[s-hash=${hash}]`).find('.s-image-wrapper').fadeIn('fast');
    }

    function getCurrentDate(){
      var date = new Date();
      return String(date.getMonth()+1) + '-' + String(date.getDate()) + '-' + String(date.getFullYear());
    }

    renderQr();

})(jQuery,Drupal,drupalSettings);