<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="Cache-control" content="no-cache">
    @include('layouts.common-header')
  </head>


  <body class="feed @if(isset($header_class)) {{$header_class}} @endif">
    <nav class="navbar navbar-default navbar-fixed-top">
      @include('layouts/navbar')
    </nav>
    
    <section class="main-content">
      @section('sidebar')
      @show
      @yield('content')
    </section>
  </body>
</html>