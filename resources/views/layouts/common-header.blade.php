 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
 <meta http-equiv="X-UA-Compatible" content="IE=edge">
 <meta http-equiv="pragma" content="no-cache">
 <meta http-equiv="Cache-control" content="no-cache">
 <meta name="csrf-token" content="{{ csrf_token() }}">
 <title>Client Share</title>


<link rel="stylesheet" href="{{ mix('css/main_compiled.css') }}{{'?q='.getenv('CACHE_COUNTER', '500')}}">

 <link href="https://fonts.googleapis.com/css?family=Lato:300,300i,400,400i,700,700i,900,900i" rel="stylesheet">
 <link href="https://fonts.googleapis.com/css?family=Mada:300,400,500,600,700,900" rel="stylesheet">
 
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
 <script src="https://www.youtube.com/iframe_api"></script>

<script>
 baseurl = "{{ env('APP_URL') }}";
 current_space_id = session_space_id = "{!!Session::get('space_info')['id']!!}";
 page_time = {{strtotime(Carbon\Carbon::now())}};
 var file_view_count = "{{ url('/file_view_count',[],env('HTTPS_ENABLE', true)) }}";
 var save_quick_links = "{{ url('/save_quick_links',[],env('HTTPS_ENABLE', true)) }}";
 var get_quick_links = "{{ url('/get_quick_links',[],env('HTTPS_ENABLE', true)) }}";
 var community_member = "{{ url('/community_member',[],env('HTTPS_ENABLE', true)) }}";
 var logged_in_user_role = 'user';
 @php
    $is_logged_in_user_admin = isset($space_user) && $space_user[0]['user_role']['user_type_name'] == 'admin' ? 1 : 0;
 @endphp
 logged_in_user_role = '@php echo $is_logged_in_user_admin ? "admin" : "user"; @endphp';
 var is_logged_in_user_admin =  {{$is_logged_in_user_admin}};
</script>


<script type="text/javascript" src="{{ url('theia-sticky-sidebar-master/dist/ResizeSensor.js',[],env('HTTPS_ENABLE', true)) }}"></script>
<script type="text/javascript" src="{{ url('theia-sticky-sidebar-master/dist/theia-sticky-sidebar.js',[],env('HTTPS_ENABLE', true)) }}"></script>
<script type="text/javascript" src="{{ url('theia-sticky-sidebar-master/js/test.js',[],env('HTTPS_ENABLE', true)) }}"></script>

<script rel="text/javascript" src="{{ mix('js/compiled/main_compiled.js') }}"></script>

<!-- Post Text Area end -->
<link rel="icon" href="{{ env('APP_URL') }}/images/CSProfileImg.png" sizes="32x32" />


    @include('layouts.drift')
    @if(env('APP_ENV') == 'production')
    <script type="text/javascript">
      window.heap=window.heap||[],heap.load=function(e,t){window.heap.appid=e,window.heap.config=t=t||{};var r=t.forceSSL||"https:"===document.location.protocol,a=document.createElement("script");a.type="text/javascript",a.async=!0,a.src=(r?"https:":"http:")+"//cdn.heapanalytics.com/js/heap-"+e+".js";var n=document.getElementsByTagName("script")[0];n.parentNode.insertBefore(a,n);for(var o=function(e){return function(){heap.push([e].concat(Array.prototype.slice.call(arguments,0)))}},p=["addEventProperties","addUserProperties","clearEventProperties","identify","removeEventProperty","setEventProperties","track","unsetEventProperty"],c=0;c<p.length;c++)heap[p[c]]=o(p[c])};
         heap.load("2650017777");
    </script>
    @endif