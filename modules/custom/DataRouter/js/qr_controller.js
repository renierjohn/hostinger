(function ($) {
    var video           = document.createElement("video");
    var canvasElement   = document.getElementById("canvas");
    var canvas          = canvasElement.getContext("2d");
    var loadingMessage  = document.getElementById("loadingMessage");
    var outputContainer = document.getElementById("output");
    var outputMessage   = document.getElementById("outputMessage");
    var outputData      = document.getElementById("outputData");
    var detect          = document.getElementsByTagName('input')
   
    $('#qr_download').hide();
    $('#qr_download').click(function(){
      download();
    });

    $('.qr_submit').click(function(){
      var hash    =  $('.qr_hash').val();
      var request = true; 
      console.log(hash)

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
          console.log(data);
          if(data.status == false){
            alert('NO DATA');
            return;
          }
          addStudentList(data);
        }
      });
    });

    function addStudentList(data){
      template = `<div class="img-wrapper" style="display:none;">
                    <div class="block-1-2">
                      <div class="col-block">
                        <input type="hidden" class="qr_hash_list"  name="qr_hash" value=`+data.data.hash+`>
                        <img src="`+data.data.image+`" alt="">
                      </div>
                      <div class="col-block">
                        <div class="row">
                          <p>`+data.data.name+`</p>
                        </div>
                        <div class="row">
                          <p>Grade 9</p>
                        </div>
                        <div class="row">
                          <p>`+data.data.ts+`</p>
                        </div>
                      </div>
                    </div>
                  </div>  `

      $('.student-lists').prepend(template)
      $('.img-wrapper').show('fast')
    }
    $('.qr_camera').click(function(){
      startWebcam();
    })

    function drawLine(begin, end, color) {
      canvas.beginPath();
      canvas.moveTo(begin.x, begin.y);
      canvas.lineTo(end.x, end.y);
      canvas.lineWidth = 4;
      canvas.strokeStyle = color;
      canvas.stroke();
    }

    function startWebcam(){
        // Use facingMode: environment to attemt to get the front camera on phones
        detect[0].value = '1';
        navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } }).then(function(stream) {
          video.srcObject = stream;
          video.setAttribute("playsinline", true); // required to tell iOS safari we don't want fullscreen
          video.play();
          requestAnimationFrame(render);
          // document.getElementById('close').addEventListener('click', function () {});
        });
    }

    function render() {
      loadingMessage.innerText = "⌛ Loading video..."
      if (video.readyState === video.HAVE_ENOUGH_DATA) {
        loadingMessage.hidden = true;
        canvasElement.hidden = false;
        outputContainer.hidden = false;

        canvasElement.height = video.videoHeight;
        canvasElement.width  = video.videoWidth;
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
          outputMessage.hidden            = true;
          outputData.parentElement.hidden = false;
          outputData.innerText            = code.data;

          new QRCode(document.getElementById("qr_image"),code.data);
          
          canvasElement.height = 0;
          canvasElement.width  = 0;
          canvasElement.hidden = true;
          video.hidden = true;
          detect[0].value = '0';
          $('#qr_download').show();
          $('.qr_hash').val(code.data);
          $('.modal').remove();
        } else {
          outputMessage.hidden            = false;
          outputData.parentElement.hidden = true;
        }
      }

      if(detect[0].value == '1'){
        requestAnimationFrame(render);
      }
    }

    function download(){
      var qr = $('#qr_image').find('img').attr('src')
      var a  = document.createElement('a');
      a.href = qr;
      a.download = "my_qrcode.png";
      a.click();
    }


})(jQuery);