@php
   $current_month = date('n');
   $current_year =  date('Y');
   $feedback_status = Session::get('space_info')->feedback_status??false;
@endphp
<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-2">
   <ul class="nav navbar-nav main-navigation hidden-lg hidden-md hidden-sm">
      <li>
      </li>
      <li><a href="{{ route('post_files',['space_id'=>Session::get('space_info')->id]) }}">Files</a></li>
      <li><a href="{{env('APP_URL')}}/community_members/{{Session::get('space_info')->id}}">Community</a></li>
      @if($feedback_status)
      <li><a href="{{env('APP_URL')}}/feedback/{{$current_month}}/{{$current_year}}/{{Session::get('space_info')->id}}">Feedback</a></li>
      @endif
      
      @if(Session::get('space_info')->reports_count)
      <li><a href="{{ url('/power_reports/'.Session::get('space_info')->id,[],env('HTTPS_ENABLE', true)) }}">Power-BI</a></li>
      @endif

      <li><a href="{{ url('/analytics/',[],env('HTTPS_ENABLE', true)) }}">Analytics</a></li>
      <li><a href="#" id="temp_id_trigger" data-toggle="modal" data-target="#user_profile" class="profile_popup">Profile</a></li>

      <li><a href="{{env('APP_URL')}}/setting/{{Session::get('space_info')->id}}">Settings</a></li>
      <li><a href="{{ url('/logout') }}">Log Out</a></li>
   </ul>
   <ul class="nav navbar-nav navbar-right hidden-xs onboarding-right-nav text-center">
      <li class="community-btn feed-button"><a href="{{ url('/clientshare/'.Session::get('space_info')->id,[],env('HTTPS_ENABLE', true)) }}" class="nav-btn @if($req_url=='clientshare' || $req_url=='') active @endif">         
         <span class="nav-icon"><img src="{{asset('/images/ic_home.svg', env('SECURE_COOKIES', true) )}}" alt="" /></span>
         <span>Home</span>
         </a>
      </li>

      @if(Session::get('space_info')->reports_count)
      <li class="community-btn feed-button"><a href="{{ url('/power_reports/'.Session::get('space_info')->id,[],env('HTTPS_ENABLE', true)) }}" class="nav-btn @if($req_url=='power_reports' || $req_url=='') active @endif">         
         <span class="nav-icon"><img width="20" src="{{asset('/images/power_bi.svg', env('SECURE_COOKIES', true) )}}" alt="" /></span>
         <span>Power-BI</span>
         </a>
      </li>
      @endif

      <li class="community-btn ">
         <a class="navbar-brand nav-btn vodafone-btn hidden-xs @if($req_url=='analytics') active @endif" href="{{ url('/analytics/',[],env('HTTPS_ENABLE', true)) }}">
            <span class="nav-icon"><img src="{{ env('APP_URL') }}/images/ic_poll.svg"></span>
            <span>Analytics</span>
         </a>
      </li>
      @if($feedback_status)
      <li class="community-btn"><a href="{{env('APP_URL')}}/feedback/{{$current_month}}/{{$current_year}}/{{Session::get('space_info')->id}}" data-toggle="modal"  class="nav-btn @if($req_url=='feedback') active @endif">
         <span class="nav-icon"><img src="{{ env('APP_URL') }}/images/ic_chat.svg" alt="" /></span>
         <span>Feedback</span>
         </a>
      </li>
      @endif
      
      <li>
         <a href="#" class="dropdown-toggle nav-btn remove_badge pro-file" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
            <div class="notification_count"></div>
            <span class="nav-icon"><img src="{{ env('APP_URL') }}/images/ic_notifications.svg" alt="" /></span>
            <span>Notifications</span>
         </a>
         <ul class="dropdown-menu notifications notificationdropdwon  ">
            <li class="header">Notifications</li>
            <li id="loading_li" class="header"><img src="{{env('APP_URL')}}/images/loading_bar1.gif"></li>
         </ul>
         <input type="hidden" class="notification_limit" value="0">
         <input type="hidden" class="notification_offset" value="0">
         <input type="hidden" class="notification_limit_more" value="0">
         <input type="hidden" class="notification_offset_more" value="0">
      </li>
      <!-- search nav -->
      <li>
         <a href="#" class=" dropdown-toggle nav-btn ic_search" >
         <span class="nav-icon"><img src="{{ env('APP_URL') }}/images/ic_search.svg" alt=""></span>
         <span>Search</span>
         </a>
         @php
         $spaceId = Session::get('space_info')['id'];
         $userId = Auth::user()->id;
         @endphp
         <div class="nav-search-wrap search-input-wrap" style="display:none;">
            <input type="text" id="search-input" class="form-control" placeholder="Search.." onkeydown="down()" onkeyup="up('{{$spaceId}}','{{$userId}}')">
            <span class="search_close"><img src="{{ env('APP_URL') }}/images/ic_search_close.svg" alt=""></span>
         </div>
         <div class="search-dropdown-wrap" style="display:none;">
            <ul class="search-dropdown" id="search-results">
            </ul>
         </div>
      </li>
      @php
      $img_t = Auth::user()->profile_image_url??env('APP_URL').'/images/dummy-avatar-img.svg';
      $img_t = strlen($img_t)?$img_t:env('APP_URL').'/images/dummy-avatar-img.svg';
      @endphp
      <li class="dropdown pro-file">
         <a href="#" class="dropdown-toggle nav-btn" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
         <span class="nav-icon account_icon">
            <span class="user-profile" style="background-image: url('{{$img_t}}') !important";></span>
         </span>
         <span class="account-nav">Account <span class="down-arroww"><img src="{{ env('APP_URL') }}/images/Shape.svg"></span></span>
         </a>
         <ul class="dropdown-menu">
            <li><a id="temp_id_trigger" href="#" data-toggle="modal" data-target="#user_profile" class="profile_popup">Profile</a></li>
            <li><a href="{{env('APP_URL')}}/setting/{{Session::get('space_info')->id}}" class="nav-btn" >Settings</a></li>
            @if(env('APP_ENV') == 'local' || env('APP_ENV') == 'staging')
            <li><a href="{{env('APP_URL')}}/version-switch/{{Session::get('space_info')->id}}/1" class="nav-btn" >Switch CS version</a></li>
            @endif
            <li><a href="{{ url('/logout') }}">Log Out</a></li>
         </ul>
      </li>
   </ul>
</div>
<!-- /.navbar-collapse -->