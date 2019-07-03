<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">
   <head>
      @php
      $ssl = false;
      if(env('APP_ENV')!='local')
      $ssl = true;
      @endphp
      <meta http-equiv="Content-Type" content="application/octet-stream; charset=utf-8; Content-Transfer-Encoding=Binary;" />
      <link href="https://fonts.googleapis.com/css?family=Lato:300,400,400i,700,700i" rel="stylesheet">
      <title>ClientShare</title>
      <link rel="stylesheet" href="{{ url('css/style.css?q='.env('CACHE_COUNTER', '500'),[],$ssl) }}">
      <script src="{{ url('js/jquery.min.js',[],$ssl) }}"></script>
      <script rel="text/javascript" src="{{ url('js/custom/generic.js?q='.env('CACHE_COUNTER', '500'),[],env('HTTPS_ENABLE', true)) }}"></script>
      <script rel="text/javascript" src="{{ url('js/custom/logger.js?q='.env('CACHE_COUNTER', '500'),[],env('HTTPS_ENABLE', true)) }}"></script>
      <link rel="stylesheet" href="{{ url('css/morris.css',[],$ssl) }}">
      <link rel="stylesheet" href="{{ url('css/graph.css',[],$ssl) }}">
      <script src="{{ url('js/raphael-min.js',[],$ssl) }}"></script>
      <script src="{{ url('js/morris.min.js',[],$ssl) }}"></script>
      <link href="https://fonts.googleapis.com/css?family=Lato:400,700" rel="stylesheet">
      <link rel="icon" href="{{ url('/',[],$ssl) }}/images/CSProfileImg.png" sizes="32x32" />
      <script type="text/javascript" src="{{ url('js/picker.min.js',[],$ssl) }}"></script>
      <script rel="text/javascript" src="{{ url('js/custom/graph.js?q='.env('CACHE_COUNTER'),[],env('HTTPS_ENABLE', true)) }}"></script>
      <script>
         var tigger_graph_change_xhr;
         var graph_months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];   
         post_interaction_global = JSON.parse('{!! json_encode($data["post_interaction"]??'') !!}');
         total_posts = JSON.parse('{!! json_encode($data["total_posts"]??'') !!}');
         community_global = JSON.parse('{!! json_encode($data["currentMonthMembers"]??'') !!}');
         nps_global = JSON.parse('{!! json_encode($data["nps"]??'') !!}');
         csi_global = JSON.parse('{!! json_encode($data["csi"]??'') !!}');
         popup_list_limit=8;
         selected_month = {!! $data['month']!!};
      </script>
      <style>
         @font-face {
         font-family: 'Lato', sans-serif;
         font-style: normal;
         font-weight: normal;
         src: url(https://fonts.googleapis.com/css?family=Lato:400,400i,700) format('truetype');
         }
         table {font-family: 'Lato', sans-serif; border-collapse:separate;}
         table tr, table td {padding: 0;}
         tr    { page-break-inside:avoid; page-break-after:auto }
        td    { page-break-inside:avoid; page-break-after:auto }
        body { padding: 0; }

        .wrap {border: 1px solid #e0e0e0; margin: auto; width: 832px; float: left;}
        .border-wrap {border: 1px solid #e0e0e0; width: 832px; float: left;}
        .top-heading {background: #eee; padding-top: 16px; padding-bottom: 20px; text-align: center;}
        .pdf-content-wrap {padding: 20px 20px; background: #F5F5F5; float: left; width: 792px;}
        .pdf-content {padding-top:30px;float: left; width: 100%; page-break-inside:avoid; page-break-after:auto; margin-bottom: 40px; clear: both;}
        .graph-box {background: #fff; float: left;  width: 100%}
        .graph-box p {text-align: center; margin-top: 30px}
        .custom_graph_class tspan,
        text.custom_graph_class { display: inline !important;}
      </style>
      <?php 
         ?>
         <body style="padding: 0; margin: 0; background: #fff;">
         <div class="main">
            <div class="border-wrap">
               <div class="top-heading">
                  {{date("F", mktime(0, 0, 0, $data['month'], 1))}} Analytics report
               </div>
               <div class="pdf-content-wrap">
                  <div class="pdf-content">
                     <div class="graph-box">
                        <p>Client Share Index</p>
                        <div class="space_activities">
                          @include('analytics/graphs/common_graph', ['view_data'=>['data_id'=>'csi_view_data','csi_view_data' => $data["csi"]??'', 'graph_legends_class'=>'csi_graph_legends', 'graph_div_id'=>'csi_graph_div_id' ]] )</div>
                     </div>
                  </div>
                  <div class="pdf-content" >
                     <div class="graph-box">
                        <p>Posts</p>
                        <div >@include('analytics/graphs/common_graph', ['view_data'=>['data_id'=>'post_view_data','post_view_data' => $data["total_posts"]??'', 'graph_legends_class'=>'post_global_graph_legends', 'graph_div_id'=>'post_global_graph_div_id' ]] )</div>
                     </div>
                  </div>
                  <div class="pdf-content">
                     <div class="graph-box">
                        <p>Posts Interaction</p>
                        <div class="postintraction_global">@include('analytics/graphs/common_graph', ['view_data'=>['data_id'=>'post_interaction_view_data','post_interaction_view_data' => $data["post_interaction"]??'', 'graph_legends_class'=>'post_interaction_graph_legends', 'graph_div_id'=>'post_interaction_graph_div_id' ]] )</div>
                     </div>
                  </div>
                  <div class="pdf-content" >
                     <div class="graph-box">
                        <p>Community</p>
                        <div>@include('analytics/graphs/common_graph', ['view_data'=>['data_id'=>'community_view_data','community_view_data' => $data["currentMonthMembers"]??'', 'graph_legends_class'=>'community_graph_legends', 'graph_div_id'=>'community_graph_div_id' ]] )</div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </body>
</html>