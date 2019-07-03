@extends('layouts.error')
@section('content')
    <link rel="stylesheet" type="text/css" href="{{ env('APP_URL').'/css/error_page.css?q='.env('CACHE_COUNTER', rand()) }}">
    <div class="title">
        <img src='{{ env("APP_URL") }}/images/ClientShare Branding_ClientShare_Logo_wideTransparent_2 copy.png'>
        <br><br>
        <p>{{ strlen($exception->getMessage())?$exception->getMessage():'It looks like you need to be on a different network to access this share.' }}
        </p>
        <a href='{{ url("/",[],env("APP_ENV")!="local") }}' class="btn btn-primary">Retry</a>
    </div>
@endsection