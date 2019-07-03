@extends('layouts.error')
@section('content')
    <style>
        html, body {
            height: 100%;
        }
        body {
            background: url('{{ url("/",[],env("APP_ENV")!="local") }}/images/login-bg.jpg');    background-size: cover;
    background-position: center center;
    padding: 0;
    margin: 0;

        }
        .main-content{
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
        }

        p {
            font-size: 17px;
            margin-bottom: 25px;
            margin-top: 2px;
            font-weight: normal;
            color: #fff;
        }   
    </style>
    <div class="title">
        <img src='{{ url("/",[],env("APP_ENV")!="local") }}/images/ClientShare Branding_ClientShare_Logo_wideTransparent_2 copy.png'>
        <br><br>
        <p>{{ $message??'It looks like you need to be on a different network to access this share.' }}
        </p>
        <a href='{{ url("/",[],env("APP_ENV")!="local") }}' class="btn btn-primary">Retry</a>
    </div>
@endsection