@php
  $space_info_right_pannel = objectToArray(Session::get('space_info'));
@endphp

<div id="tour3" class="members community-tile feed-tile pull-right">
    <a class="community_member_link community-unlocked-column" href="{{env('APP_URL')}}/community_members/{{$data->id}}"></a>
    <span class="tile-heading pull-left">Community</span>
    <div class="community-unlocked-column community-invite-col hidden">
      <div class="text-center community-center-content">
        <span class="member-text full-width community-members-count"></span>
        <div class="community-member full-width">
          <ul>
            <li></li>
          </ul>
        </div>
        @if(!$space_info_right_pannel['invite_permission'] || $space_info_right_pannel['space_user'][0]['user_type_id'] == Config::get('constants.USER_ROLE_ID'))
            @if($space_info_right_pannel['space_user'][0]['user_type_id'] == Config::get('constants.USER_ROLE_ID'))
        <a href="{{env('APP_URL')}}/setting/{{$data->id}}#pending-invites-tab">
          <span class="member-text full-width community-pending-members-count"></span>
        </a>
        @endif
        <span class="community-invite full-width"><a class="btn btn-primary invite-btn" href="javascript:void(0)" data-toggle="modal" data-target="#myModalInvite">INVITE</a></span>
        @endif
        <span class="view-member full-width"><a href="{{env('APP_URL')}}/community_members/{{$data->id}}">View all members</a></span>
      </div>
    </div>

    <div class="community-locked-box text-center community-locked-column hidden">
      <div class="community-locked-flex">
        <span class="lock-icon">
          <img src="{{url('/',[],$ssl)}}/images/ic_lock.svg" />
        </span>
        <p class="community-lock-paragraph">Please complete the set up tasks before inviting your community</p>
      </div>
    </div>
</div>