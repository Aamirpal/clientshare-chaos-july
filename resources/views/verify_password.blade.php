@extends('layouts.login')
@section('content')
 <?php
     $ssl = false;
   if(env('APP_ENV')!='local')
     $ssl = true;
    ?>
  <div class="alert alert-success" id="timer"></div>
<form class="reset_password_form" role="form" method="POST" action="{{ url('/verify_code',[],$ssl) }}" novalidate >
    {{ csrf_field() }}
            <div class="form-group">
                <input class="form-control" type="text" id="code" name="code" value="" placeholder="Enter your verification code here"/>
            </div>
            @if(!empty($error_login))
            <span style="display: block;" class="error-msg text-left"> {{$error_login}} </span> @endif
            <button type="submit" id="show-sent" class="btn btn-primary" disabled="true">
                Verify Code
            </button>

     <div class="bluelinks-wrap">    <a href="{{ url('/login') }}" class="bluelinks">Cancel</a></div>
</form>
<script type="text/javascript">
    $("#code").on('change, paste input', function(){
        $("#show-sent").prop('disabled', false);
    });
</script>
@endsection
