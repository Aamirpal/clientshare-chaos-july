<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <title>Clientshare</title>
   </head>
   <body style="font-family: arial; background-color: #e0e0e0 ; margin: 0; background-image: url('{{ $mail_data['path'] }}/images/white-bg.png'); background-position: center top; background-repeat: repeat-x; margin: 0; ">
      <div style="font-family: arial; background-color: #e0e0e0 ; margin: 0; background-image: url('{{ $mail_data['path'] }}/images/white-bg.png'); background-position: center top; background-repeat: repeat-x; margin: 0; ">
         <table cellspacing="0" cellpadding="0" align="center" style="font-family: arial; font-size:15px; max-width: 600px; margin: 0 auto;  border-radius:6px 6px 0 0;">
            <tr>
               <td colspan="2" height="94"></td>
            </tr>
            <tr>
               <td colspan="2" align="center" style="background-color: #424242; border-radius:6px 6px 0 0;">
                  <table style="font-family:arial;font-size:21px;color:#FFFFFF;border-radius:6px 6px 0 0;font-weight:500;display:inline-block;border-collapse:collapse;" cellspacing="0" cellpadding="0" border="0" bgcolor="#424242">
                     <tbody>
                        <tr>
                           <td colspan="2"><img alt="image"  src="{{ $mail_data['path'] }}/images/email-bg.jpg"></td>
                        </tr>
                        <tr>
                           <td style="padding-left: 5px;">
                              <table>
                                 <tr>
                                    <td valign="middle">
                                       <img width="48" height="48" alt="image"  src="{{$mail_data['space']['processed_seller_logo']?composeEmailURL(composeUrl($mail_data['space']['processed_seller_logo'])):composeEmailURL(composeUrl($mail_data['space']['seller_logo']))}}" style="width:48px;vertical-align:super; height:48px; float: left; margin-top:0"/>
                                    </td>
                                    <td valign="bottom"><img width="58" height="58" alt="image"  src="{{$mail_data['space']['processed_buyer_logo']?composeEmailURL(composeUrl($mail_data['space']['processed_buyer_logo'])):composeEmailURL(composeUrl($mail_data['space']['buyer_logo']))}}" style="width:58px;height:58px; " />
                                    </td>
                                 </tr>
                              </table>
                           </td>
                           <td  style="padding-left:15px;">
                              The&nbsp;{{str_replace(' ','&nbsp;',$mail_data['space']['share_name'])}}<br>Client Share
                           </td>
                        </tr>
                        <tr>
                           <td colspan="2"><img alt="image"  src="{{ $mail_data['path'] }}/images/email-bg.jpg"></td>
                        </tr>
                     </tbody>
                  </table>
               </td>
            </tr>
            <tr>
               <td colspan="2" align="left" bgcolor="#ffffff" style="background-color: #ffffff; padding: 0px; border-radius: 0 0 6px 6px; box-shadow: 0 2px 4px 0 rgba(0,0,0,0.16);">
                  <table cellspacing="0" style="font-family: arial; font-size: 15px ;color: #424242; line-height: 24px; width: 100%;font-family: arial;">
                     <tr>
                        <td style="padding: 30px 65px 10px;">Hi {{$mail_data['admin']['user']['first_name']}},</td>
                     </tr>
                     <tr>
                        <td style="padding: 10px 65px;">The {{Carbon\Carbon::now()->subMonth(3)->format('F Y').' - '.Carbon\Carbon::now()->subMonth(1)->format('F Y')}} feedback collection window is open for your {{$mail_data['space']['share_name']}} Client Share. Currently {{$mail_data['space']['feedback_submitted']}}/{{$mail_data['space']['buyers']}} Buyers from the {{$mail_data['space']['company_name']}} community have given feedback. Buyers have {{$mail_data['days_left']}} days remaining to give feedback.</td>
                     </tr>
                     <tr>
                        <td style="padding: 10px 65px;">You can send a reminder to the Buyers who have not given feedback.</td>
                     </tr>
                     <tr>
                        <td colspan="1" style="padding: 10px 65px;">
                           <a href="{{ $mail_data['send_reminder_link'] }}" style="display: block; "><img style="display: inline-block;" src="{{ $mail_data['path'] }}/images/send-reminder.png" height="40" alt="Send reminder"></a>
                        </td>
                     </tr>
                     <tr>
                        <td style="padding: 10px 65px 30px;">Thanks, <br/>The {{$mail_data['space']['share_name']}} Client Share</td>
                     </tr>
                  </table>
               </td>
            </tr>
            <tr>
               <td align="center" style="padding: 0px;" colspan="2">
                  <table align="center" style="font-family: arial; font-size: 13px; color: #A4AAB3; width: 100%;">
                     <tr>
                        <td align="center" style="padding: 24px 46px 8px 46px; padding-bottom: 8px;"><b><a href="mailto:{{trans('messages.mail_template.email')}}" style="color: #757575; text-decoration: none;">{{trans('messages.mail_template.email')}}</b></a></td>
                     </tr>
                     <tr>
                        <td align="center" style="padding: 0 46px; color:#9E9E9; line-height: 19px; padding-bottom: 16px;">Copyright &copy; 2016 Client Share, All rights reserved.<br/> @include('email.office_address')</td>
                     </tr>
                     <tr>
                        <td align="center" style="padding: 0 46px 24px 46px;"><a href="{{trans('messages.mail_template.twitter_link')}}"><img alt="image"  src="{{ $mail_data['path'] }}/images/twitterEMAIL.png"></a><a href="https://www.linkedin.com/company/10965081"><img alt="image"  src="{{ $mail_data['path'] }}/images/linkedinEMAIL.png" style="margin-left: 20px;"></a></td>
                     </tr>
                  </table>
               </td>
            </tr>
         </table>
      </div>
   </body>
</html>