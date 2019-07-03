<!DOCTYPE html>
<html lang="en">
   <head>
      @php
         $ssl = env('APP_ENV')=='local' ? false : true;
      @endphp
      <meta charset="utf-8" />
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta http-equiv="pragma" content="no-cache">
      <meta http-equiv="Cache-control" content="no-cache">
      <title>{{config('constants.APPLICATION.display_name')}}</title>
      <meta property="og:url" content="{{env('marketing_site_url', 'http://myclientshare.com')}}" />      
      <meta property="og:image" content="{{ env('CS_SHAREABLE_LOGO', url('images/logo-white.jpg',[],$ssl)) }}" />
      <meta name="description" content="{{config('constants.APPLICATION.description')}}" />
      <link rel="stylesheet" href="{{ url('css/bootstrap.min.css',[],$ssl) }}">
      <link rel="stylesheet" href="{{ url('css/style.css?q='.env('CACHE_COUNTER', '500'),[],$ssl) }}">
      <link href="https://fonts.googleapis.com/css?family=Mada:300,400,500,600,700,900" rel="stylesheet">
      <script src="{{ url('js/jquery.min.js',[],$ssl) }}"></script>
      <script src="{{ url('js/jquery.cookie.js',[],$ssl) }}"></script>
      <script rel="text/javascript" src="{{ url('js/custom/login.js?q='.env('CACHE_COUNTER', '500'),[],env('HTTPS_ENABLE', true)) }}"></script>
      <script rel="text/javascript" src="{{ url('js/custom/logger.js?q='.env('CACHE_COUNTER', '500'),[],env('HTTPS_ENABLE', true)) }}"></script>
      <!-- <script type="text/javascript" src="https://cdn.ywxi.net/js/1.js" async></script> -->
      <link href="https://fonts.googleapis.com/css?family=Lato:300,300i,400,400i,700,700i,900,900i" rel="stylesheet">
      <link rel="icon" href="{{ url('images/CSProfileImg.png',[],$ssl) }}"  sizes="32x32" />
      <style>
         input:-webkit-autofill,
         input:-webkit-autofill:hover,
         input:-webkit-autofill:focus,
         input:-webkit-autofill:active {
         transition: background-color 5000s ease-in-out 0s !important;
         -webkit-text-fill-color: #fff !important; background: rgba(0, 0, 0, 0.1) !important; color:rgba(255, 255, 255, 1) !important;
         }
      </style>
      <script>baseurl = "{{ url('/',[],$ssl) }}";</script>
      @include('layouts.drift')
   </head>
   <body class="loginpage">
      <p style="display:none">{{config('constants.APPLICATION.description')}}</p>
      <div class="wrapper">
         <div class="loginwrap">
            @if (session('status'))
            <div class="success-box login_error_message">
               <p class="text-center">If this user is registered they will receive a password reset link <span class="close_btn"><img src="{{ url('images/ic_close_dark.svg',[],$ssl) }}" alt=""></span></p>
            </div>
            @elseif (isset($existence_error) || $errors->has('verify_code'))
            <div class="success-box login_error_message">
               <p class="text-center">{{ $existence_error??$errors->first('verify_code')  }} <span class="close_btn"><img src="{{ url('images/ic_close_dark.svg',[],$ssl) }}" alt=""></span></p>
            </div>
            @else
            <div class="success-box login_error_message timeout-message" style="display:none">
               <p class="text-center"><span class="close_btn"><img src="{{ url('images/ic_close_dark.svg',[],$ssl) }}" alt=""></span></p>
            </div>
            @endif
            @if (session('reset_password_success'))
            <div class="success-box login_error_message">
               <p class="text-center"> {{session('reset_password_success')}} <span class="close_btn"><img src="{{ url('images/ic_close_dark.svg',[],$ssl) }}" alt=""></span></p>
            </div>
            @endif
            <div class="container">
               <div class="col-lg-12">
                  <img class="logo" src="{{ url('images/logo.png',[],$ssl) }}" alt="Client Share" />
               </div>
               <!-- col-lg-12 -->
               <div class="clearfix"></div>
               @yield('content')
            </div>
            <!-- container -->
         </div>
         <!-- loginwrap -->
      </div>
      <!-- wrapper -->
      <script rel="text/javascript" src="{{ url('js/bootstrap.min.js',[],$ssl) }}"></script>
      <!-- Hotjar Tracking Code -->
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
   </body>
</html>