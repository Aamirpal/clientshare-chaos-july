@php  
$ssl = false;
if(env('APP_ENV')!='local')
   $ssl = true;
$clientsharname=Session::get('space_info')['share_name'];
$dateObj   = DateTime::createFromFormat('!m', $month);
$monthName = $dateObj->format('F');
$http = $ssl ? 'https' : 'http';
@endphp   
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "{{$http}}://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="{{$http}}://www.w3.org/1999/xhtml">
   <head>
      <link href="{{$http}}://fonts.googleapis.com/css?family=Lato:300,400,400i,700,700i" rel="stylesheet">
      <style>
         @font-face {
         font-family: 'Lato', sans-serif;
         font-style: normal;
         font-weight: normal;
         src: url({{$http}}://fonts.googleapis.com/css?family=Lato:300,400,400i,700) format('truetype');
         }
         table {font-family: 'Lato', sans-serif; border-collapse:separate;}
         table tr, table td {padding: 0;}
      </style>
   </head>
   <body>
      <table align="center" cellpadding="0" cellspacing="0" width="100%" style="border: 1px solid #E0E0E0; border-bottom: 0px none;  word-break: break-all;">
         <tr>
            <td>
               <table width="100%" style="border-bottom: 1px solid #E0E0E0;">
                  <tr>
                     <td align="center" style="font-size:24px; color: #424242; line-height: 24px; height: 24px; padding-top: 25px; padding-bottom: 12px; font-weight: 500;">
                        Net Promoter Score {{@$nps}} 
                     </td>
                  </tr>
                  <tr>
                     <td align="center" style="font-size:19px; font-style: italic; color: #9E9E9E; height: 19px; line-height: 19px; font-weight: 400;">
                        @php $spacInfoValue = Session::get('space_info'); @endphp
                        {{$data['current_quater']}} Feedback for the @if(isset($spacInfoValue))
                        {{ $spacInfoValue->toArray()['seller_name']['company_name'] }} & {{ $spacInfoValue->toArray()['buyer_name']['company_name'] }}   @endif relationship
                     </td>
                  </tr>
                  <tr>
                     <td align="center" style="font-size:15px; color: #0D47A1; height: 15px; line-height: 15px; padding-top: 13px; padding-bottom: 26px; font-weight: 400;">
                        Download {{$data['current_quater']}} Feedback&nbsp;<span><img src="{{url('/',[],$ssl)}}/images/ic_file_download.svg" alt=""></span>
                     </td>
                  </tr>
               </table>
            </td>
         </tr>
         <tr>
            <td colspan="2">
               <p style="font-size:20; color: #424242; line-height: 18px; height: 18px; padding-left:17px; border-bottom: 1px solid #E0E0E0; padding-bottom: 17px; font-weight: 500;">Number of respondees: @php if(!empty($feedback)){ echo sizeOfCustom($feedback); }else{ echo '0'; } @endphp </p>
            </td>
         </tr>
         @if(!empty($feedback))
         @foreach($feedback as $feed)
         @php
            $feed->profile_image_url = getAwsSignedURL(filePathJsonToUrl($feed->profile_image));
         @endphp
         <tr>
            <td>
               <table width="100%"  style="padding: 25px 17px; border-bottom: 1px solid #E0E0E0; ">
                  <tbody>
                     <tr>
                        <td width="32px" style="padding-right: 12px;">
                           @if(!empty($feed->profile_image_url) && !strpos($feed->profile_image_url, config('constants.MEDIA_LINKEDIN')))
                           <img src="{{$feed->profile_image_url}}" height="32" width="32" style="border-radius: 16px; alt="user_image">
                           @else
                           <img src="{{url('/',[],$ssl)}}/images/user-icon.png" height="32" width="32" style="border-radius: 16px; alt="user_image">
                           @endif
                        </td>
                        <td align="left">
                           <p style="height: 15px; font-size:15px; font-weight: 400;  line-height: 15px; color: #424242; margin: 0">{{ucfirst($feed->first_name)}} {{ucfirst($feed->last_name)}}</p>
                           <p style="margin: 4px 0 0 0;"><a href="#" style="height: 13px; font-size:13px; color: #9E9E9E; font-weight: 400; line-height: 13px; text-decoration: none;">{{$feed->email}}</p>
                        </td>
                     </tr>
                     <tr>
                        <td colspan="2" style="font-size:20; color: #424242; line-height: 18px; height: 18px; padding-top: 24px; padding-bottom: 0px; font-weight: 500;">Score: {{$feed->rating}}</td>
                     </tr>
                     <tr>
                        <td colspan="2" style="font-size:18px; color: #212121; line-height: 18px; height: 18px; padding-top: 24px; padding-bottom: 13px; font-weight: 500;">What {{ucfirst($feed->first_name)??''}} thinks @if(isset($spacInfoValue))
                           {{ $spacInfoValue->toArray()['seller_name']['company_name'] }} @endif can do better
                        </td>
                     </tr>
                     <tr>
                        <td colspan="2" style="font-size:15px; color: #424242; line-height: 24px; padding-top: 4px; font-weight: 400;">@if(!empty($feed->suggestion)){{$feed->suggestion}} @else <span class="no-comment" style="color: #BDBDBD;"> No Comment </span> @endif</td>
                     </tr>
                     <tr>
                        <td colspan="2" style="font-size:18px; color: #212121; line-height: 18px; height: 18px; padding-top: 24px; padding-bottom: 13px; font-weight: 500;">General comments</td>
                     </tr>
                     <tr>
                        <td colspan="2" style="font-size:15px; color: #424242; line-height: 24px; padding-top: 4px; font-weight: 400;">@if(!empty($feed->comments)){{$feed->comments}}@else<span class="no-comment" style="color: #BDBDBD;">No Comment</span>@endif</td>
                     </tr>
                  </tbody>
               </table>
            </td>
         </tr>
         @endforeach
         @endif
         @if(!empty($get_non_feedback_user))
            @foreach($get_non_feedback_user as $non_feedback_user)
            @php
               $non_feedback_user['user']['profile_image_url'] = getAwsSignedURL(composeUrl($non_feedback_user['user']['profile_image']));
            @endphp
               @if(Session::get('space_info')['company_buyer_id'] == $non_feedback_user['user_company_id'])
                  <tr style="page-break-inside: avoid;">
                     <td style="page-break-inside: avoid;">
                        <table width="100%" style="padding: 9px 17px; border-bottom: 1px solid #E0E0E0; border-top: 1px solid #E0E0E0;">
                           <tbody>
                              <tr>
                                 <td width="32px" style="padding-right: 12px;" style="page-break-inside: avoid;" nobr="true">
                                    @if($non_feedback_user['user']['profile_image_url'] && !strpos($non_feedback_user['user']['profile_image_url'], config('constants.MEDIA_LINKEDIN')))
                                    <img src="{{$non_feedback_user['user']['profile_image_url']}}" height="32" width="32" style="border-radius: 16px; alt="user_image">
                                    @else
                                    <img src="{{url('/',[],$ssl)}}/images/user-icon.png" height="32" width="32" style="border-radius: 16px; alt="user_image">
                                    @endif
                                 </td>
                                 <td align="left" style="page-break-inside: avoid;">
                                    <p style="height: 15px; font-size:15px;  line-height: 15px; color: #424242; margin: 0">{{ucfirst($non_feedback_user['user']['first_name']) }} {{ ucfirst($non_feedback_user['user']['last_name']) }}</p>
                                    <p style="margin: 4px 0 0 0;"><a href="#k" style="height: 13px; font-size:13px; color: #9E9E9E; line-height: 13px; text-decoration: none;">{{$non_feedback_user['user']['email'] }}</p>
                                 </td>
                                 <td align="right" style="page-break-inside: avoid;">
                                    <p style="color: #BDBDBD; font-size: 15px; line-height: 15px; margin: 0">No feedback given</p>
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </td>
                  </tr>
               @endif
            @endforeach
         @endif
      </table>
   </body>
</html>