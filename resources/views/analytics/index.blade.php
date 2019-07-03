@extends(isset($req_data['view_share']) ? 'layouts/analytics_share' : session()->get('layout'))
@section('content')
@php
$ssl = false;
if(env('APP_ENV')!='local')
$ssl = true;
$clientsharname=Session::get('space_info')['share_name'];
$currentDate=date('Y');   $check_year = request()->segment(4);
$check_month = request()->segment(3);
$check_month_nextpre = $check_month;
if(isset($check_year)){
$currentDate = $check_year;
}
else{
$currentDate = $currentDate;
}
$spacInfoValue = Session::get('space_info');
if($company==$spacInfoValue->toArray()['buyer_name']['id']){
$companyName = $spacInfoValue->toArray()['buyer_name']['company_name'];
}
elseif($company==$spacInfoValue->toArray()['seller_name']['id']){
$companyName = $spacInfoValue->toArray()['seller_name']['company_name'];
}else{
$companyName='';
}
@endphp
<link rel="stylesheet" href="{{ url('css/morris.css',[],$ssl) }}">
<link rel="stylesheet" href="{{ url('css/graph.css',[],$ssl) }}">
<script src="{{ url('js/raphael-min.js',[],$ssl) }}"></script>
<script src="{{ url('js/morris.min.js',[],$ssl) }}"></script>
<link href="https://fonts.googleapis.com/css?family=Lato:400,700" rel="stylesheet">
<link rel="icon" href="{{ url('/',[],$ssl) }}/images/CSProfileImg.png" sizes="32x32" />

<script>
   <?php
      $community_data = array_reverse($currentMonthMembers);

        $nps_data = $nps;

        $post_data = $totalPosts;
      ?>

   var graph_months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
   community_graph_data = JSON.parse('{!! json_encode($community_data) !!}');
   post_interaction = JSON.parse('{!! json_encode($post_interaction) !!}');
   post_interaction_global = JSON.parse('{!! json_encode($post_interaction_global) !!}');
   nps_graph_data = JSON.parse('{!! json_encode($nps_data) !!}');
   share_activity = JSON.parse('{!! json_encode($share_activity) !!}');
   post_graph_data = JSON.parse('{!! json_encode($post_data) !!}');
   feedback_graph_data = JSON.parse('{!! json_encode($feedback_graph) !!}');

</script>
<div class="col-lg-10 col-md-12 col-md-12 col-md-12 mid-content settings_page_content analytics_page">
   <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 settings_tabs_wrap">
      <div class="box">
         @php
         $startDate = Session::get('space_info')['created_at'];
         $startYear = date('Y',strtotime($startDate));
         $startMonth = date('m',strtotime($startDate));
         $smonth = date('m',strtotime($startDate));
         $syear = date('Y',strtotime($startDate));
         @endphp
         <ul class="nav nav-tabs year-tab" role="tablist">
            <li class="active text-center feedback-year">
               @if($startYear < $currentDate)
               <span class="last_year last-year"><img src="{{url('/',[],$ssl)}}/images/ic_chevron_left.svg" alt=""></span>
               @else
               <span class="last-year"><img src="{{url('/',[],$ssl)}}/images/ic_chevron_left.svg" alt=""></span>
               @endif
               <span class="curnt_year">{{$currentDate}}
               </span><span class="next_year"><img src="{{url('/',[],$ssl)}}/images/ic_chevron_right.svg" alt=""></span>
               <span class="down-month"><img src="{{url('/',[],$ssl)}}/images/ic_arrow_drop_down.svg" alt=""></span>
            </li>
            @if($startYear < $currentDate)
            @php $startMonth = 1; @endphp
            @php $check_month = $check_month; @endphp
            @else
            @php $startMonth = $startMonth; @endphp
            @php $check_month = $startMonth; @endphp
            @endif
            @for($m=$startMonth;$m<=12;$m++)
            @php
            $monthNum  = $m;
            $dateObj   = DateTime::createFromFormat('!m', $monthNum);
            $monthName = $dateObj->format('F');
            $curr_month = date('n');
            $curr_year =  date('Y');
            @endphp
            <li @if($monthNum == $selectedMonth) class="active month-class" getmonthnum="{{$monthNum}}" @endif role="presentation"><a href="{{env('APP_URL')}}/analytics/{{$spaceId}}/{{$m}}/{{$currentDate}}/{{$companyName}}@if(isset($req_data['view_share']))?view_share=true @endif">{{$monthName}}</a>
            </li>
            @if($currentDate == $curr_year && $m == $curr_month)
            @php break; @endphp
            @endif
            @endfor
         </ul>
      </div>
      <a href="{{env('APP_URL')}}/export_analytics/{{$spaceId}}/{{$selectedMonth}}/{{$selectedYear}}" class="blue-link export-detail">Export detailed data</a>
   </div>
   <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12 settings_content_wrap">
      <div class="box">
         <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="analytics">
               <div class="heading_wrap">
                  <h4 class="title col-lg-2 col-md-2 col-sm-2 col-xs-3">Analytics</h4>
                  <div class="community-tab-section community-tab-section col-lg-7 col-md-7 col-sm-7 col-xs-12">
                     <ul class="nav nav-tabs community-member-tabs" role="tablist1">
                        <li role="presentation" class="@if($company=='') active @endif"><a href="{{env('APP_URL')}}/analytics/{{$spaceId}}/{{$selectedMonth}}/{{$selectedYear}}@if(isset($req_data['view_share']))?view_share=true @endif" aria-controls="all-members-" role="tab-" data-toggle="tab-">All</a></li>
                        <li role="presentation" class="@if($company==$spacInfoValue->toArray()['seller_name']['id']) active @endif"> @if(isset($spacInfoValue))<a href="{{env('APP_URL')}}/analytics/{{$spaceId}}/{{$selectedMonth}}/{{$selectedYear}}/{{$spacInfoValue->toArray()['seller_name']['company_name']}}@if(isset($req_data['view_share']))?view_share=true @endif" aria-controls="company-one-" role="tab-" data-toggle="tab-">@endif
                           @if(isset($spacInfoValue)) {{$spacInfoValue->toArray()['seller_name']['company_name']}} @endif
                           </a>
                        </li>
                        <li role="presentation" class="@if($company==$spacInfoValue->toArray()['buyer_name']['id']) active @endif">@if(isset($spacInfoValue))<a href="{{env('APP_URL')}}/analytics/{{$spaceId}}/{{$selectedMonth}}/{{$selectedYear}}/{{ $spacInfoValue->toArray()['buyer_name']['company_name']}}@if(isset($req_data['view_share']))?view_share=true @endif" aria-controls="company-two-" role="tab-" data-toggle="tab-">@endif
                           @if(isset($spacInfoValue)){{ $spacInfoValue->toArray()['buyer_name']['company_name']}} @endif
                           </a>
                        </li>
                     </ul>
                  </div>
                  <!-- <a class="download-report col-lg-3 col-md-3 col-sm-3 col-xs-12 invite-btn" href="{{env('APP_URL')}}/analytics/{{$spaceId}}/{{$selectedMonth}}/{{$selectedYear}}/{{$companyName}}?download_pdf=true" class="invite-btn">DOWNLOAD REPORT <span><img src="{{url('/',[],$ssl)}}/images/ic_file_download.svg" alt=""></span></a> -->
               <a class="download-report col-lg-3 col-md-3 col-sm-3 col-xs-12 invite-btn" href="{{$download_pdf_url}}" class="invite-btn">DOWNLOAD REPORT <span><img src="{{url('/',[],$ssl)}}/images/ic_file_download.svg" alt=""></span></a>
               </div>
               <div class="tab-inner-content analytics-column">
                  <div class="tab-content">
                                     <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 nps_head right-card">
                        <div class="analytics-card">
                           <div class="top-head">
                              <p>Post</p>
                           </div>
                           <div class="mid-head">
                              <div class="mid-head">
                                 <h1 class="count">
                                    @if(sizeOfCustom($share_activity))
                                    {{$share_activity[sizeOfCustom($share_activity)-1]->value }}
                                    @else
                                    0
                                    @endif
                                 </h1>
                                 <p class="time">Score
                                    <a class="helpicon yo" data-toggle="activity-hover" title="" data-placement="bottom" data-content="The CSI Score shows how engaged the community is on this Client Share. It is calculated by measuring user engagement and is a great barometer for your relationship" data-trigger="hover"><i class="fa fa-question-circle"></i></a>
                                 </p>
                              </div>
                           </div>
                           <div>@include('analytics/graphs/postintraction_global')</div>
                        </div>
                        <!-- analytics-card -->
                     </div>
                     <div role="tabpanel" class="tab-pane active" id="all-tab">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 nps_head right-card">
                           <div class="analytics-card">
                              <div class="top-head">
                                 <p>Client Share Index</p>
                              </div>
                              <div class="mid-head">
                                 <div class="mid-head">
                                    <h1 class="count">
                                       @if(sizeOfCustom($share_activity))
                                       {{$share_activity[sizeOfCustom($share_activity)-1]->value }}
                                       @else
                                          0
                                       @endif
                                    </h1>
                                    <p class="time">CSI Score
                                       <a class="helpicon yo" data-toggle="activity-hover" title="" data-placement="bottom" data-content="The CSI Score shows how engaged the community is on this Client Share. It is calculated by measuring user engagement and is a great barometer for your relationship" data-trigger="hover"><i class="fa fa-question-circle"></i></a>
                                    </p>
                                 </div>
                              </div>
                              <div>@include('analytics/graphs/space_activities')</div>
                           </div>
                           <!-- analytics-card -->
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                           <div class="analytics-card">
                              <div class="top-head">
                                 <p>Post Interaction</p>
                              </div>
                              <div class="mid-head">
                                    <h1 class="count">
                                       @if(sizeOfCustom($post_interaction))
                                       {{$post_interaction[sizeOfCustom($post_interaction)-1]->value }}
                                       @else
                                          0
                                       @endif
                                    </h1>
                                    <p class="time">Views/Downloads</p>
                              </div>
                              <div>@include('analytics/graphs/postintraction')</div>
                           </div>
                           <!-- analytics-card -->
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                           <div class="analytics-card">
                              <div class="top-head">
                                 <p>Posts</p>
                              </div>
                              <div class="mid-head">
                                 <h1 class="count">
                                    @php  $currentMonthPost = (array)end($totalPosts);
                                    $dateObj   = DateTime::createFromFormat('!m', $selectedMonth);
                                    $monthName = $dateObj->format('M');
                                    @endphp
                                    @if(isset($currentMonthPost['value']) && $currentMonthPost['year'] == $selectedYear && $currentMonthPost['day']==$monthName)
                                    {{$currentMonthPost['value']}}
                                    @else {{0}}
                                    @endif
                                 </h1>
                                 <p class="time">Total posts</p>
                              </div>
                              <div>@include('analytics/graphs/postgraph')</div>
                           </div>
                           <!-- analytics-card -->
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                           <div class="analytics-card">
                              <div class="top-head">
                                 <p>Community</p>
                              </div>
                              <div class="mid-head">
                                 <div class="half">
                                    <h1 class="count">
                                       @php $currentMonthMember = (array)end($currentMonthMembers); @endphp
                                       <?php if(isset($currentMonthMember['value']) && $currentMonthMember['year'] == $selectedYear && $currentMonthMember['day']==$monthName){
                                          $current_months_members = $currentMonthMember['value'];
                                          //echo $currentMonthMember['value'];
                                          }else{
                                          $current_months_members=0;
                                          //echo 0;
                                          }
                                          if(isset($currentMonthMembers[0])){
                                          echo $currentMonthMembers[0]->value;
                                          }
                                          ?>
                                    </h1>
                                    <p class="time">Members</p>
                                 </div>
                                 <div class="half right-border">
                                    <h1 class="count">
                                       <span></span>
                                       @if(isset($currentMonthMembers[0]->value) && isset($currentMonthMembers[1]->value))
                                       @if($currentMonthMembers[0]->value - $currentMonthMembers[1]->value<0)
                                       <span><img src="{{url('/',[],$ssl)}}/images/negativeIcon.svg"></span>
                                       @elseif($currentMonthMembers[0]->value -$currentMonthMembers[1]->value>0)
                                       <span><img src="{{url('/',[],$ssl)}}/images/plusIcon2.svg"></span>
                                       @endif
                                       <!-- {{abs($current_months_members -$prevMonthMembers)}} -->
                                       {{ abs($currentMonthMembers[0]->value - $currentMonthMembers[1]->value) }}
                                       @else
                                       @if(isset($currentMonthMembers[0]))
                                       @if( $currentMonthMembers[0]->value >0)
                                       <span><img src="{{url('/',[],$ssl)}}/images/plusIcon2.svg"></span>
                                       @endif
                                       {{$currentMonthMembers[0]->value}}
                                       @endif
                                       @endif
                                    </h1>
                                    <p class="time">Monthly change</p>
                                 </div>
                              </div>
                              <div>@include('analytics/graphs/communitygraph')</div>
                           </div>
                           <!-- analytics-card -->
                        </div>
                         <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">      
                           <div class="analytics-card">  
                            @php
                            $feedbackOnOff =  app('App\Http\Controllers\FeedbackController')->feedbackStatus(Session::get('space_info')->id);
                            @endphp 
                            @if($feedbackOnOff['feedback_status']) 
                            @if( $feedback_status['current_month_eligible'] == true && ($feedback_count>0 || $log_get>0) )   
                              <div class="top-head">     
                                 <p>NPS</p>      
                              </div>      
                              <div class="mid-head"> 
                             <div class="half">
                        <h1 class="count">
                        @php 
                          $currentMonthNps = (array)end($nps);
                        @endphp
                        {{$currentMonthNps['value']}}
                        </h1>
                                 <p class="time">NPS Score</p>        
                                   </div>
                     <div class="half right-border">
                         <h1 class="count">@php $prev_month_nps = prev($nps);
                            $prev_month_np = (array)$prev_month_nps;
                          @endphp
                          <?php 
                            if(!isset($prev_month_np['value'])){
                              $prev_month_np['value'] = 0;
                            }  
                          ?>
                          @if($currentMonthNps['value'] > $prev_month_np['value'] ) <span style="color:green">+</span> @elseif($currentMonthNps['value'] < $prev_month_np['value']) <span style="color:red">-</span>@endif                         
                        @if($currentMonthNps['value'] == $prev_month_np['value']) 
                        <span>{{$currentMonthNps['value']}}</span> 
                        @else
                        <span><?php echo(abs($currentMonthNps['value'] - $prev_month_np['value']) ); ?></span>
                        @endif 
                        </h1>
                        <p class="time">Monthly change</p>
                     </div>
                  </div>  
                              <div>@include('analytics/graphs/feedbackgraphs')</div> 
                              @else
                              <div class="greyout">
                                    <p>You will see the overall feedback results from other members on {{ $feedback_status['days_left']+Carbon\Carbon::now()->day+1 }}{{Carbon\Carbon::parse($feedback_status['next_due'])->format('-M-Y')}}.</p>
                              </div>
                              @endif  
                              @else
                              <div class="greyout">
                                    <p>The Account Relationship Feedback Feature is currently turned off.</p>
                              </div>
                              @endif   
                           </div>      
                           <!-- analytics-card -->    
                        </div>      
                     
                     <div role="tabpanel" class="tab-pane" id="sefas">
                        
                     </div>
                     <div role="tabpanel" class="tab-pane" id="nustream">
                        
                     </div>
                  </div>
                  <!-- tab-content -->
               </div>
               <!-- analytics-column -->
            </div>
         </div>
      </div>
   </div>
</div>
<script>


   $(document).on("click", ".last_year", function() {
    var spliturl = window.location.href.split('?')[1];
    if(spliturl){
      var appendattr = "?"+spliturl;
    } else {
      var appendattr = "";
    }
     var cur_year = $(".curnt_year").html();   //alert(cur_year);
      var real_year =  '2016';
       var year = parseInt(cur_year) - 1 ;
     if(parseInt(year) >= parseInt(real_year)){
       $(".curnt_year").html(year);
       baseurl = "{{ url('/',[],$ssl) }}";
       var company ="@if($companyName!=''){{$companyName}}@endif";
       var spaceId = "{{$spaceId}}";
       if(company){
         window.location.href = baseurl+"/analytics/"+spaceId+"/"+@if($smonth > $curr_month && $syear+1 < $check_year )12 @else 12 @endif +"/"+year+"/"+company+appendattr;
        }else{
          window.location.href = baseurl+"/analytics/"+spaceId+"/"+@if($smonth > $curr_month && $syear+1 < $check_year )12 @else 12 @endif +"/"+year+appendattr;
        }
     }

    });
    $(document).on("click", ".next_year", function() {
       var spliturl = window.location.href.split('?')[1];
    if(spliturl){
      var appendattr = "?"+spliturl;
    } else{
      var appendattr = "";
    }
       var cur_year = $(".curnt_year").html();
       var real_year =  new Date().getFullYear();
       var  year = parseInt(cur_year) + 1 ;
       baseurl = "{{ url('/',[],$ssl) }}";
       var company ="@if($companyName!=''){{$companyName}}@endif";
       var spaceId = "{{$spaceId}}";
       if(parseInt(year) <= parseInt(real_year)){
         $(".curnt_year").html(year);
         if(company){
            window.location.href = baseurl+"/analytics/"+spaceId+"/"+@if($check_month > $curr_month && $check_year+1 == $curr_year ) {{$curr_month }} @else {{$check_month}} @endif+"/"+year+"/"+company+appendattr;
          }else{
             window.location.href = baseurl+"/analytics/"+spaceId+"/"+@if($check_month > $curr_month && $check_year+1 == $curr_year ) {{$curr_month }} @else {{$check_month}} @endif+"/"+year+appendattr;
          }
       }else{
        // $('.next_year').css("display","none");
       }
    });

    $(document).on("click", ".down-month, .curnt_year", function() {
        if($('.year-tab').hasClass('open-month')){
            $('.year-tab').removeClass('open-month');
        } else{
          $('.year-tab').addClass('open-month');
        }

    });


   $(document).ready(function() {
     if (navigator.userAgent.match(/(iPod|iPhone|iPad)/)) {
         $('.download-report').hide();
     }
   });

   $(function () {
     $(".yo").popover();
   });
</script>
@endsection
