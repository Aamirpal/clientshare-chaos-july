@extends('layouts.login')
@section('content')
 <?php
     $ssl = false;
   if(env('APP_ENV')!='local')
     $ssl = true;  
     $userEmail=base64_decode($email); 
    ?>
<form class="reset_password_form" role="form" method="POST" action="{{ url('/password/reset',[],$ssl) }}" novalidate >
  {{ csrf_field() }}
  <input type="hidden" name="token" value="{{ $token }}">
  <div class="form-group{{ $errors->has('email') ? ' has-error' : ' ' }}">
    <input class="form-control" type="email" id="email" name="email" value="{{ old('email', $userEmail) }}" placeholder="Email address"  readonly />
    <span class="error-msg text-left error-block email_error"></span>
    @if ($errors->has('email'))
    <span class="error-msg text-left">
        {{ $errors->first('email') }}
    </span>
    @endif
  </div>

  <div class="form-group{{ $errors->has('password') ? ' has-error' : ' ' }}">
    <input class="form-control" type="password" id="password" name="password" value="{{ old('password') }}"  placeholder="Password"/>
    <span class="error-msg text-left error-block password_error"></span>
    @if ($errors->has('password'))
      <span class="error-msg text-left">
        {{ $errors->first('password') }}
      </span>
    @endif
  </div>

  <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : ' ' }}">
    <input class="form-control" type="password" id="confirm_password" name="password_confirmation" value="{{ old('password') }}"  placeholder="Confirm Password"/>
    <span class="error-msg text-left error-block confirm_password_error"></span>
    @if ($errors->has('password'))
      <span class="error-msg text-left">
        {{ $errors->first('password') }}
      </span>
    @endif
  </div>

  <button type="submit" id="show-sent" class="btn btn-primary reset_pass">
    Reset Password
  </button>

  <div class="bluelinks-wrap">    
    <a href="{{ url('/dashboard') }}" class="bluelinks">Cancel</a>
  </div>
</form>
@endsection
