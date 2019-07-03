<?php $ssl = false;
   if(env('APP_ENV')!='local')
     $ssl = true;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <title>Clientshare</title>
      <link href="https://fonts.googleapis.com/css?family=Lato:300,300i,400,400i,700,700i,900,900i" rel="stylesheet">
      
   </head>
   <body style="font-family:arial; background-color: #fff ; margin: 0; background-position: center top; background-repeat: repeat-x; margin:0;">
      <div style="font-family: arial; background-color: #fff ; margin: 0; padding-bottom: 40px; background-image: url('{{ $mail_data['path'] }}/images/white-bg.png'); background-position: center top; background-repeat: repeat-x; margin: 0; ">
         <table width="680" style="font-family: arial; font-size:15px; max-width: 680px !important; margin: 0 auto;" cellspacing="0" cellpadding="0" align="center">
            <tbody>
               <tr>
                  <td colspan="2" height="94"></td>
               </tr>
               <tr>
                  <td colspan="2" style="border-radius:6px 6px 0 0; overflow: hidden;" align="left">
                     
                     @if(!empty($mail_data['space_info']['email_header']))
                      <table  style="background-size: cover; background-position: center center; background-repeat:no-repeat; font-family:arial;font-size:21px;color:#FFFFFF;border-radius:6px 6px 0 0;font-weight:500;display:inline-block;border-collapse:collapse;  padding:0px 0px; width: 100%;border-top:16px solid #212121;" width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#212121">
                     @else
                     <table  style="background-image:url({{$mail_data['space_info']['background_logo']}});  background-size: cover; background-position: center center; background-repeat:no-repeat; font-family:arial;font-size:21px;color:#FFFFFF;border-radius:6px 6px 0 0;font-weight:500;border-collapse:collapse;  padding:0px 65px 0px; width: 100%;border-top:16px solid #212121;" background="{{$mail_data['space_info']['background_logo']}}" width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#212121">
                      @endif
                       <tbody>
                        @if(!empty($mail_data['space_info']['email_header']))
                        <tr>
                           <td colspan="2" style="height:104px;display:block;">
                            <img src="{{composeEmailURL($mail_data['space_info']['email_header'])}}" />
                          </td>
                        </tr>
                        @else
                        <tr>
                           <td colspan="2" >
                           <img height="20" alt="image" src="{{ $mail_data['path'] }}/images/email-bg.jpg">
                           </td>
                        </tr>
                        <tr>
                           <td style="padding-left: 60px; width: 95px">
                              <table>
                                 <tbody>
                                 <tr>
                                    <td valign="middle">
                                       <img alt="image" src="{{composeEmailURL($mail_data['space_info']['seller_processed_logo'])}}" style="width:36px;vertical-align:super; height:36px; float: left; margin-top: -2px;position:relative;left:5px;" width="36" height="36">
                                    </td>
                                    <td valign="bottom">
                                        <img alt="image" src="{{composeEmailURL($mail_data['space_info']['buyer_processed_logo'])}}" style="width:44px;height:44px; " width="44" height="44">
                                    </td>
                                 </tr>
                              </tbody>
                              </table>
                           </td>
                           <td style="padding-left:15px; text-align: left; font-weight: 600;">{{$mail_data['space_info']['share_name']}} Clientshare
                           </td>
                        </tr>
                        <tr>
                           <td colspan="2"><img height="20" alt="image" src="{{ $mail_data['path'] }}/images/email-bg.jpg"></td>
                        </tr>
                        @endif
                     </tbody>
                     </table>
                  </td>
               </tr>
               <tr>
                  <td colspan="2" style="background-color: #f5f5f5; padding: 0px;" bgcolor="#F9F9F9" align="left">
                     <table style="font-size: 15px;color: #424242; line-height: 24px; width: 100%;font-family: arial; width: 100%;" cellspacing="0">
                        <tbody>
                           <tr>
                             
                              <td style="padding: 30px 65px 4px;">
                                          @if(strpos($mail_data['email_body'], '[Name],'))
                                             Hi {{ucfirst($mail_data['receiver'])}},
                                             {!! explode("[Name],", nl2br($mail_data['email_body']))[1] !!}
                                           @else
                                             {!! nl2br($mail_data['email_body']) !!}
                                           @endif
                                          </td>
                                       </tr>
                                       <tr>
                                          <td style="color:#0d47a1;font-size:20px;font-weight:600;line-height:24px;text-align:center;padding-top:20px;padding-bottom:15px;">
                                            Your Statistics
                                          </td>
                                       </tr>
                                       <tr>
                                          <td valign="top" style="padding:11px 65px 10px;">
                                            <table style="background: #f5f5f5;width:100%;font-family:arial;font-size:21px;color:#FFFFFF;border-radius:6px 6px 0 0;font-weight:500;display:inline-block;border-collapse:collapse;" cellspacing="0" cellpadding="0" border="0" bgcolor="#212121">
                                               <tbody>
                                                 <tr>
                                                   <td valign="top" style="background-color:#fff;color:#8192AA;   border: 1px solid #E0E0E0;height:162px;width:266px;">
                                                     <table cellspacing="0" height="100%" cellpadding="0" border="0" style="width:100%">
                                                       <tr>
                                                         <td colspan="2" valign="middle" style="padding:17px 17px 0 17px; height:16px;color:#8192AA;font-size:16px;font-weight:600;line-height:16px;text-align:left;">Customer Community</td>
                                                       </tr>
                                                       <tr>
                                                         <td valign="middle" width="50%" style="color:#0D47A1;font-size:32px;  font-weight: 600; line-height: 48px; text-align: center;">{{$mail_data['community_buyers']}}</td>
                                                         <td width="50%" valign="middle" style="color:#0D47A1;font-size:32px;  font-weight: 600; line-height: 48px; text-align: center; padding-top: 20px;">
                                                          <table style="text-align: center;" width="100%" valign="middle">
                                                            <tr>
                                                            <td valign="middle" style="color: #424242; text-align: center; font-size: 32px;  font-weight: 600; line-height: 48px;">
                                                                @if($mail_data['community_buyers_change'] > 0)
                                                                  <img alt="image" src="{{ $mail_data['path'] }}/images/ic_plus.png">
                                                                @endif
                                                                @if($mail_data['community_buyers_change'] < 0)
                                                                  <img alt="image" src="{{ $mail_data['path'] }}/images/ic_minus.png">
                                                               @endif
                                                              {{abs($mail_data['community_buyers_change'])}}</td>
                                                            </tr>
                                                            <tr>
                                                       <td colspan="2" style="color: #757575; font-size:014px; line-height: 20px; text-align: center;">
                                                                This month
                                                              </td>
                                                       </tr>
                                                          </table>
                                                         </td>
                                                       </tr>

                                                     </table>
                                                   </td>
                                                   <td style="background-color:#f5f5f5;border-right: 1px solid #E0E0E0;border-top: 1px solid #f5f5f5; height:162px;width:14px;">

                                                   </td>
                                                    <td valign="top" style="background-color:#fff;color:#8192AA;   border: 1px solid #E0E0E0;height:162px;width:266px;">
                                                     <table cellspacing="0" height="100%" cellpadding="0" border="0" style="width:100%">
                                                       <tr>
                                                         <td colspan="2" valign="middle" style="padding:17px 17px 0 17px; height:16px;color:#8192AA;font-size:16px;font-weight:600;line-height:16px;text-align:left;">Supplier Community</td>
                                                       </tr>
                                                       <tr>
                                                         <td valign="middle" width="50%" style="color:#0D47A1;font-size:32px;  font-weight: 600; line-height: 48px; text-align: center;">{{$mail_data['community_sellers']}}</td>
                                                         <td width="50%" valign="middle" style="color:#0D47A1;font-size:32px;  font-weight: 600; line-height: 48px; text-align: center; padding-top: 20px;">
                                                          <table style="text-align: center;" width="100%" valign="middle">
                                                            <tr>
                                                              <td valign="middle" style="color: #424242; text-align: center; font-size: 32px;  font-weight: 600; line-height: 48px;">
                                                                @if($mail_data['community_sellers_change'] > 0)
                                                                  <img alt="image" src="{{ $mail_data['path'] }}/images/ic_plus.png">
                                                                @endif
                                                                @if($mail_data['community_sellers_change'] < 0)
                                                                  <img alt="image" src="{{ $mail_data['path'] }}/images/ic_minus.png">
                                                               @endif
                                                               {{abs($mail_data['community_sellers_change'])}}
                                                              </td>
                                                            </tr>
                                                            <tr>
                                                       <td colspan="2" style="color: #757575; font-size:014px; line-height: 20px; text-align: center;">
                                                                This month
                                                              </td>
                                                       </tr>
                                                          </table>
                                                         </td>
                                                       </tr>

                                                     </table>
                                                   </td>
                                                 </tr>
                                                 <tr>
                                                   <td style="height:14px;background-color:#f5f5f5; border-bottom:1px solid #E0E0E0; border-left:1px solid #f5f5f5;"></td>
                                                   <td style="height:14px;background-color:#f5f5f5; border:1px solid #f5f5f5"></td>
                                                   <td style="height:14px;background-color:#f5f5f5; border-bottom:1px solid #E0E0E0; border-right:1px solid #f5f5f5;"></td>
                                                 </tr>

                                                 <tr>
                                                   <td valign="top" style="background-color:#fff;color:#8192AA;   border: 1px solid #E0E0E0;height:162px;width:266px;">
                                                     <table cellspacing="0" height="100%" cellpadding="0" border="0" style="width:100%">
                                                       <tr>
                                                         <td colspan="2" valign="middle" style="padding:17px 17px 0 17px; height:16px;color:#8192AA;font-size:16px;font-weight:600;line-height:16px;text-align:left;">Total Posts</td>
                                                       </tr>
                                                       <tr>
                                                         <td valign="middle" width="50%" style="color:#0D47A1;font-size:32px;  font-weight: 600; line-height: 48px; text-align: center;">{{$mail_data['total_posts']}}</td>
                                                         <td width="50%" valign="middle" style="color:#0D47A1;font-size:32px;  font-weight: 600; line-height: 48px; text-align: center; padding-top: 20px;">
                                                          <table style="text-align: center;" width="100%" valign="middle">
                                                            <tr>
                                                            <td valign="middle" style="color: #424242; text-align: center; font-size: 32px;  font-weight: 600; line-height: 48px;">
                                                                @if($mail_data['month_posts'] > 0)
                                                                  <img alt="image" src="{{ $mail_data['path'] }}/images/ic_plus.png">
                                                                @endif
                                                                @if($mail_data['month_posts'] < 0)
                                                                  <img alt="image" src="{{ $mail_data['path'] }}/images/ic_minus.png">
                                                               @endif
                                                               {{abs($mail_data['month_posts'])}}
                                                              </td>
                                                            </tr>
                                                            <tr>
                                                       <td colspan="2" style="color: #757575; font-size:014px; line-height: 20px; text-align: center;">
                                                                This month
                                                              </td>
                                                       </tr>
                                                          </table>
                                                         </td>
                                                       </tr>

                                                     </table>
                                                   </td>
                                                   <td style="background-color:#f5f5f5;border-right: 1px solid #E0E0E0;border-top: 1px solid #f5f5f5; height:162px;width:14px;">

                                                   </td>
                                                    <td valign="top" style="background-color:#fff;color:#8192AA;   border: 1px solid #E0E0E0;height:162px;width:266px;">
                                                     <table cellspacing="0" height="100%" cellpadding="0" border="0" style="width:100%">
                                                       <tr>
                                                         <td colspan="2" valign="middle" style="padding:17px 17px 0 17px; height:16px;color:#8192AA;font-size:16px;font-weight:600;line-height:16px;text-align:left;">Client Share Index</td>
                                                       </tr>
                                                       <tr>
                                                         <td valign="middle" width="50%" style="color:#0D47A1;font-size:32px;  font-weight: 600; line-height: 48px; text-align: center;">{{$mail_data['csi_score']}}</td>
                                                         <td width="50%" valign="middle" style="color:#0D47A1;font-size:32px;  font-weight: 600; line-height: 48px; text-align: center; padding-top: 20px;">
                                                          <table style="text-align: center;" width="100%" valign="middle">
                                                            <tr>
                                                              <td valign="middle" style="color: #424242; text-align: center; font-size: 32px;  font-weight: 600; line-height: 48px;">
                                                                @if($mail_data['csi_score_change'] > 0)
                                                                  <img alt="image" src="{{ $mail_data['path'] }}/images/ic_plus.png">
                                                                @endif
                                                                @if($mail_data['csi_score_change'] < 0)
                                                                  <img alt="image" src="{{ $mail_data['path'] }}/images/ic_minus.png">
                                                               @endif
                                                               {{abs($mail_data['csi_score_change'])}}%
                                                              </td>
                                                            </tr>
                                                            <tr>
                                                       <td colspan="2" style="color: #757575; font-size:014px; line-height: 20px; text-align: center;">
                                                                This month
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
                                         <td style="padding-top:22px;padding-bottom:32px;text-align:center;">
                                         <a href="{{ url('/', [], env('HTTPS_ENABLE', true)) }}" title="login" ><img alt="image" src="{{ $mail_data['path'] }}/images/login_mail.png"> </a></td>
                                       </tr>
                                    </tbody>
                                 </table>
                              </td>
                              <td width="27"></td>
                           </tr>
                           <tr>
                              <td style="padding: 0px; border-radius: 0 0 6px 6px; font-family: arial; background-color: #f5f5f5;" colspan="2" align="center">
                                 <table style="font-size: 13px; color: #A4AAB3; width: 100%; font-family: arial;" align="center">
                                    <tbody>
                                      <tr>
                                          <td style="padding: 0 46px 24px 46px; font-family: arial;" align="center">
                                             <a style="text-decoration:none" href="https://twitter.com/myclientshare">
                                                <img alt="image" src="{{ $mail_data['path'] }}/images/twitterEMAIL.png">
                                             </a>
                                             <a style="text-decoration:none" href="https://www.linkedin.com/company/10965081">
                                                <img alt="image" src="{{ $mail_data['path'] }}/images/linkedinEMAIL.png" style="margin-left: 20px;">
                                             </a>
                                          </td>
                                       </tr>
                                       <tr>
                                          <td style="padding: 0 46px; color:#9e9e9e; line-height: 19px; padding-bottom: 8px; font-family: arial;" align="center">Copyright © 2018 Client Share, All rights reserved. <br> <a href="javascript:void(0)" style="color:#9e9e9e">12-18 Hoxton Street, London N1 6NG</a></td>
                                       </tr>
                                       <tr>
                                          <td style="padding: 0 46px 0 46px; font-family: arial;" align="center"><b><a href="mailto:hello@myclientshare.com" style="color:#9e9e9e; text-decoration: none; font-family: arial; font-size:14px;line-height:20px;font-weight:600;">hello@myclientshare.com</a></b></td>
                                       </tr>
                                       <tr>
                                          <td style="padding-top:4px; padding-bottom: 32px; font-family: arial;color:#9e9e9e;text-decoration:none;font-size:14px;line-height:20px;" align="center">
                                             <a href="{{ $mail_data['unsubscribe_share'] }}" style="text-decoration:underline;color:#9e9e9e;">Click here</a> to change your alert preferences
                                          </td>
                                       </tr>
                                    </tbody>
                                 </table>
                              </td>
                              <td width="27"></td>
                           </tr>
                        </tbody>
                     </table>
                  </td>
               </tr>
            </tbody>
         </table>
      </div>
   </body>
</html>
