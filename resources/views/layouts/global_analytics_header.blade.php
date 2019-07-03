<!DOCTYPE html>

<html>
   <head>
      @include('layouts.common-header')
   </head>
   <body class="feed @if(isset($header_class)) {{$header_class}} @endif">
      @if (session('status'))
      <div class="alert alert-success flash-sucess">
         {{ session('status') }}
      </div>
      @endif
      <?php
         $account_data = json_decode(Auth::User()->social_accounts);
         ?>
      <!-- SESSION EXPIRE MESSAGE DIV START -->
      <!-- SESSION EXPIRE MESSAGE DIV END -->
      <input type="hidden" class="check_tour_status" value="{{Auth::user()->show_tour}}">
      <div class="black-overlay" style="display:none"></div>
      <nav class="navbar navbar-default navbar-fixed-top">
        @include('layouts/navbar')
      </nav>
      <section class="main-content analytics-global-header">
         <div class="container-fluid spacename">
            <div class="analytics-header">
               <img src="{{ url('/',[],$ssl) }}/images/ic_poll_large.svg"><span>Client Share Analytics</span>
            </div>
         </div>
         <input type="hidden" value="{{Session::get('space_info')['id']}}" name="share_id" class="hidden_space_id">
         @section('sidebar')
         @show
         @yield('content')
         </div>
      </section>
      <script>
         $(document).ready(function(){
          var $window = $(window), previousScrollTop = 0, scrollLock = false;
         $('.navbar-brand').on('click', function(){
             $('body').toggleClass('test-click1');
              if ($("body").hasClass("test-click1")) {
              scrollLock = true;
              }
              else
              {
               scrollLock = false;
              }
              $window.scroll(function(event) {
              if(scrollLock) {
              $window.scrollTop(previousScrollTop);
               }
         
              previousScrollTop = $window.scrollTop();
         
               } );
         });
         $(window).click(function() {
         $('body').removeClass('test-click1');
         scrollLock = false;
          $window.scroll(function(event) {
              if(scrollLock) {
              $window.scrollTop(previousScrollTop);
               }
         
              previousScrollTop = $window.scrollTop();
         
               } );
         });
          $('[data-toggle="popover"]').popover();
          $('.ic_search').click(function(){
              $('.search-input-wrap').show();
              $('.ic_search').hide();
              $('#search-results').show();
         
              $('#search-input').focus();
          });
            $('.search_close').click(function(){
              $('#search-input').val('');
              $('.search-input-wrap').hide();
              $('.ic_search').show();
              $('#search-results').hide();
              $('#msearch-results').hide();
               $('.search-dropdown-wrap1').hide();
               $('.search-dropdown-wrap').hide();
            });

         });
        
      </script>
   </body>
 @include('layouts.profile_popup')
</html>
<input type="hidden" value="{{Auth::user()->id}}" class="notify_userid">
<input type="hidden" value="{{ Session::get('space_info')['id'] }}" class="notify_spaceid">

