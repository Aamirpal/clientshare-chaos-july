@extends('layouts.layoutsV2.master')
@section('content')

@php
$session_data  = Session::get('space_info');
@endphp
<div class="container-fluid">
    <div class="main-container">
        @if( $session_data['share_setup_steps'] < config('constants.MAX_SHARE_SETUP_STEPS')
        && ($share_progress_percentage < config('constants.HUNDRED_PERCENT')))
        @include('layouts.layoutsV2.include.onboarding-process',['session_data' => $session_data])
        <span data-toggle="modal" data-target="#welcome_tour" class="tour-trigger"></span>
        @endif
        <div  id="feed" class="feed-container @if($session_data['share_setup_steps'] < config('constants.MAX_SHARE_SETUP_STEPS')
              && $share_progress_percentage < config('constants.HUNDRED_PERCENT'))
              onboarding-design-fix @endif ">
        </div>
    </div>
</div>
@include('v2-views/setting/invite_colleague', ['data' => $session_data])
@endsection
@section('scripts')
@if( $session_data['share_setup_steps'] < config('constants.MAX_SHARE_SETUP_STEPS')
&& ($share_progress_percentage < config('constants.HUNDRED_PERCENT')))
<script rel="text/javascript" src="{{  asset('js/handle_bar.js?q='.env('CACHE_COUNTER', rand()))}}"></script>
<script rel="text/javascript" src="{{ asset('js/custom/circlos.js?q='.env('CACHE_COUNTER', rand())) }}"></script>
<script rel="text/javascript" src="{{ asset('js/custom/v2/welcome_tour.js?q='.env('CACHE_COUNTER', rand())) }}"></script>

<script rel="text/javascript" src="{{ asset('js/custom/v2/post_feature.js?q='.env('CACHE_COUNTER', rand())) }}"></script>
@endif
<script src="{{ url('js/setting_v2.js?q='.env('CACHE_COUNTER', '500'),[],env('HTTPS_ENABLE', true)) }}"></script>
<script src="{{ url('js/custom/v2/invite.js?q='.env('CACHE_COUNTER', '500'),[],env('HTTPS_ENABLE', true)) }}"></script>
@endsection
