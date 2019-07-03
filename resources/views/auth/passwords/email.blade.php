@extends('layouts.login')
<!-- Main Content -->
@section('content')
 <?php
     $ssl = false;
   if(env('APP_ENV')!='local')
     $ssl = true;
    ?>

    <p class="reset-desc text-left">Enter the email address associated with your account, and we’ll email you a link to reset your password.</p>
    <form class="" role="form" method="POST" action="{{ url('/password/email',[],$ssl) }}" novalidate>
    {{ csrf_field() }}


        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}" >
            <input id="email" type="email" class="form-control reset_eamil" name="email" value="{{ old('email') }}" placeholder="Email address">

            @if ($errors->has('email'))
          <span class="error-msg text-left">
              {{ $errors->first('email') }}
          </span>
            @endif
        </div>
        <button type="submit" id="show-sent" class="btn btn-primary">Send Reset Link</button>
           <div class="bluelinks-wrap">    <a href="{{ url('/dashboard') }}" class="bluelinks">Cancel</a></div>

    </form>
    <script type="text/javascript">
      
   
     $(function(){
         // reset email field to lowercase
      $('.reset_eamil').on('keypress, keyup',function(){
          $(this).val($(this).val().toLowerCase());
       });
        
     })

    </script>
@endsection
 