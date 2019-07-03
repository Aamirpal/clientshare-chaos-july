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
    $space_user =$session_data['space_user'];
    $ssl = env('APP_ENV') == 'local'? false : true;
    $data = $session_data;
    $space_id = $session_data->id;
    @endphp
    <script Type="text/javascript">
        var baseurl="{{ getenv('APP_URL') }}";
        var loggin_user_image = "{{$profile_image}}";
    </script>
    <style> .hidden{display:none;}</style>
    <body class="v2-clientshare">
        <div class="main_content_loader">
            <div class="loader-col">
                <img width="60" src="{{asset('images/v2-images/loader.svg')}}" alt="Loader..." />
            </div>
        </div>
    <header>
        <div class="top-navbar">
            <div class="share-dropdown-wrap">
                <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <span class="share-logo"><img src="{{ $session_data->seller_circular_logo??url('/images/login_user_icon.png',[],env('HTTPS_ENABLE', true)) }}" alt="Share Logo" /></span>
                    <span class="share-logo"><img src="{{ $session_data->buyer_circular_logo??url('/images/login_user_icon.png',[],env('HTTPS_ENABLE', true)) }}" alt="Share Logo" /></span>
                    <a class="nav-link dropdown-toggle nav-dropdown-link" href="#" id="shareDropdown" role="button" data-toggle="dropdown" 
                    aria-haspopup="true" aria-expanded="false">
                    {{ $session_data->share_name }}
                    </a>
                    <ul class="dropdown-menu custom-scrollbar" aria-labelledby="shareDropdown" id="search_share_ul">
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
                <li class="share_logo">
                    @if($is_logged_in_user_admin)
                        <span data-toggle="modal" data-target="#share_logo_edit" class="edit-icon">
                          <img src="https://uat-clientspace.herokuapp.com/images/ic_edit.svg">
                        </span>
                    @endif
                </li>
                </ul>
            </div>
            <div class="header-search-wrap" id="global_search">
                <!-- Render react part-->
            </div>
            <div class="profile-wrap">
                <div class="notification-wrap">
                    <div class="notification-dropdown dropdown">
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
                        <li><a href="{{env('APP_URL')}}/version-switch/{{Session::get('space_info')->id}}/0" class="nav-btn" >Switch CS version</a></li>
                        @endif
                        <li class="dropdown-item"><a href="{{url('/logout/',[],env('HTTPS_ENABLE', true))}}">Log out</a></li>
                    </ul>
                </li>
                </ul>
            </div>
        </div>
    </header>
    
        @include('layouts.layoutsV2.include.left-sidebar')
        <div id="react_modal" class="react-modal-col">
        </div>
        <div class="clientshare-v2-container">
            @yield('content')
            
            @include('layouts.layoutsV2.include.profile_popup')
           
        </div>
        <span data-toggle="modal" data-target="#welcome_tour" class="tour-trigger"></span>
        <script rel="text/javascript" src="{{ mix('js/compiled/main_compiled-v2.js') }}"></script>
    <script rel="text/javascript" src="{{ asset('js/handle_bar.js') }}"></script>
    <script rel="text/javascript" src="{{ asset('js/custom/circlos.js') }}"></script>
    <script rel="text/javascript" src="{{ asset('js/custom/v2/welcome_tour.js') }}"></script>
    <script rel="text/javascript" src="{{ asset('js/custom/v2/header_banner.js') }}"></script>
    <script rel="text/javascript" src="{{ asset('js/custom/v2/twitter_feed.js') }}"></script>
    <script rel="text/javascript" src="{{ asset('js/custom/v2/post_feature.js') }}"></script>
    <script type="text/javascript">
        $('.dropdown-toggle').dropdown()
        var check_user_is_new ='{{$check_user_is_new}}';
        var linkedin_data ='{{!empty($_GET["linkedin"])? $_GET["linkedin"] :''}}';
        var buyer ="{{!empty(session('buyer'))? session('buyer') :''}}";
        var linked_in ="{{!empty(session('linked'))? session('linked') :''}}";
        if(check_user_is_new || linkedin_data == 'yes'|| buyer == 'yes' || linked_in == 'yes'){
           setTimeout(function(){
                //$('#show_user_profile').trigger('click');
        },100);
        }
    </script>
    <script type="text/javascript">
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
           });
         }
           $(document).ready(function () {
                runOnBoardingTour();
              });
    </script>
    @yield('scripts')   
    </body>


</html>
