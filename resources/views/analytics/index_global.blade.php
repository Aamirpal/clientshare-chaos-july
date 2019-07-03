@extends(Auth::user()->user_type_id == 1 ? 'layouts/analytics_share' : 'layouts/global_analytics_header')
@section('content')
@php
$ssl = false;
if(env('APP_ENV')!='local')
$ssl = true;
@endphp
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
  date_filter = JSON.parse('{!! json_encode($data["date_filter"]) !!}');
  selected_month = {!! $data['month']!!};
  popup_list_limit = 8;
</script>
<input type='hidden' id='user_email' value="{{ Auth::user()->email }}">
<form class="tigger_graph_change_form">
  {{csrf_field()}}
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
        <li class="analytics-down-arrow text-center">
          Your Client Shares
          <span class="down-month">
          <img src="{{url('/',[],$ssl)}}/images/ic_arrow_drop_down.svg" alt="">
          </span>
        </li>
        <li>
          <div class="panel panel-default">
            <div class="orange">
              <h4 class="panel-title">
                <input type="checkbox" id="select_all_share" class="tigger_graph_change">
                <label for="select_all_share" style=""><span style="background: #424242; border-color: #424242;"><i class="fa fa-check" aria-hidden="true"></i></span>Select all Client Shares ({{config('constants.ANALYTIC.graph_selection_limit')}} maximum)</label>
                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
                <img src="{{url('/',[],$ssl)}}/images/ic_expand_more_light.svg" alt="">
                </a>
              </h4>
            </div>
            <div id="collapseOne" class="panel-collapse collapse">
              <div class="panel-body">
                <div class="form-group">
                  <input type="checkbox" id="select_all_share_buyer" class="tigger_graph_change">
                  <label for="select_all_share_buyer" style=""><span style="background: #424242; border-color: #424242;"><i class="fa fa-check" aria-hidden="true"></i></span>Select all Buyer ({{config('constants.ANALYTIC.graph_selection_limit')}} maximum)</label>
                </div>
                <div class="form-group">
                  <input type="checkbox" id="select_all_share_seller" class="tigger_graph_change">
                  <label for="select_all_share_seller" style=""><span style="background: #424242; border-color: #424242;"><i class="fa fa-check" aria-hidden="true"></i></span>Select all Seller ({{config('constants.ANALYTIC.graph_selection_limit')}} maximum)</label>
                </div>
              </div>
            </div>
          </div>
        </li>
        <!-- Gerating share list start -->
        @foreach($data['user_shares'] as $key => $value)
        <li>
          <div class="panel panel-default">
            <div class="panel-heading">
              <h4 class="panel-title">
                @php
                  $checked = $value['id'] == $data['select_share_id']?'checked':'';
                @endphp
                <input {{$checked}} type="checkbox" class="tigger_graph_change share_main_cb" id="{{ $value['id'].$key }}" name="Shares[{{ $value['id'] }}][all]" data-share-id={{ $value['id'] }}  >
                <label for="{{ $value['id'].$key }}" style=""><span style="background: #424242; border-color: #424242;"><i class="fa fa-check" aria-hidden="true"></i></span>{{ $value['share_name'] }}</label>
                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapse{{ $value['id'].$key }}">
                <img src="{{url('/',[],$ssl)}}/images/ic_expand_more_light.svg" alt="">
                </a>
                <a role="menuitem" class="single-share-report export-detail" tabindex="-1" href="#" data-toggle="modal" data-target="#"><span><img src="{{url('/',[],$ssl)}}/images/ic_file_download_grey.svg"></span>
                <input type='hidden' name="Shares[{{ $value['id'] }}][created_at]" id='share_created_at' value="{{$value['created_at']}}">
                <input type='hidden' name="Shares[{{ $value['id'] }}][share_name]" id='share_created_at' value="{{$value['share_name']}}">
                <input type='hidden' id='share_id' value="{{$value['id']}}">
                <input type='hidden' id='share_name' value="{{ $value['share_name'] }}">
              </a>
                
              </h4>
            </div>
            <div id="collapse{{ $value['id'].$key }}" class="panel-collapse collapse">
              <div class="panel-body">
                <div class="form-group">
                  <input type="checkbox" class="tigger_graph_change share_buyer" id="buyer{{ $value['id'].$key }}" name="Shares[{{ $value['id'] }}][company][{{ $value['BuyerName']['id'] }}]">
                  <label for="buyer{{ $value['id'].$key }}" style=""><span style="background: #424242; border-color: #424242;"><i class="fa fa-check" aria-hidden="true"></i></span>{{ $value['BuyerName']['company_name'] }}</label>
                </div>
                <div class="form-group">
                  <input type="checkbox" class="tigger_graph_change share_seller" id="seller{{ $value['id'].$key }}" name="Shares[{{ $value['id'] }}][company][{{ $value['SellerName']['id'] }}]">
                  <label for="seller{{ $value['id'].$key }}" style=""><span style="background: #424242; border-color: #424242;"><i class="fa fa-check" aria-hidden="true"></i></span>{{ $value['SellerName']['company_name'] }}</label>
                </div>
              </div>
            </div>
          </div>
        </li>
        @endforeach
        <!-- Gerating share list end -->
      </ul>
    </div>
  </div>
  <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12 settings_content_wrap">
    <div class="box">
      <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="analytics">
          <div class="heading_wrap">
            <select name="year" id="ex-events" class="tigger_graph_change year_filter" name="year">
            @foreach(array_reverse($data['date_filter'], true) as $key => $value)
            @if(Carbon\Carbon::now()->year == $key ) @php $selected='selected'; @endphp @else @php $selected=''; @endphp @endif
            <option {{$selected??''}} value={{$key}}>{{$key}}</option>
            @endforeach
            </select>
            <select id="ex-eventss" class="analytics-month tigger_graph_change month_filter" name="month">
            @foreach($data['date_filter'][Carbon\Carbon::now()->year] as $key => $value)
            @if(Carbon\Carbon::now()->month == $value ) @php $selected='selected'; @endphp @else @php $selected=''; @endphp @endif
            <option {{$selected??''}} value="{{$value}}">{{date("F",mktime(0,0,0,$value,1,1901))}}</option>
            @endforeach
            </select>
            <div class="dropdown right">
              <button class="btn btn-primary dropdown-toggle right" type="button" id="analytics-download-dropdown" data-toggle="dropdown"><span><img src="{{ asset('/images/ic_file_download_white.svg', env('SECURE_COOKIES', true) ) }}"></span>Download</button>
              <ul class="dropdown-menu" role="menu" aria-labelledby="analytics-download-dropdown">
                <li role="presentation"><a class="download_pdf_trigger" role="menuitem" tabindex="-1" href="javascript:void(0)">Download report as PDF</a></li>
                <li role="presentation"><a role="menuitem" class="excel_download_all" tabindex="-1" href="javascript:void(0)" data-toggle="modal" data-target="#analytics_email_popup">Export all data (.xslx)</a></li>
              </ul>
            </div>
          </div>
          <div class="tab-inner-content analytics-column">
            <div class="tab-content">
              <!-- CSI start -->
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="analytics-card">
                  <div class="form-submit-loader graph_loader" style="display:none"><span></span></div>
                  <div class="top-head">
                    <p>Client Share Index</p>
                  </div>
                  <div class="space_activities">
                    @include('analytics/graphs/common_graph', ['view_data'=>['data_id'=>'csi_view_data','csi_view_data' => $data["csi"]??'', 'graph_legends_class'=>'csi_graph_legends', 'graph_div_id'=>'csi_graph_div_id' ]] )</div>
                </div>
              </div>
              <!-- CSI end -->
                <!-- Posts graph start  -->
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="analytics-card">
                    <div class="form-submit-loader graph_loader" style="display:none"><span></span></div>
                    <div class="top-head">
                      <p>Posts</p>
                    </div>
                    <div class="post_global">
                    @include('analytics/graphs/common_graph', ['view_data'=>['data_id'=>'post_view_data','post_view_data' => $data["total_posts"]??'', 'graph_legends_class'=>'post_global_graph_legends', 'graph_div_id'=>'post_global_graph_div_id' ]] )</div>
                  </div>
                </div>
              </div>
              <!-- Posts graph end  -->
              <!-- post intiraction start  -->
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="analytics-card">
                  <div class="form-submit-loader graph_loader" style="display:none"><span></span></div>
                  <div class="top-head">
                    <p>Posts Interaction</p>
                  </div>
                  <div class="postintraction_global">
                  @include('analytics/graphs/common_graph', ['view_data'=>['data_id'=>'post_interaction_view_data','post_interaction_view_data' => $data["post_interaction"]??'', 'graph_legends_class'=>'post_interaction_graph_legends', 'graph_div_id'=>'post_interaction_graph_div_id' ]] )</div>
                </div>
              </div>
              <!-- post intiraction end  -->
              <!-- Community start  -->
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="analytics-card">
                  <div class="form-submit-loader graph_loader" style="display:none"><span></span></div>
                  <div class="top-head">
                    <p>Community</p>
                  </div>
                  <div class="communitygraph">
                  @include('analytics/graphs/common_graph', ['view_data'=>['data_id'=>'community_view_data','community_view_data' => $data["currentMonthMembers"]??'', 'graph_legends_class'=>'community_graph_legends', 'graph_div_id'=>'community_graph_div_id' ]] )</div>
                </div>
              </div>
              <!-- Community end  -->
            <!-- analytics-column -->
          </div>
        </div>
      </div>
    </div>
  </div>
</form>
<!-- Popup modal for data-point -->
<div class="modal fade add_scroll" id="view-more-shares" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{ asset('/images/ic_highlight_removegray.svg', env('SECURE_COOKIES', true) ) }}" alt=""></button>
        <h4 class="modal-title data_point_modal_header"></h4>
      </div>
      <div class="modal-body">
        <ul class="data_point_modal_data"></ul>
      </div>
    </div>
  </div>
</div>
<!-- Popup modal for data-point -->
<!---modal for email popup -->
<div class="modal fade" id="analytics_email_popup" role="dialog">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Download xslx<span class="add_share_name"></span></h4>
      </div>
      <div class="modal-body">
        <p>A link to download all your analytics data in xlsx format has been emailed to {{Auth::user()->email}} and will be available shortly.</p>
      </div>
      <div class="modal-footer">
        <input type="hidden" class="access_token" value="{{ csrf_token() }}" />
        <input type="hidden" class="" value="">
        <input type="hidden" class="" value="">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!--end modal for email popup-->
<script rel="text/javascript" src="{{asset('js/custom/analytics.js?q='.env('CACHE_COUNTER', '500'), env('SECURE_COOKIES', true) ) }}"></script>
@endsection