  @extends(session()->get('layout'))
  @section('content')
  <div class="container-fluid feed-content">
    <div class="col-md-10  mid-content community_page_content">
     <div class="community-wrap ">
      <div class="community-content-wrap box">
       <div class="heading_wrap">
        <div class="col-lg-2 col-md-3 col-sm-6 col-xs-3 community_hd_section">
          <h4 class="title">Community</h4>
        </div>
        <div class="col-lg-2 col-md-3 col-sm-6 col-xs-8 community_search_section">
        @if(!Session::get('space_info')['invite_permission'] || Session::get('space_info')['space_user'][0]['user_type_id'] == Config::get('constants.USER_ROLE_ID'))
          <a href="#" class="invite-icon" data-toggle="modal" data-target="#myModalInvite"><img src="{{ url('images/ic_group_add.svg',[],env('HTTPS_ENABLE', true)) }}" alt="" /></a>
        @endif  
          <div class="search-wrap" >
            <a href="#" class="search_icon_blue"><img src="{{env('APP_URL')}}/images/ic_search_blue.png" alt="" /></a>
            <form class="search_form dropdown" method="post" action="../community_members/{{request()->segment(2)}}" style="display:none">
             {{ csrf_field() }}
             <input 
                 type="text" 
                 name="search" 
                 id="search_auto"
                 class="form-control" 
                 autocomplete="off" 
                 value="@php echo $search??''; @endphp" 
                 data-toggle="dropdown" 
                 aria-expanded="false"
                 placeholder="Search" />
                <ul class="dropdown-menu ajax-user-search-dropdown" aria-labelledby="search_auto" id="community_search_results_list">
                </ul>
             </span>
             <input type="hidden" name="company_id" id="search_company_id" value="@php echo $company_id ?? ''; @endphp" />
             @if ($errors->has('search'))
             <span class="error-msg text-left">
               {{ $errors->first('search') }}
             </span>
             @endif
             <input type="hidden" name="share_id" value="{{request()->segment(2)}}"> 
             <button class="btn btn-primary" type="submit">Search</button>
           </form>
         </div>
       </div>
       <div class="col-lg-8 col-md-6 col-sm-12 col-xs-12 community-tab-section">
        <ul class="nav nav-tabs community-member-tabs" role="tablist1">
         @php $spacInfoValue = Session::get('space_info'); @endphp 
         <li role="presentation" class="@if(!in_array($company_id, [$spacInfoValue->toArray()['seller_name']['id'], $spacInfoValue->toArray()['buyer_name']['id']])) active @endif"><a href="{{env('APP_URL')}}/community_members/{{request()->segment(2)}}?company_id=&_tab=" aria-controls="all-members-" role="tab-" data-toggle="tab-">All</a></li>
         <li role="presentation" class="@if($spacInfoValue->toArray()['seller_name']['id'] == $company_id) active @endif"> @if(isset($spacInfoValue))<a href="{{env('APP_URL')}}/community_members/{{request()->segment(2)}}?company_id={{ $spacInfoValue->toArray()['seller_name']['id'] }}" aria-controls="company-one-" role="tab-" data-toggle="tab-">@endif
          @if(isset($spacInfoValue)) {{$spacInfoValue->toArray()['seller_name']['company_name']}} @endif
        </a>
      </li>
      <li role="presentation" class="@if($spacInfoValue->toArray()['buyer_name']['id'] == $company_id) active @endif">@if(isset($spacInfoValue))<a href="{{env('APP_URL')}}/community_members/{{request()->segment(2)}}?company_id={{ $spacInfoValue->toArray()['buyer_name']['id'] }}" aria-controls="company-two-" role="tab-" data-toggle="tab-">@endif
        @if(isset($spacInfoValue)){{ $spacInfoValue->toArray()['buyer_name']['company_name']}} @endif
      </a></li>
    </ul>
  </div>
</div>
@include('layouts.invite_colleague')
<div class="ajax_search_community_member_blocks" style="display: none;"></div>
<div class="community_inner_content">
  @if(!empty($space_members))
  @php $tot_records =  sizeOfCustom($space_members); 
  $count = 1;
  $div_count = 1;
  @endphp
  <input type="hidden" id="next_div" value="show_2">
  <input type="hidden" id="need_record" value="15">
  <input type="hidden" id="tot_record" value="{{$tot_records}}">
  @foreach($space_members as $me) 
  @php
  if ($count%15 == 1)
  {  
  if ($div_count == 1)
  {  
  echo "
  <div class='show_$div_count'>
   ";
 }
 else
 {  
 echo "
 <div class='show_$div_count' style='display: none'>
  ";
}
}
@endphp
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
        <span class="community-member-name">
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

@php
if ($count%15 == 0)
{
  echo "
</div>
";
$div_count++;
}
$count++;
@endphp
@endforeach
@else
No Results Found
@endif
</div>
</div><!-- comment-wrap -->
</div><!-- post-wrap-->
</div><!-- col-md-8 -->
</div><!-- container -->
<div class="modal fade community-member-detail" id="member_info_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
     <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{env('APP_URL')}}/images/ic_highlight_removegray.svg" alt="" /></button>
    </div>  
    <div class="modal-body">
      <div class="modal_image_section">
       @if(!isset($me['user']['profile_image_url']))
       <span style="background-image:url('{{env('APP_URL')}}/images/dummy-avatar-img.svg')">
       </span>
       @else
       <span style="background-image:url('{{$me['user']['profile_image_url']}}')">
       </span>        
       @endif
     </div>
     <div class="modal_content_section community_member_info">
       <div class="member_info">
        <h4>NAME</h4>
        <h5>COMPANY</h5>
        <h6>JOBTITLE</h6>              
        <p>BIO</p>
        <div class="contact-info">
         <h6>Contact information</h6>                 
         <span class="email-link">EMAIL</span>
         <span class="linkedin-link">LINKEDIN</span>
         <span class="call-link">CONTACT</span> 
       </div>
     </div>
   </div>
 </div>
</div>
</div>
</div>
<script src="{{ url('js/bootstrap-multiselect.js',[],env('HTTPS_ENABLE', true)) }}"></script>
<script src="{{ url('js/bootstrap-tour.min.js',[],env('HTTPS_ENABLE', true)) }}"></script>
<script src="{{ url('js/bootstrap-select.js',[],env('HTTPS_ENABLE', true)) }}"></script>
<script src="{{ url('js/custom/community.js',[],env('HTTPS_ENABLE', true)) }}"></script>
@endsection
