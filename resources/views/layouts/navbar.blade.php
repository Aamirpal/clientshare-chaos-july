<div class="container">
<!-- Brand and toggle get grouped for better mobile display -->
	<div class="navbar-header">
		<button type="button" class="navbar-toggle collapsed" id="a_nav" data-toggle="collapse" data-target="#bs-example-navbar-collapse-2" aria-expanded="false">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>

		<ul class="navbar-brand right-nav-dropdown">
			<li class="dropdown">
				<a class="dropdown-toggle nav-btn" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
					<span class="nav-icon full-width text-center">
						<img class="img-responsive" alt="MyVitae" src="{{ env('APP_URL') }}/images/cssymbol.svg">
					</span>
					<span class="share-title full-width text-center">
						Shares
						<span class="down-arroww"><img src="{{ env('APP_URL') }}/images/Shape.svg"></span>
					</span>
					<span class="allnotifications" style="display:none;"></span>
				</a>
				<ul class="dropdown-menu company-dropdown" id="search_share_ul">
				    <input type="text" class="search_share form-control" id="search_share" onkeyup="searchShare()" placeholder="Search shares.." title="Type in a member">
					@php $req_url   =  request()->segment(1); @endphp
					@php $space_value=Session::get('user_spases_space_user'); @endphp
					@if(isset($space_value))
					@foreach( $space_value as $space_user_info )
					@if( strpos(url()->current(), $space_user_info['share']['id']) )
					<li class="active" value="{{ $space_user_info['share']['share_name'] }}"><a href="{{ url('/clientshare/'.$space_user_info['share']['id'],[],env('HTTPS_ENABLE', true)) }}">{{ $space_user_info['share']['share_name'] }} <span id="shareNoti_{{$space_user_info['share']['id']}}" style="display:none;"></span></a></li>
					@else
					<li value="{{ $space_user_info['share']['share_name'] }}"><a href="{{ url('/clientshare/'.$space_user_info['share']['id'],[],env('HTTPS_ENABLE', true)) }}">{{ $space_user_info['share']['share_name'] }} <span id="shareNoti_{{$space_user_info['share']['id']}}" style="display:none;"></span></a></li>
					@endif
					@endforeach
					@endif
				</ul>
			</li>
		</ul>
		@php $spacInfoValue = Session::get('space_info'); @endphp
		<div class="mobile-nav-col">
			<a class="navbar-brand nav-btn hidden-lg hidden-md hidden-sm" href="{{ env('APP_URL') }}/clientshare/{{  Session::get('space_info')['id'] }}"><img src="{{ env('APP_URL') }}/images/ic_home.svg"></a>
			<a href="#" class="navbar-brand dropdown-toggle nav-btn remove_badge hidden-lg hidden-md hidden-sm notification" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
				<div class="notification_count"></div>
				<img src="{{ env('APP_URL') }}/images/ic_notifications.svg" alt="" />
			</a>
			<a href="#" class=" dropdown-toggle nav-btn ic_search  navbar-brand dropdown-toggle nav-btn remove_badge hidden-lg hidden-md hidden-sm" >
				<img src="{{ env('APP_URL') }}/images/ic_search.svg" alt="">
			</a>
			<ul class="dropdown-menu notifications notificationdropdwon  " >
				<li class="header">Notifications</li>
				<li id="loading_li" class="header"><img src="{{env('APP_URL')}}/images/loading_bar1.gif"></li>
			</ul>
		</div>
		@php
		$spaceId = Session::get('space_info')['id'];
		$userId = Auth::user()->id;
		@endphp
		<div class="nav-search-wrap search-input-wrap hidden-lg hidden-md hidden-sm" style="display:none;">
			<input type="text" id="msearch-input" class="form-control" placeholder="Search.." onkeydown="downm()" onkeyup="upm('{{$spaceId}}','{{$userId}}')">
			<span class="search_close"><img src="{{ env('APP_URL') }}/images/ic_search_close.svg" alt=""></span>
		</div>
		<div class="search-dropdown-wrap1" style="display:none;">
			<ul class="search-dropdown" id="msearch-results">
			</ul>
		</div>
		<input type="hidden" class="notification_limit" value="0">
		<input type="hidden" class="notification_offset" value="0">
		<input type="hidden" class="notification_limit_more" value="0">
		<input type="hidden" class="notification_offset_more" value="0">
	</div>
	@include('layouts/mobile_navbar')
</div>
<script rel="text/javascript" src="{{ url('js/searchshare.js',[],env('HTTPS_ENABLE', true)) }}"></script>