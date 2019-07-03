@if($notification_header==1 && !empty($data)) 
<li class="header">Notifications</li>
@endif
@foreach($data as $notifications)
@php
   $notifications->profile_image_url = wrapUrl(composeUrl($notifications->profile_image));
@endphp
<?php $readstatus_class='read_inactive'; ?>
@if($notifications->notification_status==1)
<?php $readstatus_class= 'read_active'; ?>
@endif
<li class="{{$readstatus_class}}">
   @if($notifications->notification_type == 'feedback_close')
      <a href="{{ env('APP_URL').'/feedback' }}">
      <span class="space-pic-wrap">
         <img class="" src="{{ $current_page['space_data'][0]['company_seller_logo'] }}" alt="">
         <img class="space-pic" src="{{ $current_page['space_data'][0]['company_buyer_logo'] }}" alt="">
      </span>
   @elseif($notifications->notification_type == 'feedback')
      @if( sizeOfCustom($feedback) )
         <a href="#!" style="background:rgba(13, 71, 161, 0.12) none repeat scroll 0 0 !important;">
      @else
         <!-- <a href="#!" class="feedbacknoti" data-toggle="modal" data-target="#feedback-popup"> -->
         <a href="{{ env('APP_URL') }}/clientshare/{{$current_page['space_id']}}?feedback=true" class="feedbacknoti">
      @endif
      <span class="space-pic-wrap">
         <img class="" src="{{ $current_page['space_data'][0]['company_seller_logo'] }}" alt="">
         <img class="space-pic" src="{{ $current_page['space_data'][0]['company_buyer_logo'] }}" alt="">
      </span>
   @elseif($notifications->profile_image_url != '')
         <a href="{{ env('APP_URL') }}/clientshare/{{$current_page['space_id']}}/{{$notifications->post_id}}/{{$notifications->id}}">
         <span class="dp pro_pic_wrap" style="background-image: url('{{ $notifications->profile_image_url }}');"></span>
   @elseif($notifications->profile_image_url == '')
         <a href="{{ env('APP_URL') }}/clientshare/{{$current_page['space_id']}}/{{$notifications->post_id}}/{{$notifications->id}}">
         <span class="dp pro_pic_wrap" style="background-image: url('{{ env('APP_URL') }}/images/dummy-avatar-img.svg');"></span>

   @endif
   <span class="notifydetail">
   @if($notifications->comment_count > '0' && $notifications->notification_type == 'comment')
      {!! $notifications->posttext !!}
   @elseif($notifications->notification_type == 'user_tagged')
      {!! $notifications->posttext !!}
   @elseif($notifications->notification_type == 'feedback_close')
      <strong>
         Feedback for {{ Carbon\Carbon::parse($notifications->created_at)->subMonth(3)
         ->format('F')}} - {{ Carbon\Carbon::parse($notifications->created_at)->subMonth(1)
         ->format('F')}} is now available
      </strong>
   @elseif($notifications->notification_type == 'feedback')
         <strong>
            {{ Carbon\Carbon::parse($notifications->created_at)->subMonth(3)
         ->format('F')}} - {{ Carbon\Carbon::parse($notifications->created_at)->subMonth(1)
         ->format('F')}}
            feedback is now open.
      </strong>
   @elseif($notifications->notification_type == 'like')
         <strong>
            {!! ucfirst($notifications->first_name) !!}
         </strong>  
            has liked your post.
   @else
      <strong>
            {!! ucfirst($notifications->first_name) !!}
      </strong>
      added a post
   @endif
   </span>
   </a>
</li>
@endforeach
@if($notification_header==0 && $notification_count!=0) 
<li id="loading_li" class="header"><img src="{{env('APP_URL')}}/images/loading_bar1.gif"></li>
@endif