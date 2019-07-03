@extends('layouts.super_admin')
@section('content')
<h3> {{$users->first_name}} Profile</h3>
@if(Session::has('message')) 
  <div class="alert alert-info text-center">
    {{Session::get('message')}} 
  </div>
@endif

<form class="form-horizontal user_register_from" role="form" method="POST" action="{{ url('/updateprofile',[],env('HTTPS_ENABLE', true)) }}">
  {{ csrf_field() }}

  <div class="form-group{{ $errors->has('firstname') ? ' has-error' : '' }}">
    <label for="firstname" class="col-md-4 control-label">First Name</label>
    <div class="col-md-6">
      <input id="firstname" type="text" class="form-control" name="firstname" value="{{ $users->first_name }}" placeholder="First name"  autofocus>
      <span class="error-msg text-left error-block firstname_error"></span>
      @if ($errors->has('firstname'))
      <span class="help-block">
        <strong>{{ $errors->first('firstname') }}</strong>
      </span>
      @endif
    </div>
  </div>

  <div class="form-group{{ $errors->has('lastname') ? ' has-error' : '' }}">
    <label for="lastname" class="col-md-4 control-label">Last Name</label>

    <div class="col-md-6">
      <input id="lastname" type="text" class="form-control" name="lastname" value="{{ $users->last_name }}" placeholder="Last name" autofocus>
      <span class="error-msg text-left error-block lastname_error"></span>
      @if ($errors->has('lastname'))
      <span class="help-block">
        <strong>{{ $errors->first('lastname') }}</strong>
      </span>
      @endif
    </div>
  </div>

  <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
    <label for="email" class="col-md-4 control-label">E-Mail Address</label>

    <div class="col-md-6">
      <input id="email" type="email" class="form-control" name="email" value="{{ $users->email }}" readonly="readonly">

      @if ($errors->has('email'))
        <span class="help-block">
          <strong>{{ $errors->first('email') }}</strong>
        </span>
      @endif
    </div>
  </div>

  <input type="hidden" name="id" value="{{ $users->id }}">

  <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
    <label for="password" class="col-md-4 control-label">Password</label>
    <div class="col-md-6">
      <input id="password" type="password" class="form-control" name="password" placeholder="Default Password" >
      <span class="error-msg text-left error-block password_error"></span>
      @if ($errors->has('password'))
      <span class="help-block">
        <strong>{{ $errors->first('password') }}</strong>
      </span>
      @endif
    </div>
  </div>

  <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
    <label for="password-confirm" class="col-md-4 control-label">Confirm Password</label>
    <div class="col-md-6">
      <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" >
      <span class="error-msg text-left error-block confirm_password_error"></span>
      @if ($errors->has('password_confirmation'))
      <span class="help-block">
        <strong>{{ $errors->first('password_confirmation') }}</strong>
      </span>
      @endif
    </div>
  </div>

  <div class="form-group">
    <div class="col-md-6 col-md-offset-4">
      <button type="submit" class="btn btn-primary user_register_form_submit">
        Save
      </button>
    </div>
  </div>
</form>
@endsection
