@extends('layouts.layoutsV2.master')
@section('content')
<link rel="stylesheet" type="text/css" href="{{ url('/css/daterangepicker.css') }}">
<link rel="stylesheet" type="text/css" href="{{ url('/css/bootstrap-multiselect_v0_9_15.css') }}">

	<div class="container-fluid">
		<div class="main-container">
			<div class="files_panel">
				<div class="file_filter_block">		
					@include('v2-views.file_view.filters')
				</div>
				<div class="file_listing_block">
					<div class="file-heading">
						<h2>Files</h2>
					</div>
					@include('v2-views.file_view.file_view')				
				</div>
			</div>
		</div>
	</div>

@include('v2-views.generic.file_viewer')
@endsection
@section('scripts')
	<script rel="text/javascript" src="{{ url('js/custom/v2/post_file_view.js?q='.env('CACHE_COUNTER', rand()),[],env('SSL', true)) }}"></script>
	<script rel="text/javascript" src="{{ url('js/custom/v2/file_viewer.js?q='.env('CACHE_COUNTER', rand()),[],env('SSL', true)) }}"></script>
	<script rel="text/javascript" src="{{url('js/bootstrap-multiselect_V0_9_15.js')}}"></script>
	<script rel="text/javascript" src="{{url('js/moment.min.js')}}"></script>
	<script rel="text/javascript" src="{{url('js/daterangepicker.js')}}"></script>
@endsection