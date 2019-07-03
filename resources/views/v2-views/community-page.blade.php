@extends('layouts.layoutsV2.master')
@section('styles')
<link rel="stylesheet" href="<?php echo e(url('css/sweetalert2(6.6.9).min.css')); ?>">
@endsection
@section('content')
<div class="container-fluid">
    <div class="main-container" id="community_members"></div>
</div>
@include('v2-views/setting/invite_colleague', ['data' => Session::get('space_info')])
@endsection
@section('scripts')
<script src="{{ url('js/setting_v2.js?q='.env('CACHE_COUNTER', '500'),[],env('HTTPS_ENABLE', true)) }}"></script>
<script src="{{ url('js/custom/v2/invite.js?q='.env('CACHE_COUNTER', '500'),[],env('HTTPS_ENABLE', true)) }}"></script>
<script src="{{ url('js/sweetalert2(6.6.9).min.js') }}"></script>
@endsection