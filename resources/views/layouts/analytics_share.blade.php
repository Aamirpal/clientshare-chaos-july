<html>
   <head>
      @include('layouts.common-header')
   </head>
   <body class="feed analytics-page temp">
      <nav class="navbar navbar-default">
         <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
               <a class="navbar-brand nav-btn" href="{{ url('/dashboard') }}">
               <img class="img-responsive" alt="" src="{{ url('/',[], env('HTTPS_ENABLE', true)) }}/images/ic_clientShare.svg">
               <span>Client Share</span>
               </a>
               <ul class="nav navbar-nav navbar-right onboarding-right-nav pull-right">
                  <li class="dropdown">
                     <a href="#" class="dropdown-toggle nav-btn" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                     <img src="{{ url('/',[], env('HTTPS_ENABLE', true)) }}/images/ic_settings.svg" alt="">
                     </a>
                     <ul class="dropdown-menu">
                        <li><a href="{{ url('/profile') }}">Profile</a>
                        </li>
                        <li><a href="{{ url('/settings') }}">Settings</a>
                        </li>
                        <li><a href="{{ url('/logout') }}">Signout</a>
                        </li>
                     </ul>
                  </li>
               </ul>
            </div>
            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-2">        
            </div>
            <!-- /.navbar-collapse -->
         </div>
         <!-- /.container-fluid -->
      </nav>
      <section class="main-content">
      <div class="analytics-breadcrumb">
         <ol class="breadcrumb container">
            <li><a href="{{ url('/dashboard') }}">Admin</a></li>
            <li  class="active"><a href="#">{{Session::get('space_info')['share_name']}} Client Share</a></li>
         </ol>
      </div>
      <!-- analytics-breadcrumb -->
      @section('sidebar')
      @show
      @yield('content')