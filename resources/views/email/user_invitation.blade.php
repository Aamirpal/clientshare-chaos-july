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
            .reacted-col{font-weight:bold !important; color: #293248 !important;}
        </style>
      <![endif]-->
      <style>
         @import url('https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900');
         body {
         font-family: 'Roboto', Arial, Helvetica, sans-serif;
         }
         [owa] .profile-heading {
            line-height: 24px; 
            font-weight: bold; 
            color: #293248;
         }
      </style>
   </head>
   <body style="font-family: 'Roboto', Arial, Helvetica, sans-serif; background-color: #F5F6FA ; margin: 0; background-position: center top; background-repeat: repeat-x; padding: 0; height: 100%; ">
      <div style="font-family: 'Roboto', Arial, Helvetica, sans-serif; background-color: #F5F6FA ; margin: 0; background-position: center top; background-repeat: repeat-x; margin: 0; ">
         <table cellspacing="0" cellpadding="0" align="center" border="0" width="700" style="max-width: 700px; margin: 0 auto; width: 100%;">
            <tr>
               <td>
                  <table width="608" cellspacing="0" cellpadding="0" align="center" style="width: 100%; word-break: break-word; font-family: 'Roboto', Arial, Helvetica, sans-serif; font-size:15px; max-width: 608px; margin: 0 auto;  border-radius:10px 10px 0 0;">
                     <tr>
                        <td colspan="3" height="94"></td>
                     </tr>
                     <tr>
                        <td colspan="3" align="center" style="border-radius:10px;">
                            <table cellspacing="0" cellpadding="0" style="border: 1px solid #E8F0F8; border-radius:10px;">
                                <tr>
                                    <td colspan="3" align="center" style="border-radius: 10px 10px 0px 0px;">
                                    <table valign="middle" style="border-radius: 10px 10px 0px 0px; background-color: #FFFFFF; font-family:'Roboto', Arial, Helvetica, sans-serif;font-size:16px;line-height: 24px;color:#293248;font-weight:500;display:block;border-collapse:collapse; box-sizing: border-box;" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF">
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
                                                            <table align="center" height="60" width="60" style="height:60px; width:60px !important; background-repeat: no-repeat; background-position: center; background-size: contain;font-family:'Roboto', Arial, Helvetica, sans-serif;font-size:18px;color:#293248;border-radius:50px;font-weight:800;border-collapse:collapse;" cellspacing="0" cellpadding="0" border="0">
                                                            <tr>
                                                                @if(filter_var($mail_data['mail']['sender_image'], FILTER_VALIDATE_URL))
                                                                <td align="center" valign="middle" height="60" width="60" style="height: 60px; width: 60px;">
                                                                    <img height="60" width="60" style="border-radius: 50px; -ms-border-radius: 50px; height: 60px; width: 60px; vertical-align: middle; display: block;" src="{{$mail_data['mail']['sender_image']}}" />
                                                                </td>
                                                                @else
                                                                <td align="center" valign="middle" height="60" width="60" style="height: 60px; width: 60px;">
                                                                    <img height="60" width="60" style="border-radius: 50px; -ms-border-radius: 50px; height: 60px; width: 60px; vertical-align: middle; display: block;" src="{{ asset('/images/profile-placeholder.png') }}" />
                                                                </td>
                                                                @endif  
                                                            </tr>
                                                            </table>
                                                        </td>
                                                        <td style="padding:0px 30px 0px 15px; text-align:left;">
                                                            <p class="profile-heading" style="margin: 0px;">{{$mail_data['mail']['sender_first_name']}} {{$mail_data['mail']['sender_last_name']}} has invited you to a dedicated space for your <span style="color: #0D47A1;"><a href="{{ $mail_data['mail']['link'] }}" style="color: #0D47A1; text-decoration: none; ">{!! $mail_data['mail']['share_name'] !!}</a></span> relationship 
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
                                                <td colspan="3">
                                                 @include('email.include.share_joint_logo', ['seller_logo' => $mail_data['mail']['company_seller_logo'], 'buyer_logo' => $mail_data['mail']['company_buyer_logo']])
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left" colspan="3">
                                    <table bgcolor="#ffffff" style="border-radius: 0px 0px 10px 10px; font-size: 14px; color: #293248; line-height: 24px; font-family: 'Roboto', sans-serif;display:block;border-collapse:collapse; box-sizing: border-box;" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td width="64"></td>
                                            <td width="480" align="left" bgcolor="#ffffff" style="background-color: #ffffff; padding: 0px; border-radius: 0 0 10px 10px;">
                                                <table cellspacing="0" cellpadding="0" style="font-size: 14px ;color: #293248; line-height: 24px; width: 100%;font-family: 'Roboto', sans-serif;">
                                                <tr>
                                                    <td style="padding: 38px 0px 10px;">
                                                        Hello{{ $mail_data['mail']['receiver_first_name'] }},
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 10px 0px; word-break: break-word;">
                                                        @if(isset($mail_data['mail']['body']['message'] ))
                                                            {!! $mail_data['mail']['body']['message'] !!}
                                                        @else
                                                            Please join me on this Share – a unique platform that together will help us build a closer, more productive relationship. It’s powerful, simple to use and you can easily invite your colleagues to join too. It's a great way to ensure you have secure access to the latest information and contract insight in the best format, anytime, anywhere.
                                                        @endif    
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 10px 0px 0px;">Thanks</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 10px 0px 0px;">{{$mail_data['mail']['sender_first_name']}} {{$mail_data['mail']['sender_last_name']}} <br /> {{$mail_data['mail']['supplier_name']}}</td>
                                                </tr>
                                                @if(count($mail_data['mail']['colleagues']) > 0)
                                                <tr>
                                                    <td style="padding: 50px 0px 0px;font-weight: 500;"><span class="reacted-col">Your colleagues who have joined the community:</span></td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 10px 0px 0px;">
                                                        <table align="left" cellspacing="0" cellpadding="0">
                                                            <tr>
                                                            @foreach($mail_data['mail']['colleagues'] as $key => $colleague)
                                                            @if($key == 'rest_count')
                                                            <td style="padding-right: 8px;">
                                                                <table class="user-count" align="center" height="32" width="32" style="width: 32px !important; border-radius: 50px; color: #0D47A1; font-weight: bold; font-size: 12px; text-align: center;" bgcolor="#FFFFFF" cellspacing="0" cellpadding="0">
                                                                    <tr>
                                                                        <td height="32" width="32" style="height: 32px; width: 32px;">+{{$colleague}}</td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                            @elseif(filter_var($colleague, FILTER_VALIDATE_URL))
                                                            <td style="padding-right: 8px;">
                                                                <table align="center" height="32" width="32" style="width: 32px !important; border-radius: 50px; text-align: center;" bgcolor="#FFFFFF" cellspacing="0" cellpadding="0">
                                                                    <tr>
                                                                        <td height="32" width="32" style="height: 32px; width: 32px;"><img alt="User Profile Image" height= "32" width= "32" src="{{$colleague}}" style="border-radius: 50px; height: 32px; width: 32px; vertical-align: middle;"/></td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                            @else	
                                                            <td style="padding-right: 8px;">
                                                                <table align="center" height="32" width="32" style="width: 32px !important; border-radius: 50px; color: #293248; font-weight: bold; font-size: 12px; text-align: center;" bgcolor="#ffffff" cellspacing="0" cellpadding="0">
                                                                    <tr>
                                                                        <td height="32" width="32" style="height:32px; width: 32px;">
                                                                        <img height="32" width="32" style="border-radius: 50px; -ms-border-radius: 32px; height: 32px; width: 32px; vertical-align: middle; display: block;" src="{{ asset('images/name_initials/'.strtolower($colleague).'.png')}}" />    
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                            @endif
                                                            @endforeach
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                                @endif
                                                <tr>
                                                    <td style="padding: 60px 0px;"> <a href="{{ $mail_data['mail']['link'] }}"><img alt="Join Team" width="124" src="{{asset('images/join-team.png')}}" style="width:124px;" /></a></td>
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
                           <p style="margin: 25px 0px 0px;"><a href="{{$mail_data['mail']['unsubscribe_share']}}" style="color: #748AA1;text-decoration-line: underline;">Click here</a> to unsubscribe or manage your notifications.</p>
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