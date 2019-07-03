@extends('layouts.layoutsV2.master')
@section('content')
<script rel="text/javascript" src="https://cdn.jsdelivr.net/npm/powerbi-client@2.6.5/dist/powerbi.min.js"></script>
<div class="container-fluid">
    <div class="main-container">
<div class="mid-content settings_page_content power-bi-main-container">
   <div class="row justify-content-center col py-3 px-lg-5">
      <div class="power-bi-report-list">
         <ul class="nav nav-tabs report-list" role="tablist">
         	@foreach($report_data as $report)
         		<li role="presentation" class="report_list-li" data-report-type="{{$report['report_type']}}" data-get-report="{{json_encode($report['metadata'])}}" ><a href="#user-management-tab" aria-controls="profile" role="tab" data-toggle="tab" class="user_manage side_tabs" aria-expanded="true">{{ucFirst($report['report_name'])}}</a></li>
            @endforeach
         </ul>
      </div>
   </div>
   <div class="powerBi-report-wrapper">
    <div class="report-inner-container" style="height: 800px;"> 
        <div id="reportContainer" class="report-container" style="height: 100%"></div>
    </div>
   </div>
</div>
</div>
</div>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script src="{{ url('js/custom/power_report.js?q='.env('CACHE_COUNTER', '500'),[],env('HTTPS_ENABLE', true)) }}"></script>
@endsection