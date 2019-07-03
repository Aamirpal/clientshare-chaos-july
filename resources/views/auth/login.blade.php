@extends('layouts.login')
@section('content')
<?php
   $uuToken = $spToken = null;
   if(isset($_GET['_userToken']) && $_GET['_userToken']!=null  && isset($_GET['_shareToken']) && $_GET['_shareToken']!=null){
      $uuToken = $_GET['_userToken'];
      $spToken = $_GET['_shareToken'];
   }
   
   $ssl = (env('APP_ENV')!='local') ? true : false;
   
   if (isset($email_pre) && $email_pre!='') {
     $value = $email_pre;
   } else {
     $value=old('email');
   }
   
   /*** Make login email field read only for alert notification***/
   if(isset($alert_mail) && $alert_mail !='') {
     $readonly="readonly";
   } else {
     $readonly="";
   }
   ?>
  <form class="" role="form" method="POST" action="{{ url('/login',[],$ssl) }}" novalidate >
     {{ csrf_field() }}
     <div class="form-group{{ $errors->has('email') ? ' has-error' : ' ' }}">
        @if(isset($readonly) && $readonly !='')
        <input class="form-control  abcc" type="email" id="email" name="email" value="{{ $value }}" placeholder="Email address" {{$readonly}} />
        @else
        <input class="form-control  abcc" type="email" id="email" name="email" value="{{ $value }}" placeholder="Email address"  />
        @endif
        @if ($errors->has('email'))
        <span class="error-msg text-left">
        {{ $errors->first('email') }}
        </span>
        @endif
     </div>
     <div class="form-group{{ $errors->has('password') ? ' has-error' : ' ' }}{{ $errors->has('email') ? ' has-error' : ' ' }}">
        <input class="form-control" type="password" id="password" name="password" value="{{ old('password') }}" placeholder="Password" />
        @if ($errors->has('password'))
        <span class="error-msg text-left">
        {{ $errors->first('password') }}
        </span>
        @endif
     </div>
     <input type="hidden" name="uuToken" value="{{$uuToken}}">
     <input type="hidden" name="spToken" value="{{$spToken}}">
     <div class="bluelinks-wrap text-left">  <a href="{{ url('/password/reset',[],$ssl) }}" class="bluelinks">Forgot your password?</a>
        @if(Session::has('error_login'))
        <span style="display: block;" class="error-msg text-left"> {{Session::get('error_login')}} </span> @endif
     </div>
     <button id="show-sent" type="submit" class="btn btn-primary">Sign in</button>
     <div class="bluelinks-wrap"><a href="http://myclientshare.com/privacy/" target="_blank" class="bluelinks">Terms and conditions</a></div>
  </form>
  
@endsection