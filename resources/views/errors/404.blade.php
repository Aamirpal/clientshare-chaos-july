<!DOCTYPE html>
<html>
    <head>
     @if( isset($message) )
      <title>Client Share</title>
       @else
      <title>Be right back.</title>
        @endif
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

        <link rel="shortcut icon" type="image/png" href='{{ url("/",[],env("APP_ENV")!="local") }}/images/CSProfileImg.png'/>
        <link href="https://fonts.googleapis.com/css?family=Mada:300,400,500,600,700,900" rel="stylesheet">

        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                color: #B0BEC5;
                display: table;
                font-weight: 100;
                font-family: 'Mada', sans-serif;
                background: url('{{ url("/",[],env("APP_ENV")!="local") }}/images/login-bg.jpg');
                background-size: cover;
                background-repeat: no-repeat; 
                background-color: #212121;
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 72px;
                margin-bottom: 40px;
                padding: 10px;
            }
            a {
                    color: #fff !important;
                    text-decoration: none;
    background-color: #0d47a1;
    border-color: transparent;
    display: inline-block;
    margin-bottom: 0;
                    font-weight: normal;
    text-align: center;
    vertical-align: middle;
    -ms-touch-action: manipulation;
    touch-action: manipulation;
    cursor: pointer;
    padding: 6px 12px;
    font-size: 15px;
    line-height: 1.42857143;
    border-radius: 2px;
    box-shadow: 0px 2px 4px 0px rgba(0,0,0,0.16);
    border-radius: 2px;
    padding-left: 33px;
    padding-right: 33px;
    text-transform: uppercase;
    display: block;
    width: 74px;
    margin: auto;
            }
            a.big-bluelinks {
                    width: auto;
    background: transparent;
    color: #ffffff !important;
    text-transform: lowercase;
    font-weight: normal;
        display: inline-block;
    padding: 0;
    font-size: 17px;
    margin-top: -5px;
            }
            h1  {
                margin-bottom: 0;
                font-weight: 600;
            }
            p {
                font-size: 17px;
    margin-bottom: 25px;
    margin-top: 2px;
    font-weight: normal;
    color: #fff;
            }
             @media screen and (max-width: 399px) {
                .title img {
                    width: 100%;
                }
             }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">
                    <img src='{{ url("/",[],env("APP_ENV")!="local") }}/images/ClientShare Branding_ClientShare_Logo_wideTransparent_2 copy.png'>
                    @if( isset($message) )
                     <p>{!! $message !!}</p>
                    @else
                    <p>Hi, it looks like the page you are trying to view is no longer available.<br/>Please click here to login. </p>
                    @endif

                    @if( !isset($login_btn) || (isset($login_btn) && $login_btn) )
                    <a href='{{ url("/",[],env("APP_ENV")!="local") }}' class="btn btn-primary">Login</a>
                    @endif
                </div>
            </div>
        </div>
    </body>
</html>
