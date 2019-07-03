<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Client Share</title>
    <!-- sweetalert2 css-->
    <link rel="stylesheet" href="{{ url('css/sweetalert2(6.6.9).min.css') }}">
    <link rel="stylesheet" href="{{ url('css/bootstrap.min.css',[],env('HTTPS_ENABLE', true)) }}">
    <link rel="stylesheet" href="{{ url('css/stylecrop.css',[],env('HTTPS_ENABLE', true)) }}">
    <link rel="stylesheet" href="{{ url('css/font-awesome.min.css',[],env('HTTPS_ENABLE', true)) }}">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ url('css/font-awesome.css',[],env('HTTPS_ENABLE', true)) }}">
    <link rel="stylesheet" href="{{ url('css/bootstrap-select.min.css',[],env('HTTPS_ENABLE', true)) }}">
    <link rel="stylesheet" href="{{ url('css/style.css?q='.env('CACHE_COUNTER', '500'),[],env('HTTPS_ENABLE', true)) }}">
    <link rel="stylesheet" href="{{ url('css/daterangepicker.css',[],env('HTTPS_ENABLE', true)) }}">
    <link href="https://fonts.googleapis.com/css?family=Mada:300,400,500,600,700,900" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ url('/css/bootstrap-multiselect_v0_9_15.css') }}">
    <script src="{{ url('js/jquery.min.js',[],env('HTTPS_ENABLE', true)) }}"></script>
    <script src="{{ url('js/jquery.cookie.js',[],env('HTTPS_ENABLE', true)) }}"></script>
    <script rel="text/javascript" src="{{ url('js/custom/login.js?q='.env('CACHE_COUNTER', '500'),[],env('HTTPS_ENABLE', true)) }}"></script>
    <script rel="text/javascript" src="{{ url('js/custom/logger.js?q='.env('CACHE_COUNTER', '500'),[],env('HTTPS_ENABLE', true)) }}"></script>
    <script rel="text/javascript" src="{{ url('js/bootstrap.min.js',[],env('HTTPS_ENABLE', true)) }}"></script>
    <script rel="text/javascript" src="{{ url('js/bootstrap-select.js',[],env('HTTPS_ENABLE', true)) }}"></script>
     <script rel="text/javascript" src="{{url('js/bootstrap-multiselect_V0_9_15.js',[],env('HTTPS_ENABLE', true))}}"></script>
    <script rel="text/javascript" src="{{ url('js/modular_polyfill_standard.js',[],env('HTTPS_ENABLE', true)) }}"></script>
    
    <!-- sweetalert2 js -->
    <script src="{{ url('js/sweetalert2(6.6.9).min.js') }}"></script>
    <script rel="text/javascript" src="{{url('js/moment.min.js')}}"></script>

    <script rel="text/javascript" src="{{ url('js/dist/jquery.cropit.js',[],env('HTTPS_ENABLE', true)) }}"></script>
  
@include('layouts.drift')

      <link rel="icon" href="{{ env('APP_URL') }}/images/CSProfileImg.png" sizes="32x32" />
      <?php if (env('APP_ENV') == 'production') {?>
<script type="text/javascript">
   window.heap=window.heap||[],heap.load=function(e,t){window.heap.appid=e,window.heap.config=t=t||{};var r=t.forceSSL||"https:"===document.location.protocol,a=document.createElement("script");a.type="text/javascript",a.async=!0,a.src=(r?"https:":"http:")+"//cdn.heapanalytics.com/js/heap-"+e+".js";var n=document.getElementsByTagName("script")[0];n.parentNode.insertBefore(a,n);for(var o=function(e){return function(){heap.push([e].concat(Array.prototype.slice.call(arguments,0)))}},p=["addEventProperties","addUserProperties","clearEventProperties","identify","removeEventProperty","setEventProperties","track","unsetEventProperty"],c=0;c<p.length;c++)heap[p[c]]=o(p[c])};
     heap.load("2650017777");
</script>
<?php }?>



  </head>
      <body class="dashboard">
       <div class="black-overlay" style="display:none"></div>



        <nav class="navbar navbar-default navbar-fixed-top">
          <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
              <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="{{ url('/dashboard') }}"><img class="img-responsive"  src="{{ url('/') }}/images/logo_small.png" alt="Client Share"></a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav main-navigation">
                <li><a href="{{ url('/dashboard') }}">Admin</a></li>
                <li><a href="analytics/">Analytics</a></li>
                <li @if(Route::getCurrentRoute()->uri() == 'management-information') class="active" @endif><a href="{{url('/management-information')}}">Management Information</a></li>
                <li><a href="{{url('/email')}}">Performance Email</a></li>
                <li @if(Route::getCurrentRoute()->uri() == 'user-search') class="active" @endif><a href="{{url('/user-search')}}">User</a></li>
             <li class="hidden-lg hidden-md hidden-sm"><a href="{{ url('/profile') }}">Profile</a></li>
              <li class="hidden-lg hidden-md hidden-sm"><a href="{{ url('/settings') }}">Settings</a></li>
                 <li class="hidden-lg hidden-md hidden-sm"><a href="{{ url('/logout') }}">Signout</a></li>
              </ul>
              <ul class="nav navbar-nav navbar-right hidden-xs">
                <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                    <span><?=session()->get('key')[0]->first_name;?> <?=session()->get('key')[0]->last_name;?></span> <img class="img-responsive" src="{{ url('/') }}/images/profile_icon.svg"> <i class="fa fa-angle-down" aria-hidden="true"></i></a>
                  <ul class="dropdown-menu">
                    <li><a href="{{ url('/profile') }}">My Profile</a></li>
                    <li><a href="{{ url('/settings') }}">Settings</a></li>
                    <li><a href="{{ url('/logout') }}">Signout</a></li>
                  </ul>
                </li>
              </ul>
            </div><!-- /.navbar-collapse -->
          </div><!-- /.container-fluid -->
        </nav>



        <section class="main-content">
        <div class="container-fluid">


          @section('sidebar')
          @show
          @yield('content')


        </div>
</section>

<script>
   $(document).ready(function(){


       $('[data-toggle="popover"]').popover()





     function setModalMaxHeight(element) {
       this.$element     = $(element);
       this.$content     = this.$element.find('.modal-content');
       var borderWidth   = this.$content.outerHeight() - this.$content.innerHeight();
       var dialogMargin  = $(window).width() < 768 ? 20 : 60;
       var contentHeight = $(window).height() - (dialogMargin + borderWidth);
       var headerHeight  = this.$element.find('.modal-header').outerHeight() || 0;
       var footerHeight  = this.$element.find('.modal-footer').outerHeight() || 0;
       var maxHeight     = contentHeight - (headerHeight + footerHeight);

       this.$content.css({
           'overflow': 'hidden'
       });

       this.$element
         .find('.modal-body').css({
           'max-height': maxHeight,
           'overflow-y': 'auto'
       });
     }

     $('.modal').on('show.bs.modal', function() {
       $(this).show();
       setModalMaxHeight(this);
     });

     $(window).resize(function() {
       if ($('.modal.in').length != 0) {
         setModalMaxHeight($('.modal.in'));
       }
     });
     });
     </script>