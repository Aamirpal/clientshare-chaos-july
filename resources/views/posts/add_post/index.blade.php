<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <meta name=”x-apple-disable-message-reformatting”>
      <title>Clientshare</title>
      <style>
         @import url('https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900');
         body {
         font-family: 'Roboto', Arial, Helvetica, sans-serif;
         }
         .body-content a {
            word-wrap: anywhere;
            word-break: break-all;
         }
      </style>
   </head>
   <body style="font-family: 'Roboto', Arial, Helvetica, sans-serif; background-color: #F5F6FA ; margin: 0; background-position: center top; background-repeat: repeat-x; padding: 0; height: 100%;">
      <div style="font-family: 'Roboto', Arial, Helvetica, sans-serif; background-color: #F5F6FA ; margin: 0; background-position: center top; background-repeat: repeat-x; margin: 0; ">
         <table cellspacing="0" cellpadding="0" align="center" border="0" width="700" style="max-width: 700px; margin: 0 auto; width: 100%;">
            <tr>
               <td>
                  <table width="608" cellspacing="0" cellpadding="0" align="center" style="width: 100%; word-break: break-word; font-family: 'Roboto', Arial, Helvetica, sans-serif; font-size:15px; max-width: 608px; margin: 0 auto;  border-radius: 10px 10px 0 0;">
                     <tr>
                        <td colspan="3" height="94"></td>
                     </tr>
                     <tr>
                        <td colspan="3" align="center" style="border-radius:10px;">
                            <table cellspacing="0" cellpadding="0" style="border: 1px solid #E8F0F8; border-radius:10px;">
                     <tr>
                        <td colspan="3" align="center" style="border-radius: 10px 10px 0 0;">
                        <table valign="middle" style="background-color: #FFFFFF; font-family:'Roboto', Arial, Helvetica, sans-serif;font-size:16px;line-height: 24px;color:#293248;border-radius:10px 10px 0 0;font-weight:500;display:block;border-collapse:collapse; box-sizing: border-box;" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF">
                              <tbody>
                                 <tr>
                                    <td colspan="4" height="62"></td>
                                 </tr>
                                 <tr>
                                    <td width="64"></td>
                                    <td width="480" align="left">
                                       <table cellspacing="0" cellpadding="0">
                                          <tr>
                                             <td>
                                                <table align="center" height="60" width="60" style="height:60px; width:60px !important; background-repeat: no-repeat; background-position: center; background-size: contain;font-family:arial;font-size:18px;color:#293248;border-radius:50px;font-weight:800;border-collapse:collapse;" cellspacing="0" cellpadding="0" border="0">
                                                   <tr>
                                                      @if( $mail_data['from_user']['circular_profile_image'])
                                                      <td align="center" valign="middle" height="60" width="60" style="height: 60px; width: 60px;">
                                                         <img height="60" width="60" style="border-radius: 50px; -ms-border-radius: 50px; height: 60px; width: 60px; vertical-align: middle; display: block;" src="{{composeEmailUrl(composeUrl($mail_data['from_user']['circular_profile_image']))}}" />
                                                      </td>
                                                      @else
                                                      <td align="center" valign="middle" height="60" width="60" style="height: 60px; width: 60px;">
                                                      <img height="60" width="60" style="border-radius: 50px; -ms-border-radius: 50px; height: 60px; width: 60px; vertical-align: middle; display: block;" src="{{ $mail_data['app_url'] }}/images/profile-placeholder.png" />
                                                      </td>
                                                      @endif  
                                                   </tr>
                                                </table>
                                             </td>
                                             <td style="padding: 0px 62px 0px 15px; text-align: left;">
                                                <p class="profile-heading" style="margin: 0px;">{{$mail_data['from_user']['first_name'].' '.$mail_data['from_user']['last_name']}} would like you to see a new post on the <span style="color: #0D47A1;"><a href="{{route('landing_page', ['id'=>$mail_data['post']['space_id']])}}" style="color: #0D47A1; text-decoration: none;">{!! trim($mail_data['mail']['space_name']) !!}</a></span> Share
                                                </p>
                                             </td>
                                          </tr>
                                       </table>
                                    </td>
                                    <td width="64"></td>
                                 </tr>
                                 <tr>
                                    <td colspan="3" height="20"></td>
                                 </tr>
                                 
                                 <tr>
                                    <td colspan="3" height="30"></td>
                                 </tr>
                                 <tr>
                                    <td colspan="4">
                                       <table align="center" style="width: 100%; font-family: 'Roboto', Arial, Helvetica, sans-serif; font-size:18px;color:#FFFFFF;font-weight:500;border-collapse:collapse; background-repeat: no-repeat;background-position: center;" cellspacing="0" cellpadding="0" border="0">
                                          <tr>
                                           <td>
                                               <table align="center" cellspacing="0" cellpadding="0" border="0" style="width:100%;">
                                                 <tr>
                                                   <td width="257" style="width:257px !important">
                                                     <img src="{{ $mail_data['app_url'] }}/images/border-large.png" alt="">
                                                   </td>
                                                   <td width="45" style="width:45px">
                                                     <img width="45" src="{{$mail_data['mail']['seller_logo']}}" alt="">
                                                   </td>
                                                    <td width="4" style="width:4px !important">
                                                     <img src="{{ $mail_data['app_url'] }}/images/border-new.png" alt="">
                                                   </td>
                                                   <td width="45" style="width:45px">
                                                     <img width="45" src="{{$mail_data['mail']['buyer_logo']}}" alt="">
                                                   </td>
                                                   <td width="257" style="width:257px !important">
                                                     <img src="{{ $mail_data['app_url'] }}/images/border-large.png" alt="">
                                                   </td>

                                                 </tr>

                                               </table>
                                           </td>
                                       </tr>
                                       </table>
                                    </td>
                                 </tr>
                                 
                              </tbody>
                           </table>
                        </td>
                     </tr>
                     <tr>
                        <td align="center" colspan="3">
                        <table bgcolor="#ffffff" style="border-radius: 0px 0px 10px 10px; font-size: 14px; color: #293248; line-height: 24px; font-family: 'Roboto', sans-serif;display:block;border-collapse:collapse; box-sizing: border-box;" cellspacing="0" cellpadding="0">
                              <tbody>
                                 <tr>
                                    <td width="64" style="width:64px"></td>
                                    <td width="480">
                                       <table cellspacing="0" cellpadding="0" style="background-color: #FFFFFF; font-family: 'Roboto', Arial, Helvetica, sans-serif; font-size: 14px ;color: #293248; line-height: 24px; width: 100%;">
                                          <tbody>
                                             <tr>
                                                <td style="font-weight: 500; padding: 38px 0px 0px;">
                                                <span class="post-subject">{{$mail_data['mail']['post_subject']}}</span></td>
                                             </tr>
                                             <tr>
                                                <td style="padding: 10px 0px 0px;">
                                                   <span class="body-content">
                                                      {!!nl2br(limitAlertStringWithRedirect($mail_data['mail']['post_body'], 292, $mail_data['post_page']))!!}
                                                   </span>
                                                </td>
                                             </tr>
                                             <tr>
                                                @if(isset($mail_data['post_media']) && sizeOfCustom($mail_data['post_media']))
                                                @include('posts.add_post.post_images')
                                                @endif
                                                @if(isset($mail_data['post']['metadata']['get_url_data']) && sizeOfCustom($mail_data['post']['metadata']['get_url_data']))
                                                @include('posts.add_post.post_url')
                                                @endif
                                             </tr>
                                             @if(isset($mail_data['post_media']) && sizeOfCustom($mail_data['post_media']))
                                             @include('posts.add_post.post_attachment')
                                             @endif
                                             @include('posts.add_post.user_list_same_post')
                                             <tr>
                                                <td style="padding: 60px 0px;">
                                                   <a href="{{$mail_data['post_page']}}">
                                                   <img width="98" alt="image" src="{{ $mail_data['app_url'] }}/images/view-post-btn.png" style="width: 98px;">
                                                   </a>
                                                </td>
                                             </tr>
                                          </tbody>
                                       </table>
                                    </td>
                                    <td width="64" style="width:64px"></td>
                                 </tr>
                              </tbody>
                           </table>
                        </td>
                     </tr>
                     </table>
                        </td>
                     </tr>
                     <tr>
                        <td colspan="3" style="color: #748AA1;font-size: 14px;line-height: 22px;text-align: center;font-family: 'Roboto', Arial, Helvetica, sans-serif;font-weight: normal;">
                           <p style="margin: 25px 0px 0px;"><a href="{{$mail_data['unsubscribe_share']}}" style="color: #748AA1;text-decoration-line: underline;">Click here</a> to unsubscribe or manage your notifications.</p>
                        </td>
                     </tr>
                     <tr>
                        <td colspan="3" height="94"></td>
                     </tr>
                  </table>
               </td>
            </tr>
         </table>
      </div>
   </body>
</html>