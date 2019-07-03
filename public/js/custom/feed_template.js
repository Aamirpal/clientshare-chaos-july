$(document).ready(function(){
  $.ajax({
    type: 'GET',
    dataType: 'JSON',
    url: baseurl+'/posts?space_id='+session_space_id+'&limit=3',
    success: function(response) {
      $('#load_more').before($("#post_template").tmpl(response) );
    }
  });
});