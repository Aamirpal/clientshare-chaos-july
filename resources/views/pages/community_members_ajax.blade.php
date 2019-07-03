@foreach($space_members as $me) 

@if(isset($me['metadata']['user_profile']['bio']))
@php
  $full_bio = $me['metadata']['user_profile']['bio'];
@endphp
@if(strlen($me['metadata']['user_profile']['bio'])>30)
@php
$biography = substr($me['metadata']['user_profile']['bio'], 0, 30)."...";
@endphp
@else
@php
$biography = $me['metadata']['user_profile']['bio']
@endphp
@endif
@else
@php
  $full_bio = "";
  $biography = "";
@endphp
@endif
@if(isset($me['user']['contact'])) 
@if(isset($me['user']['contact']['linkedin_url']))
@php
  $linkedin = $me['user']['contact']['linkedin_url'];
@endphp
@else
@php
  $linkedin = "";
@endphp
@endif
@if(isset($me['user']['contact']['contact_number']))
@php
  $contact = $me['user']['contact']['contact_number'];
@endphp
@else
@php
  $contact = "";
@endphp
@endif
@else
@php
  $linkedin = "";
  $contact = "";
@endphp
@endif
<?php 
if(!empty($linkedin))
{
  $parsed = parse_url($linkedin);
  if (empty($parsed['scheme']))
  {
    $linkedin = 'http://' . ltrim($linkedin, '/');
  }
}
?>
<div class="col-lg-4 col-md-6 col-sm-6 col-xs-12 community_member_block">
  <div class="community-col">
    <div class="community-top-detail community-user-detail" data-toggle="modal-" data-target="#myModal{{$me['id']}}" data-id="{{$me['user_id']}}">
      <div class="community-user-member-img">
        @if(!isset($me['user']['profile_image_url']) || (isset($me['user']['profile_image_url']) && empty($me['user']['profile_image_url'])))
        <div class="community-user-memmber" style="background-image:url('{{env('APP_URL')}}/images/dummy-avatar-img.svg')"></div>
        @else
        <div class="community-user-memmber" style="background-image:url('{{$me['user']['profile_image_url']}}')"></div>      
        @endif
      </div>
      <div class="community-user-member-detail">
        <span class="community-member-name"  data-id="{{$me['user_id']}}">
          {{ucfirst($me['user']['first_name'])}}@if(isset($me['user']['last_name'])) {{ucfirst($me['user']['last_name'])}} @endif
        </span>
        <span class="community-member-designation">
          @php
          if(isset($me['metadata']['user_profile']['job_title'])){
          if(strlen($me['metadata']['user_profile']['job_title'])>30){
          $title = substr($me['metadata']['user_profile']['job_title'], 0, 30);
          echo $result = substr($title, 0, strrpos($title, ' '));
          echo (strlen($me['metadata']['user_profile']['job_title'])>30 ?'...':'');
        }else{
        echo $me['metadata']['user_profile']['job_title'];
      }
    }
    @endphp
  </span>
  @if(!empty($me['sub_comp']))
  <span class="community-member-company">{{$me['sub_comp']['company_name']}}</span>
  @else   
  <span class="community-member-company">{{$companies_dictonary[$me['company_id']]}}</span>    
  @endif
</div>
<div class="community-user-des">
  <p>
    @php
    if(isset($me['metadata']['user_profile'])){
    if(strlen(trim($me['metadata']['user_profile']['bio']))>70){
    echo $bio = trim(substr($me['metadata']['user_profile']['bio'], 0, 70));
    echo (strlen(trim($me['metadata']['user_profile']['bio']))>70 ?'...':'');
  }else{
  echo trim($me['metadata']['user_profile']['bio']);
}  }
@endphp
  </p>
  <p class="full_bio hide">{{ $me['metadata']['user_profile']['bio'] }}</p>
</div>
</div>
<div class="community-user-contact">
  @if(isset($me['user']['email']))
  <span class="community-user-mail"><a href="mailto:{{$me['user']['email']}}" target="_top"><img src="{{env('APP_URL')}}/images/ic_email.svg" alt="Mail"/></a></span>
  @endif
  @if(isset($me['metadata']['user_profile']['user']['contact']['linkedin_url']) && $me['metadata']['user_profile']['user']['contact']['linkedin_url']!='')  
  <?php
  $linked_url= $me['metadata']['user_profile']['user']['contact']['linkedin_url'];

  if (preg_match("#https?://#", $me['metadata']['user_profile']['user']['contact']['linkedin_url']) === 0 ) {
    $linked_url = 'http://'.$me['metadata']['user_profile']['user']['contact']['linkedin_url'];
  } 
  ?>
  <span class="community-user-linkdin"><a href="{{$linked_url}}" target="_blank"><img src="{{env('APP_URL')}}/images/ic_linkedin.svg" alt="Mail"/></a></span>
  @endif
  @if(isset($me['metadata']['user_profile']['user']['contact']['contact_number']) && $me['metadata']['user_profile']['user']['contact']['contact_number']!='')  
  <span class="community-user-phone"><a href="tel:{{$me['metadata']['user_profile']['user']['contact']['contact_number']}}"><img src="{{env('APP_URL')}}/images/ic_call.svg" alt="Mail"/></a></span>
  @endif  
</div>
</div>
</div>


@endforeach