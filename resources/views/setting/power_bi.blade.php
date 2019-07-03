@extends(session()->get('layout'))
@section('content')
<script rel="text/javascript" src="https://cdn.jsdelivr.net/npm/powerbi-client@2.6.5/dist/powerbi.min.js"></script>

<div class="col-lg-12 col-md-12 col-md-12 col-md-12 mid-content settings_page_content">
   <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
      <div class="box">
         <ul class="nav nav-tabs report-list" role="tablist">
            <li class="active feedback-year">Power-BI</li>
         	@foreach($report_data as $report)
         		<li role="presentation" class="report_list-li" data-report-type="{{$report['report_type']}}" data-get-report="{{json_encode($report['metadata'])}}" ><a href="#user-management-tab" aria-controls="profile" role="tab" data-toggle="tab" class="user_manage side_tabs" aria-expanded="true">{{ucFirst($report['report_name'])}}</a></li>
            @endforeach
         </ul>
      </div>
   </div>
   <div class="col-lg-10 col-md-10 col-sm-12 col-xs-12 container-fluid" style="height: 800px;">
   	<div id="reportContainer" style="height: 100%"></div>
   </div>
</div>
<script src="{{ url('js/custom/power_report.js?q='.env('CACHE_COUNTER', '500'),[],env('HTTPS_ENABLE', true)) }}"></script>
@endsection