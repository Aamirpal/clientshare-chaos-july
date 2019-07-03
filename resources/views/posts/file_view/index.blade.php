@inject('post_controller', 'App\Http\Controllers\PostController')
@extends(session()->get('layout'))
@section('content')
<link rel="stylesheet" type="text/css" href="{{ url('/css/daterangepicker.css') }}">
<link rel="stylesheet" type="text/css" href="{{ url('/css/bootstrap-multiselect_v0_9_15.css') }}">

	<div class="container-fluid">
		<div class="files_panel">
			<div class="file_filter_block">		
				@include('posts.file_view.filters')
			</div>
			<div class="file_listing_block">
				@include('posts.file_view.file_view')				
			</div>
		</div>
	</div>

@include('generic.file_viewer')
<script rel="text/javascript" src="{{ url('js/custom/post_file_view.js?q='.env('CACHE_COUNTER', rand()),[],env('SSL', true)) }}"></script>
<script rel="text/javascript" src="{{ url('js/custom/file_viewer.js?q='.env('CACHE_COUNTER', rand()),[],env('SSL', true)) }}"></script>

<script rel="text/javascript" src="{{url('js/bootstrap-multiselect_V0_9_15.js')}}"></script>
<script rel="text/javascript" src="{{url('js/moment.min.js')}}"></script>
<script rel="text/javascript" src="{{url('js/daterangepicker.js')}}"></script>
@endsection