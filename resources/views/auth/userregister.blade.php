@extends('layouts.login')
@section('content')
   <?php
     $ssl = false;
   if(env('APP_ENV')!='local')
     $ssl = true;
    ?>
    <form class="user_register_from" role="form" method="POST" action="{{ url('/updateregisteruser',[],$ssl) }}
    " novalidate>
            {{ csrf_field() }}
            <div class="form-group{{ $errors->has('firstname') ? ' has-error' : ' ' }}">
                <input class="form-control" type="text" id="firstname" name="firstname" value="<?php echo $user[0]['first_name'];?>" placeholder="First Name"/>
                <span class="error-msg text-left error-block firstname_error"></span>
                @if ($errors->has('firstname'))
                <span class="error-msg text-left">
                    {{ $errors->first('firstname') }}
                </span>
            @endif

            </div>
            <div class="form-group{{ $errors->has('lastname') ? ' has-error' : ' ' }}">
                <input class="form-control" type="text" id="lastname" name="lastname" value="<?php echo $user[0]['last_name'];?>" placeholder="Last Name"/>
                <span class="error-msg text-left error-block lastname_error"></span>
                @if ($errors->has('lastname'))
                <span class="error-msg text-left">
                    {{ $errors->first('lastname') }}
                </span>
            @endif

            </div>
           
            <div class="form-group{{ $errors->has('email') ? ' has-error' : ' ' }}">
                <input class="form-control" type="email" id="email" name="email" value="<?php echo $user[0]['email'];?>"  placeholder="Email" readonly/>
                 <span id='register_email_error' class="error-msg text-left error-block "></span>
                 <span id='register_space_id_error' class="error-msg text-left error-block "></span>
                @if ($errors->has('email'))
                <span class="error-msg text-left">
                    {{ $errors->first('email') }}
                </span>
            @endif

            </div>
            <div class="form-group{{ $errors->has('password') ? ' has-error' : ' ' }}">
                <input class="form-control" type="password" id="password" name="password" value="{{ old('password') }}" placeholder="Create new password"/>
                 <span class="error-msg text-left error-block password_error"></span>
                @if ($errors->has('password'))
                    <span class="error-msg text-left">
                        {{ $errors->first('password') }}
                    </span>
                @endif
            </div>
            <input type="hidden" name="id" value="<?php echo $user[0]['id'];?>">
            <input type="hidden" name="shareid" value="{{ $shareid }}">
            <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : ' ' }}">
                <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" value="" placeholder="Confirm new password"/>
                <span class="error-msg text-left error-block confirm_password_error"></span>
                @if ($errors->has('password_confirmation'))
                    <span class="error-msg text-left">
                        {{ $errors->first('password_confirmation') }}
                    </span>
                @endif
            </div>

            <div class="form-group verify-code" style="display:none;">
                <input class="form-control" type="password" name="verify_code"  placeholder="Enter your verification code here">
                <span class="error-msg text-left error-block verify-code-error"></span>                
                <span class="error-msg text-left"></span>
            </div>

            <div class="success-box login_error_message timeout-message registration-verification" style="display:none;">
             <p class="text-center"></p>
           </div>

            <button type="button" class="btn btn-primary verify-user-details">Verify</button>
            <button id="show-sent" type="submit" style="display:none;" class="btn btn-primary user_register_form_submit">Register</button>

             
            <span class="check_box_error error-msg" style="display:block; margin-bottom: 16px;">Please agree to the Terms & Conditions. </span>
             <div class="term-check">
             <input type="checkbox" id="terms" style="display: none;">
                 <label for="terms" class="">
                   <img src="{{env('APP_URL')}}/images/check.png" alt="" class="checkd">
                    <img src="{{env('APP_URL')}}/images/check1.png" alt="" class="uncheck">
                 </label>
            
             <span class="terms">I agree to the </span>
             <a href="http://myclientshare.com/privacy/" target="_blank" class="terms">Terms & Conditions</a>
           </div>
        </form>
@endsection