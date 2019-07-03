$(document).ready(function(){
  setTimeout(loadCommunity, 1000);
});


function loadCommunity() {
 $.ajax({
    type: 'get',
    url: community_member+"?space_id="+current_space_id,
    success: function (response) {
      if(response.result){
        $('div.members.community-tile').find('span.community-members-count').html('<span>'+response.total_member+'</span> Members');
        $('div.members.community-tile').find('span.community-pending-members-count').html('<span>'+response.total_pending_invitations+'</span> Pending');
        profile_list_html = '';
        $.each(response.community_members, function(index, member){
          if(member.profile_image != '' && typeof member.profile_image != 'undefined' &&  !(member.profile_image.indexOf('default-user-image') != -1)) {
              profile_list_html += '<li class="member-profile"><a href="#"><span class="lazy-asset" data-lazy-asset="'+ member.profile_image +'"></span></a></li>';
             }
        });
        $('div.members.community-tile').find('div.community-member ul').html(profile_list_html);
      }
      else
      {
        $('div.members.community-tile').find('div.community-member ul').html('<li></li>');
      }
       return false;
    },
    error   : function ( response )
    {
      console.log('Something went wrong.');
    },
  });
}