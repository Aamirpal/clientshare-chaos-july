<html lang="{{ app()->getLocale() }}">
    <head>
        @include('layouts.layoutsV2.include.common-header')
    </head>
    @php
    $current_month = date('n');
    $current_year =  date('Y');
    $session_data = Session::get('space_info');
    $feedback_status = $session_data->feedback_status??false;
    $check_user_is_new = !empty($session_data['space_user'][0]['metadata']['user_profile']['company']) ? false : true;
    $check_buyer =  checkBuyerSeller($session_data['id'], Auth::User()->id);
    if($check_user_is_new){
    $buyer_info = $session_data['BuyerName'];
    $buyer_seller = [$session_data['BuyerName'], $session_data['SellerName']];
    }
    $account_data = json_decode(Auth::User()->social_accounts);

    $profile_image = (!empty(Auth::user()->circular_profile_image))? composeUrl(Auth::user()->circular_profile_image) :
    (!empty(Auth::user()->profile_image_url)? Auth::user()->profile_image_url :'');

    if(!empty(Auth::user()->profile_image_url) && session('linked')|| session('buyer')|| empty($profile_image) && (!empty($_GET["linkedin"]))){
    $profile_image = $account_data->linkedin->user->pictureUrls->values[0]??'';
    }
    $profile_image = strlen($profile_image) ? $profile_image:asset('/images/v2-images/user-placeholder.svg');
    @endphp
    <script Type="text/javascript">
        var baseurl="{{ getenv('APP_URL') }}";
        var loggin_user_image = "{{$profile_image}}";
    </script>
    @yield('styles')
    <body class="v2-clientshare">
        <div class="main_content_loader">
            <div class="loader-col">
                <img width="60" src="{{asset('images/v2-images/loader.svg')}}" alt="Loader..." />
            </div>
        </div>
    <header>
        <div class="top-navbar lazy-asset" data-lazy-asset="{{wrapUrl($session_data['background_image'])??env('APP_URL').'/images/bgIimg.jpg'}}" style="background-image: url('/images/bgDummy.jpg');">
            <div class="share-dropdown-wrap welcome_share_wrap">
                <ul class="navbar-nav">
                    <li class="nav-item dropdown space-pic-wrap">
                        @php
                            $banner_seller_logo = $session_data->seller_circular_logo == config('constants.EMAIL_DEFAULT_SHARE_LOGO') ? 
                               ($session_data->seller_logo??config('constants.EMAIL_DEFAULT_SHARE_LOGO')) : $session_data->seller_circular_logo;
                        @endphp
                        <span class="share-logo"><img src="{{$banner_seller_logo}}{{'?q='.urlencode($session_data->updated_at)}}" alt="Share Logo" /></span>

                        @php
                            $banner_buyer_logo = $session_data->buyer_circular_logo == config('constants.EMAIL_DEFAULT_SHARE_LOGO') ? 
                               ($session_data->buyer_logo??config('constants.EMAIL_DEFAULT_SHARE_LOGO')) : $session_data->buyer_circular_logo;
                        @endphp
                        <span class="share-logo"><img src="{{$banner_buyer_logo}}{{'?q='.urlencode($session_data->updated_at)}}" alt="Share Logo" /></span>

                    <a class="nav-link dropdown-toggle nav-dropdown-link" href="#" id="shareDropdown" role="button" data-toggle="dropdown" 
                    aria-haspopup="true" aria-expanded="false">
                        <span class="share-name-col">{{ $session_data->share_name }}</span>
                    </a>
                    <ul class="dropdown-menu custom-scrollbar" aria-labelledby="shareDropdown" id="search_share_ul">
                        <div class="hidden-desktop">
                            <div class="share-mbl-col">
                                Shares
                            </div>
                        </div>
                        <div class="share-search">
                            <input autocomplete="off" class="form-control search-box" type="search" id="search_share" onkeyup="searchShare()" placeholder="Type Something...">
                        </div>
                        @php $req_url   =  request()->segment(1); @endphp
                        @php $space_value=Session::get('user_spases_space_user'); @endphp
                        @if(isset($space_value))
                        @foreach( $space_value as $space_user_info )
                        @if( strpos(url()->current(), $space_user_info['share']['id']) )
                        <li class="dropdown-item active" value="{{ $space_user_info['share']['share_name'] }}"><a href="{{ url('/clientshare/'.$space_user_info['share']['id'],[],env('HTTPS_ENABLE', true)) }}"><span class="share-name">{{ $space_user_info['share']['share_name'] }}</span> <span id="shareNoti_{{$space_user_info['share']['id']}}" class="notification-count" style="display:none;"></span></a></li>
                        @else
                        <li class="dropdown-item" value="{{ $space_user_info['share']['share_name'] }}"><a href="{{ url('/clientshare/'.$space_user_info['share']['id'],[],env('HTTPS_ENABLE', true)) }}"><span class="share-name">{{ $space_user_info['share']['share_name'] }}</span> <span id="shareNoti_{{$space_user_info['share']['id']}}" class="notification-count" style="display:none;"></span></a></li>
                        @endif
                        @endforeach
                        @endif
                    </ul>
                </li>
                <li class="edit-share-icon">
                    @if($is_logged_in_user_admin)
                        <span data-toggle="modal" data-target="#share_logo_edit" class="edit-icon">
                          <img src="{{asset('images/v2-images/edit-icon-white.svg')}}">
                        </span>
                    @endif
                </li>
                </ul>
            </div>
            <div class="header-search-wrap" id="global_search">
                <!-- Render react part-->
            </div>
            <div class="mobile-humberg-menu">
                <nav class="navbar navbar-light light-blue lighten-4">
                    <button class="navbar-toggler toggler-example" type="button" data-toggle="collapse" data-target="#navbarSupportedContent1"
                    aria-controls="navbarSupportedContent1" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="dark-blue-text">
                            <span class="menu-line"></span>
                            <span class="menu-line"></span>
                            <span class="menu-line"></span>
                        </span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent1">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="?type=review">Business Review</a>
                            </li>
                            <li class="nav-item active">
                                <a class="nav-link" href="#">My account</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{url('/setting',[$session_data->id],env('HTTPS_ENABLE', true))}}">Settings</a>
                            </li>
                            <li class="nav-item">
                                <a class="profile_popup nav-link" id="show_user_profile" href="JavaScript:void(0);">Profile</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{url('/logout/',[],env('HTTPS_ENABLE', true))}}">Log out</a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
            <div class="profile-wrap">
                <div class="notification-wrap ">
                    <div class="notification-dropdown dropdown ">
                        <div class="notification_count"></div>
                        <a href="#" class="dropdown-toggle nav-dropdown-link" id="notificationDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img src="{{asset('images/v2-images/notification-icon.svg')}}" alt="" />
                        </a>
                        <div class="dropdown-menu" id="main-notification" aria-labelledby="notificationDropdown"></div>
                    </div>
                </div>

                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <span class="user-profile-pic"><span class="lazy-asset" data-lazy-asset="{{($profile_image)??env('APP_URL').'/images/bgIimg.jpg'}}" style="background-image: url('/images/bgDummy.jpg');" id="user_profile_popup"></span></span>
                        <a class="nav-link dropdown-toggle nav-dropdown-link" href="JavaScript:Void(0);" id="profileDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Hello, <span>{{ ucfirst(Auth::User()->first_name)??''}}</span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="profileDropdown">
                            <li class="dropdown-item"><a class="profile_popup" id="show_user_profile" href="JavaScript:void(0);">Profile</a></li>

                            @if(env('APP_ENV') == 'local' || env('APP_ENV') == 'staging')
                            <li class="dropdown-item"><a href="{{env('APP_URL')}}/version-switch/{{Session::get('space_info')->id}}/0" class="nav-btn" >Switch CS version</a></li>
                            @endif
                            <li class="dropdown-item"><a href="{{url('/logout/',[],env('HTTPS_ENABLE', true))}}">Log out</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <textarea 
    contentEditable="true"
    readonly="false"
    class="ios_copy_link_hidden_textarea"
    id="copy_post_link_ios"></textarea>
    </header>
    
        @include('layouts.layoutsV2.include.left-sidebar')
        <div id="react_modal" class="react-modal-col">
        </div>
        <div class="clientshare-v2-container">
            @yield('content')
            @include('layouts.layoutsV2.include.profile_popup')
            @include('layouts/layoutsV2/include/logo_banner_popup')
        </div>
        <input type="hidden" class="notification_limit" value="0">
        <input type="hidden" class="notification_offset" value="0">
        <input type="hidden" class="notification_limit_more" value="0">
        <input type="hidden" class="notification_offset_more" value="0">

        <script rel="text/javascript" src="{{ mix('js/compiled/main_compiled-v2.js') }}"></script>
        <script rel="text/javascript" src="{{ asset('js/custom/v2/header_banner.js?q='.env('CACHE_COUNTER', rand())) }}"></script>
        <script rel="text/javascript" src="{{ asset('js/custom/v2/twitter_feed.js?q='.env('CACHE_COUNTER', rand())) }}"></script>
        <script type="text/javascript">
        $('.dropdown-toggle').dropdown()
        var check_user_is_new ='{{$check_user_is_new}}';
        var linkedin_data ='{{!empty($_GET["linkedin"])? $_GET["linkedin"] :''}}';
        var buyer ="{{!empty(session('buyer'))? session('buyer') :''}}";
        var linked_in ="{{!empty(session('linked'))? session('linked') :''}}";
        if(check_user_is_new || linkedin_data == 'yes'|| buyer == 'yes' || linked_in == 'yes'){
           setTimeout(function(){
                $('#show_user_profile').trigger('click');
        },100);
        }
  
        (function($){
			$(window).on("load",function(){			
				$(".custom-scrollbar").mCustomScrollbar({
                    theme:"minimal"
                });		
            });
        })(jQuery);

        window.onload = function () {
            $.getScript("{{ mix('js/compiled/react_compiled-v2.js') }}").done(function(script, textStatus) {
               $('.main_content_loader').hide();
            $(document).on('click', '.add-member-tile, .invite-button', function(event) {
                event.preventDefault();
                $('#myModalInvite').modal('toggle');
                $('.white_box_info').hide();
                });
           });
         }

        $(document).ready(function () {
        $('.upload-logo').tooltip({
            customClass: 'tooltip-custom'
        });
        $(document).click(function (event) {
                var clickover = $(event.target);
                var _opened = $(".navbar-collapse").hasClass("navbar-collapse in");
                if (_opened === true && !clickover.hasClass("navbar-toggle")) {
                    $("button.navbar-toggle").click();
                }
        });
        notifications_trigger();
        var notification_run = setInterval(function() {
          notifications_trigger();
        }, <?php echo Config::get('constants.AJAX_INTERVAL') ?>);

        function notifications_trigger(){
         notificationCount();
         getAllShareNotifications();
        }
        });
    </script>
    @yield('scripts')   
    <div role="button" tabindex="0" id="client-overlay"></div>
    </body>


</html>