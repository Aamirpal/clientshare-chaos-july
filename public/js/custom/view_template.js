var space_category;

$(document).ready(function(){
  getFeed();
  setTimeout(function() {
      var unpin_post = $('.unpin_post').length;
      if(unpin_post == 4){
        $('.pin_post').remove();
      }
  }, 5000);
  $(document).on('click','.edit-post-dropdown .delete_post',function(){
    var post_id = $(this).attr('post_id');
    $('.delete_posted').attr('href',baseurl+'/delete_post/'+post_id); 
  });
});