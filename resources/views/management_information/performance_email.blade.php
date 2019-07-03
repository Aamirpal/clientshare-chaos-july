@extends('layouts.super_admin')
@section('content')
<div class="row">
  <div class="col-xs-12">
    <div class="edit-mail-form text-center">
      @if(session()->has('success'))
      <div class="col-xs-12">
        <div class="alert alert-success alert-dismissible edit-mail-form-box">
          <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
           {{ session()->get('success') }}
        </div>
      </div>
      @endif
      <form class="edit-mail-form-box" action="" method="POST" >
        {{ csrf_field() }}
        <div class="form-group row">
          <label for="email_to" class="col-sm-2 col-form-label">To:</label>
          <div class="col-sm-10">
            <input type="email" class="form-control" name="email_to" id="email_to" placeholder="Email" value="{{ old('email_to', $data['email_to'] ?? '') }}" required >
            @if ($errors->has('email_to'))
              <div class="error">{{ $errors->first('email_to') }}</div>
            @endif
          </div>
        </div>
        <div class="form-group row">
          <label for="email_cc" class="col-sm-2 col-form-label">cc:</label>
          <div class="col-sm-10">
            <input type="email" name="email_cc" class="form-control" id="email_cc" placeholder="Email" value="{{ old('email_cc', $data['email_cc'] ?? '') }}" >
            @if ($errors->has('email_cc'))
              <div class="error">{{ $errors->first('email_cc') }}</div>
            @endif
          </div>
        </div>
        <div class="form-group row">
          <label for="email_bcc" class="col-sm-2 col-form-label">bcc:</label>
          <div class="col-sm-10">
            <input type="email" name="email_bcc" class="form-control" id="email_bcc" placeholder="Email" value="{{ old('email_bcc', $data['email_bcc'] ?? '') }}" >
            @if ($errors->has('email_bcc'))
              <div class="error">{{ $errors->first('email_bcc') }}</div>
            @endif
          </div>
        </div>
        <div class="form-group row">
          <label for="email_subject" class="col-sm-2 col-form-label">Subject:</label>
          <div class="col-sm-10">
              <input type="text" name="email_subject" class="form-control" id="email_subject" placeholder="Subject" value="{{ old('email_subject', $data['email_subject'] ?? '') }}" required >
              @if ($errors->has('email_subject'))
              <div class="error">{{ $errors->first('email_subject') }}</div>
          @endif
          </div>
        </div>
        <div class="form-group message-area col-xs-12">
          <textarea name="email_body" class="form-control rounded-0" id="exampleFormControlTextarea1" rows="8" placeholder="Type your message here..." required >{{ old('email_body', $data['email_body'] ?? '') }}</textarea>
          @if ($errors->has('email_body'))
              <div class="error">{{ $errors->first('email_body') }}</div>
          @endif
        </div>
        <div class="edit-mail-bottom-col full-width col-xs-12">
          <div class="form-group row">
            <label for="community_buyers" class="col-form-label">Customer Community</label>
            <div class="edit-user-label">
                <input type="number" name="community_buyers" class="form-control" id="community_buyers" value="{{ old('community_buyers', $data['community_buyers'] ?? '') }}" min="-9999" max="99999" placeholder="0" required >
                @if ($errors->has('community_buyers'))
              <div class="error">{{ $errors->first('community_buyers') }}</div>
            @endif
            </div>
          </div>
          <div class="form-group row">
            <label for="community_sellers" class="col-form-label">Supplier Community</label>
            <div class="edit-user-label">
                <input name="community_sellers" type="number" class="form-control" id="community_sellers" value="{{ old('community_sellers', $data['community_sellers'] ?? '') }}" min="-9999" max="99999" placeholder="0" required >
                @if ($errors->has('community_sellers'))
              <div class="error">{{ $errors->first('community_sellers') }}</div>
            @endif
            </div>
          </div>
          <div class="form-group row">
            <label for="month_posts" class="col-form-label">Posts this month</label>
            <div class="edit-user-label">
                <input type="number" name="month_posts" class="form-control" id="month_posts" value="{{ old('month_posts', $data['month_posts'] ?? '') }}" min="-9999" max="99999" placeholder="0" required >
                @if ($errors->has('month_posts'))
              <div class="error">{{ $errors->first('month_posts') }}</div>
            @endif
            </div>
          </div>
          <div class="form-group row">
            <label for="csi_score" class="col-form-label">Client Share Index (CSI) Score</label>
            <div class="edit-user-label">
                <input type="number" name="csi_score" class="form-control" id="csi_score" value="{{ old('csi_score', $data['csi_score'] ?? '') }}" min="-9999" max="99999" placeholder="0" required >
                @if ($errors->has('csi_score'))
              <div class="error">{{ $errors->first('csi_score') }}</div>
            @endif
            </div>
          </div>
          <input type="hidden" name="performance_email" value="1">
          <div class="form-group row form-send-col">
            <button type="submit" class="btn btn-primary post-button">Send</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection