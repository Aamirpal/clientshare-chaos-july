<?php $ssl = false;
   if(env('APP_ENV')!='local')
     $ssl = true;?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <meta name=”x-apple-disable-message-reformatting”>
      <title>Clientshare</title>
       <!--[if mso]>
        <style type="text/css">
            body, table, td {font-family: Arial, Helvetica, sans-serif !important;}
            .profile-heading {line-height: 24px; font-weight: bold; color: #293248;}
            .user-count {font-weight:bold !important;}
            .post-subject{font-weight:bold !important;}
            .reacted-col{font-weight:bold !important; color: #293248 !important;}
        </style>
      <![endif]-->
      <style>
         @import url('https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900');
         body {
         font-family: 'Roboto', Arial, Helvetica, sans-serif;
         }
      </style>
   </head>
   <body style="font-family: 'Roboto', sans-serif; background-color: #F5F6FA ; margin: 0; background-position: center top; background-repeat: repeat-x; padding: 0; height: 100%;">
      <div style="font-family: 'Roboto', sans-serif; background-color: #F5F6FA ; margin: 0; background-position: center top; background-repeat: repeat-x; margin: 0; ">
         <table cellspacing="0" cellpadding="0" align="center" border="0" width="700" style="max-width: 700px; margin: 0 auto; width: 100%;">
            <tr>
               <td>
                  <table width="608" cellspacing="0" cellpadding="0" align="center" style="width: 100%; word-break: break-word; font-family: 'Roboto', sans-serif; font-size:15px; max-width: 608px; margin: 0 auto;  border-radius:10px 10px 0 0;">
                     <tr>
                        <td colspan="3" height="126"></td>
                     </tr>
                     <tr>
                        <td colspan="3" align="center" style="border-radius:10px;">
                            <table cellspacing="0" cellpadding="0" style="border: 1px solid #E8F0F8; border-radius:10px;">
                     <tr>
                        <td colspan="3" align="center" style="border-radius:10px 10px 0 0;">
                           <table valign="middle" style="background-color: #FFFFFF; font-family:'Roboto', Arial, Helvetica, sans-serif;font-size:16px;line-height: 24px;color:#293248;border-radius:10px 10px 0 0;font-weight:500;display:block;border-collapse:collapse; box-sizing: border-box;" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF">
                              <tbody>
                                 <tr>
                                    <td colspan="3" height="62"></td>
                                 </tr>
                                 <tr>
                                    <td width="64"></td>
                                    <td width="480" align="left">
                                       <table cellspacing="0" cellpadding="0">
                                          <tr>
                                             <td>
                                                <table align="center" height="60" width="60" style="height:60px; width:60px !important; background-repeat: no-repeat; background-position: center; background-size: contain;font-family:arial;font-size:18px;color:#293248;border-radius:50px;font-weight:800;border-collapse:collapse;" cellspacing="0" cellpadding="0" border="0">
                                                   <tr>
                                                      @if(filter_var($data['user_liked_profile_picture'] , FILTER_VALIDATE_URL))
                                                      <td align="center" valign="middle" height="60" width="60" style="height: 60px; width: 60px;">
                                                         <img height="60" width="60" style="border-radius: 50px; -ms-border-radius: 50px; height: 60px; width: 60px; vertical-align: middle; display: block;" src="{{ $data['user_liked_profile_picture'] }}" />
                                                      </td>
                                                      @else
                                                      <td align="center" valign="middle" height="60" width="60" style="height: 60px; width: 60px;">
                                                      <img height="60" width="60" style="border-radius: 50px; -ms-border-radius: 50px; height: 60px; width: 60px; vertical-align: middle; display: block;" src="{{asset('images/profile-placeholder.png')}}" />
                                                      </td>
                                                      @endif  
                                                   </tr>
                                                </table>
                                             </td>
                                             <td style="padding:0px 64px 0px 15px; text-align:left;">
                                                <p class="profile-heading" style="margin: 0px;">{{$data['user_liked_post']}} found your post useful in the
                                                <span style="color: #0D47A1;"><a href="{{$data['share_link']}}" style="text-decoration: none;color: #0D47A1;">{!! $data['share_name'] !!}</a></span> Share
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
                                    <td colspan="3" height="20"></td>
                                 </tr>
                                 <tr>
                                    <td colspan="3">
                                    <table align="center" style="width: 100%; font-family: 'Roboto', Arial, Helvetica, sans-serif; font-size:18px;color:#FFFFFF;font-weight:500;border-collapse:collapse; background-repeat: no-repeat;background-position: center;" cellspacing="0" cellpadding="0" border="0">
                                          <tr>
                                             <td>
                                                <table width="100" align="center" cellspacing="0" cellpadding="0" border="0" style="width:100px !important;">
                                                    <tr>
                                                        <td width="257" style="width:257px !important">
                                                            <img src="{{ asset('/images/border-large.png') }}" alt="">
                                                        </td>
                                                        <td width="45" style="width:45px">
                                                            <img width="45" src="{{$data['seller_logo']}}" alt="">
                                                        </td>
                                                        <td width="4" style="width:4px !important">
                                                            <img src="{{ asset('/images/border-new.png') }}" alt="">
                                                        </td>
                                                        <td width="45" style="width:45px">
                                                            <img width="45" src="{{$data['buyer_logo']}}" alt="">
                                                        </td>
                                                        <td width="257" style="width:257px !important">
                                                            <img src="{{ asset('/images/border-large.png')}}" alt="">
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
                        <td colspan="3" align="center">
                           <table bgcolor="#ffffff" style="border-radius: 0px 0px 10px 10px; font-size: 14px; color: #293248; line-height: 24px; font-family: 'Roboto', sans-serif;display:block;border-collapse:collapse; box-sizing: border-box;" cellspacing="0" cellpadding="0">
                              <tr>
                                 <td width="64"></td>
                                 <td width="480" align="left" bgcolor="#ffffff" style="background-color: #ffffff; padding: 0px; border-radius: 0 0 10px 10px;">
                                    <table cellspacing="0" cellpadding="0" style="font-size: 14px ;color: #293248; line-height: 24px; width: 100%;font-family: 'Roboto', sans-serif;">
                                       <tr>
                                          <td style="padding: 38px 0px 0px; font-weight: 500;">
                                          <span class="post-subject">{{$data['post_subject']}}</span></td>
                                       </tr>
                                       <tr>
                                          <td style="padding: 8px 0px 0px; word-break: break-word;">
                                             {!! nl2br(limitString($data['post_description'], config('constants.GENERIC.email_alert.post_description'))) !!}
                                          </td>
                                       </tr>
                                       @if(count($data['people_reacted']) > 0)
                                       <tr>
                                          <td style="padding: 40px 0px 2px;">
                                             <table align="left" cellspacing="0" cellpadding="0">
                                                <tr>
                                                   @foreach($data['people_reacted'] as $key => $people)
                                                   <td style="padding: 10px 0px 2px;">
                                                      <table align="left" cellspacing="0" cellpadding="0">
                                                         <tr>
                                                            @if($key == 'rest_count')
                                                            <td style="padding-right: 8px;">
                                                               <table class="user-count" align="center" height="32" width="32" style="width: 32px !important; border-radius: 50px; color: #0D47A1; font-weight: bold; font-size: 12px; text-align: center;" bgcolor="#FFFFFF" cellspacing="0" cellpadding="0">
                                                                  <tr>
                                                                     <td height="32" width="32" style="height: 32px; width: 32px;">{{$people}}</td>
                                                                  </tr>
                                                               </table>
                                                            </td>
                                                            @elseif(filter_var($people, FILTER_VALIDATE_URL))
                                                            <td style="padding-right: 8px;">
                                                               <table align="center" height="32" width="32" style="width: 32px !important; border-radius: 50px; text-align: center;" bgcolor="#FFFFFF" cellspacing="0" cellpadding="0">
                                                                  <tr>
                                                                     <td height="32" width="32" style="height: 32px; width: 32px;"><img alt="User Profile Image" height= "32" width= "32" src="{{$people}}" style="border-radius: 50px; height: 32px; width: 32px; vertical-align: middle;"/></td>
                                                                  </tr>
                                                               </table>
                                                            </td>
                                                            @else	
                                                            <td style="padding-right: 8px;">
                                                               <table align="center" height="32" width="32" style="width: 32px !important; border-radius: 50px; color: #293248; font-weight: bold; font-size: 12px; text-align: center;" bgcolor="#ffffff" cellspacing="0" cellpadding="0">
                                                                  <tr>
                                                                      <td height="32" width="32" style="height:32px; width: 32px;">
                                                                      <img height="32" width="32" style="border-radius: 50px; -ms-border-radius: 32px; height: 32px; width: 32px; vertical-align: middle; display: block;" src="{{ asset('images/name_initials/'.strtolower($people).'.png')}}" />        
                                                                      </td>
                                                                  </tr>
                                                               </table>
                                                            </td>
                                                            @endif
                                                         </tr>
                                                      </table>
                                                   </td>
                                                   @endforeach
                                                </tr>
                                             </table>
                                          </td>
                                       </tr>
                                       <tr>
                                          <td style="padding: 10px 0px 0px;font-weight: 500;"><span class="reacted-col">...have reacted on this post</span></td>
                                       </tr>
                                       @endif
                                       <tr>
                                          <td style="padding: 60px 0px;">
                                             <a href="{{$data['respond_link']}}">
                                             <img alt="Respond" width="85"  src="{{asset('images/respond.png')}}" style="width: 85px;" />
                                             </a>
                                          </td>
                                       </tr>
                                    </table>
                                 </td>
                                 <td width="64"></td>
                              </tr>
                           </table>
                        </td>
                     </tr>
                     </table>
                        </td>
                     </tr>
                     <tr>
                        <td colspan="3" style="color: #748AA1;font-size: 14px;line-height: 22px;text-align: center;font-family: 'Roboto', Arial, Helvetica, sans-serif;font-weight: normal;">
                           <p style="margin: 25px 0px 0px;">
                              <a href="{{ $data['unsubscribe_share'] }}" style="color: #748AA1;text-decoration-line: underline;">Click here</a> to unsubscribe or manage your notifications.
                           </p>
                        </td>
                     </tr>
                     <tr>
                        <td colspan="3" height="126"></td>
                     </tr>
                  </table>
               </td>
            </tr>
         </table>
      </div>
   </body>
</html>