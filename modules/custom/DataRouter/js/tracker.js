(function ($, Drupal) {
  var url = '/api/count/views?t=';
  var query = '';
  $('[id="view_count"]').each(function(index,item){
    if(index == 0){
      query =  $(this).attr('class');
    }
    if(index > 0){
      query = query +','+ $(this).attr('class');
    }
  })
  url = url+query;
  $.ajax(url).then(function(data){
   $('[id="view_count"]').each(function(index,item){
    var id = $(this).attr('class');
      $(this).html(data[id]);
    });
  });
})(jQuery, Drupal);
