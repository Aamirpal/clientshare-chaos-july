 <meta charset="utf-8">
 <meta http-equiv="X-UA-Compatible" content="IE=edge">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <meta http-equiv="X-UA-Compatible" content="IE=edge">
 <meta http-equiv="pragma" content="no-cache">
 <meta http-equiv="Cache-control" content="no-cache">
 <meta name="csrf-token" content="{{ csrf_token() }}">
 <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
 <title>Client Share</title>

 <link href="{{ mix('css/main_compiled_v2.css') }}" rel="stylesheet">
 <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.min.css">
<script>
 baseurl = "{{ env('APP_URL') }}";
 current_space_id = session_space_id = "{!!Session::get('space_info')['id']!!}";
 current_space_name = "{!!Session::get('space_info')['share_name']!!}";
 page_time = {{strtotime(Carbon\Carbon::now())}};
 var file_view_count = "{{ url('/file_view_count',[],env('HTTPS_ENABLE', true)) }}";
 var save_quick_links = "{{ url('/save_quick_links',[],env('HTTPS_ENABLE', true)) }}";
 var get_quick_links = "{{ url('/get_quick_links',[],env('HTTPS_ENABLE', true)) }}";
 var community_member = "{{ url('/community_member',[],env('HTTPS_ENABLE', true)) }}";
 var logged_in_user_role = 'user';
 var loggin_user_id = "{{\Auth::user()->id}}";
 var loggin_user_name = "{{\Auth::user()->fullname}}";
 logged_in_user_role = '@php echo $is_logged_in_user_admin ? "admin" : "user"; @endphp';
 var is_logged_in_user_admin =  {{$is_logged_in_user_admin}};
 var share_setup_steps =  "{!!Session::get('space_info')['share_setup_steps']!!}";
</script>

<!-- Post Text Area end -->
<link rel="icon" href="{{ env('APP_URL') }}/images/CSProfileImg.png" sizes="32x32" />


  
    @if(getenv('APP_ENV') == 'production')
    <script type="text/javascript">
      window.heap=window.heap||[],heap.load=function(e,t){window.heap.appid=e,window.heap.config=t=t||{};var r=t.forceSSL||"https:"===document.location.protocol,a=document.createElement("script");a.type="text/javascript",a.async=!0,a.src=(r?"https:":"http:")+"//cdn.heapanalytics.com/js/heap-"+e+".js";var n=document.getElementsByTagName("script")[0];n.parentNode.insertBefore(a,n);for(var o=function(e){return function(){heap.push([e].concat(Array.prototype.slice.call(arguments,0)))}},p=["addEventProperties","addUserProperties","clearEventProperties","identify","removeEventProperty","setEventProperties","track","unsetEventProperty"],c=0;c<p.length;c++)heap[p[c]]=o(p[c])};
         heap.load("2650017777");
    </script>
    @endif