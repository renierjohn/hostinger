(function ($,Drupal,drupalSettings) {
    console.log(drupalSettings);
    var video           = document.createElement("video");
    var canvasElement   = document.getElementById("canvas");
    var canvas          = canvasElement.getContext("2d");
    var detect          = document.getElementsByTagName('input')
    
    ///////////////////////////////////////////
    // FIREBASE
    //////////////////////////////////////////
    firebase.initializeApp(drupalSettings.firebase);
    var firestore = firebase.firestore();
    var db        = firebase.database();
    var date      = getCurrentDate();
    // var ref  = firestore.collection('students').doc('elementary').collection(date);

    // ref.onSnapshot((doc) => {
    //       doc.docs.forEach((data)=>{
    //         addStudentListInit(data.data());
    //       })
    // });

    // ONCE ONLY FETCH
    //  db.ref('/students/'+date).get().then((snapshot) => {
    //     if (!snapshot.exists()) {
    //       alert('NO INITIAL DATA');
    //       return; 
    //     }
    //     snapshot.forEach((childSnapshot) => {
    //       var childKey  = childSnapshot.key;
    //       var childData = childSnapshot.val();
    //       console.log(childKey);
    //       console.log(childData);
    //       addStudentListInit(childData);
    //     });
    // });

    // REALTIME FETCH
    db.ref('/students/'+date).on('value', (snapshot) => {
        snapshot.forEach((childSnapshot) => {
          var childKey  = childSnapshot.key;
          var childData = childSnapshot.val();
          console.log(childKey);
          console.log(childData);
          addStudentListInit(childData);
        });
    });
   
    //////////////////////////////////////////////


    ///////////////////////////////////////////
    // BUTTONS
    //////////////////////////////////////////

    $('.qr_hash_generate').keyup(function(e){
      if(e.keyCode == 13){$(this).trigger("enterKey");}
    });

    $('.qr_hash_generate').bind("enterKey",function(e){
        var hash = $(this).val()
        $('#qr_image_result').html(' '); 
        new QRCode(document.getElementById('qr_image_result'),hash);
        $('.camera').show();
    });

    $('.qr_hash').keyup(function(e){
      if(e.keyCode == 13){$(this).trigger("enterKey");}
    });

    $('.qr_hash').bind("enterKey",function(e){
        var hash = $(this).val() 
        requestUSer(hash)
    });

    $('.qr_reset').click(function(e){
        fetch('/api/student/delete');
        db.ref('/students').child(date).remove();
        $('.student-lists').hide();
    });
    //////////////////////////////////////////////


    //////////////////////////////////////////////////////////////
    //
    //  CAMERA 
    //
    //////////////////////////////////////////////////////////////
    $('.qr_camera').click(function(){
      $('.camera').show();
      $('#qr_image_result').html('' );
      startWebcam();
    })

    function startWebcam(){
        detect[0].value = '1';
        navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } }).then(function(stream) {
          video.srcObject = stream;
          video.setAttribute("playsinline", true); // required to tell iOS safari we don't want fullscreen
          video.play();
          requestAnimationFrame(render);
          $('.qr_stop').click(function(){
              stream.getVideoTracks().forEach(function(track) {
                  track.stop();
                  detect[0].value = '0';
                  // renderModal();
                  // $('.qr_image').html(' ');
                  $('.modal').remove();
              });
          });
        });
    }

    function drawLine(begin, end, color) {
      canvas.beginPath();
      canvas.moveTo(begin.x, begin.y);
      canvas.lineTo(end.x, end.y);
      canvas.lineWidth = 4;
      canvas.strokeStyle = color;
      canvas.stroke();
    }

    function render() {
      // loadingMessage.innerText = "⌛ Loading video..."
      if (video.readyState === video.HAVE_ENOUGH_DATA) {
          canvasElement.hidden   = false;
          canvasElement.height = 200;
          canvasElement.width  = 200;

        canvas.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
        
        var imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);
        var code      = jsQR(imageData.data, imageData.width, imageData.height, {
          inversionAttempts: "dontInvert",
        });

        if (code) {
          drawLine(code.location.topLeftCorner, code.location.topRightCorner,       "#FF3B58");
          drawLine(code.location.topRightCorner, code.location.bottomRightCorner,   "#FF3B58");
          drawLine(code.location.bottomRightCorner, code.location.bottomLeftCorner, "#FF3B58");
          drawLine(code.location.bottomLeftCorner, code.location.topLeftCorner,     "#FF3B58");

          $('#qr_image_result').html(' ');
          new QRCode(document.getElementById('qr_image_result'),code.data);

          canvasElement.height = 0;
          canvasElement.width  = 0;
          canvasElement.hidden = true;
          video.hidden = true;
          detect[0].value = '0';
          
          requestUSer(code.data);
        } 
      }

      if(detect[0].value == '1'){
        requestAnimationFrame(render);
      }
    }

    //////////////////////////////////////////////////////////////
    


    //////////////////////////////////////////////////////////////
    //
    //  Utils 
    //
    //////////////////////////////////////////////////////////////
    // function download(){
    //   var qr = $('#qr_image').find('img').attr('src')
    //   var a  = document.createElement('a');
    //   a.href = qr;
    //   a.download = "my_qrcode.png";
    //   a.click();
    // }

    function getCurrentDate(){
      var date = new Date();
      return String(date.getMonth()+1) + '-' + String(date.getDate()) + '-' + String(date.getFullYear());
    }

    function requestUSer(hash){
      var request = true; 
      
      $('.qr_hash_list').each(function(){
       var hash_list = $(this).val();
        if(hash_list == hash && hash.length > 0){
          request = false;
        }
      })

      if(request == false){
        alert('already scanned')
        return;
      }

      $.ajax({
        'url' : '/api/student/?qr='+hash ,
        'type': 'GET',
        'success' : function(data) {
          if(data.status == false){
            alert('NO DATA');
            return;
          }
          console.log(data);
          saveToRealTimeDatabase(data);
        }
      });
    }

    function saveToFireStore(data){
      var unique_id = data.data.hash
      ref.doc(unique_id).set({
          hash  : unique_id,
          image : data.data.image,
          name  : data.data.name,
          ts    : data.data.ts,
          gender: data.data.gender,
          level : data.data.level,
          hour  : data.data.hour,
          minute: data.data.minute,
          ampm  : data.data.ampm,
      })
    }

    function saveToRealTimeDatabase(data) {
       var unique_id = data.data.hash
        // db.ref('students/'+ date+'/'+ unique_id).set({
         db.ref('students/'+ date).push({ 
            hash  : unique_id,
            image : data.data.image,
            name  : data.data.name,
            ts    : data.data.ts,
            gender: data.data.gender,
            level : data.data.level,
            hour  : data.data.hour,
            minute: data.data.minute,
            ampm  : data.data.ampm,
        });
    }

    function addStudentListInit(data){
      var hash   = data.hash;
      var render = true;
      $('.qr_hash_list').each(function(){
       var hash_list = $(this).val();
        if(hash_list == hash && hash.length > 0){
          render = false;
        }
      })

      if(render == false){
        console.log('already render'+data.name);
        return;
      }

        template = `<div class="img-wrapper" style="display:none;">
                      <div class="block-1-2">
                        <div class="col-block">
                          <input type="hidden" class="qr_hash_list"  name="qr_hash" value=`+data.hash+`>
                          <img src="`+data.image+`" alt="">
                        </div>
                        <div class="col-block">
                          <div class="row">
                            <p>`+data.name+`</p>
                          </div>
                          <div class="row">
                            <p>Grade `+data.level+`</p>
                          </div>
                          <div class="row">
                            <p>`+data.ts+`</p>
                          </div>
                        </div>
                      </div>
                    </div>  `

        $('.student-lists').prepend(template)
        $('.img-wrapper').show('fast')
    }

})(jQuery,Drupal,drupalSettings);