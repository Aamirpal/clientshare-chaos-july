<div class="box action">
    @php
      $space_id = Session::get('space_info')['id'];
      $user_id = Session::get('space_info')['space_user'][0]['user_id'];
      $check_buyer =  app('App\Http\Controllers\FeedbackController')->checkBuyer($space_id,$user_id);
    @endphp
    @if($check_buyer == Config::get('constants.USER.role_tag.buyer'))
    @if((Session::get('space_info')['allow_buyer_post']) || Session::get('space_info')['space_user'][0]['user_type_id'] == Config::get('constants.USER_ROLE_ID'))
      <span class="top_post_add_link">Add a Post</span>
      @else
      <p>No Posts this month</p>
    @endif
    @else
    @if((Session::get('space_info')['allow_seller_post']) || Session::get('space_info')['space_user'][0]['user_type_id'] == Config::get('constants.USER_ROLE_ID'))
      <span class="top_post_add_link">Add a Post</span>
      @else
      <p>No Posts this month</p>
    @endif
    @endif
</div>