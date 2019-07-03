<!DOCTYPE html>
<html>
  <head>
    @php
      $session_data = spaceSessionData($space_id);
    @endphp
    
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="Cache-control" content="no-cache">
    <script>
      var check_share_active;
    </script>
    @include('layouts.common-header')
  </head>

  <!--- Header End -->

  <body class="feed @if(isset($header_class)) {{$header_class}} @endif">
    @if (session('status'))
    <div class="alert alert-success flash-sucess">
      {{ session('status') }}
    </div>
    @endif
    @php
      $account_data = json_decode(Auth::User()->social_accounts);
    @endphp

    <input type="hidden" class="check_tour_status" value="{{Auth::user()->show_tour}}">
    <div class="black-overlay" style="display:none"></div>

    <!-- Navigation start -->

    <nav class="navbar navbar-default navbar-fixed-top">
      @include('layouts/navbar')
    </nav>

    <!-- Navigation end -->

    <!-- Main header after main navigation -->
    <section class="main-content">
      <div class="main-banner-row">
        <div class="container-fluid spacename lazy-asset" data-lazy-asset="{{wrapUrl($session_data['background_image'])??env('APP_URL').'/images/bgIimg.jpg'}}" style="background-image: url('/images/bgDummy.jpg');">
            <div class="container">
              <div class="col-lg-6 col-lg-offset-3 col-md-6 col-md-offset-3 col-sm-6 col-sm-offset-3 col-xs-10 col-sm-offset-2 welcome_share_wrap">
                <a href="{{env('APP_URL').'/clientshare/'.$session_data['id'] }}">
                  <div class="space-pic-wrap">
                  <span class="lazy-asset" data-lazy-asset="{{ $session_data['company_seller_logo']??url('/images/login_user_icon.png',[],env('HTTPS_ENABLE', true)) }}" ></span>
                  <span class="space-pic lazy-asset"  data-lazy-asset="{{ $session_data['company_buyer_logo']??url('/images/login_user_icon.png',[],env('HTTPS_ENABLE', true)) }}" ></span>
                  </div>
                </a>
                <input type="hidden" value="{{$session_data['id']}}" name="share_id" class="hidden_space_id">
                <div class="share_name">
                <h1><span class="small"></span>
                <span class="s_name"><a href="{{env('APP_URL').'/clientshare/'.$session_data['id'] }}">{{ $session_data['share_name'] }}</a></span> 
                <span class="fix-name"><a href="{{env('APP_URL').'/clientshare/'.$session_data['id'] }}">Client Share</a> </span> 
                  @if($session_data['is_admin'])
                    <span data-toggle="modal" data-target="#share_logo_edit" class="edit-icon">
                      <img src="{{env('APP_URL')}}/images/ic_edit.svg">
                    </span>
                  @endif
                </h1>
              </div>
              <div class="edit_share" style="display:none">
                <div class="space-heading left">
                  <span>&nbsp;</span>
                  <div id="input" class="updated_share" contenteditable>{{ $session_data['share_name'] }}</div>
                  <span class="input-span">Client Share</span>
                </div>
                <button class="btn btn-primary edit_share_save_btn left">save</button>
                <span class="cancel_edit_share left">Cancel</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      @section('sidebar')
      @show
      @yield('content')
</section>
@include('layouts.logo_banner_popup')
</body>

@include('layouts.profile_popup')


<input type="hidden" value="{{Auth::user()->id}}" class="notify_userid">
<input type="hidden" value="{{ Session::get('space_info')['id'] }}" class="notify_spaceid">
<script rel="text/javascript" src="{{ url('js/custom/header_banner.js?q='.env('CACHE_COUNTER', rand()),[],env('HTTPS_ENABLE', true)) }}"></script>
<!-- Hotjar Tracking Code for https://uat-clientspace.herokuapp.com -->
<script>
  (function(h,o,t,j,a,r){
    h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
    h._hjSettings={hjid:{{env('HOT_JAR_ID')}},hjsv:6};
    a=o.getElementsByTagName('head')[0];
    r=o.createElement('script');r.async=1;
    r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
    a.appendChild(r);
  })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
</script>
</html>