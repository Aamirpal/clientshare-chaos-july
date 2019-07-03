<?php $ssl = false;
   if(env('APP_ENV')!='local')
     $ssl = true;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <title>Clientshare</title>
      <link href="https://fonts.googleapis.com/css?family=Lato:300,300i,400,400i,700,700i,900,900i" rel="stylesheet">
      <style>
         
      </style>
   </head>
   <body style="font-family: arial; background-color: #e0e0e0 ; margin: 0; background-image: url('{{ $mail_data['path'] }}/images/white-bg.png'); background-position: center top; background-repeat: repeat-x; margin: 0; ">
      <div style="font-family: arial; background-color: #e0e0e0 ; margin: 0; background-image: url('{{ $mail_data['path'] }}/images/white-bg.png'); background-position: center top; background-repeat: repeat-x; margin: 0; ">



         <table style="font-family: arial; font-size:15px; max-width: 680px !important; margin: 0 auto;  border-radius:6px 6px 0 0;" cellspacing="0" cellpadding="0" align="center">
      <tbody>
         <tr>
            <td colspan="2" height="94"></td>
         </tr>
         <tr>
            <td colspan="2" style="background-color:#212121; border-radius:6px 6px 0 0;" align="center">
               <table style="font-family:arial;font-size:21px;color:#FFFFFF;border-radius:6px 6px 0 0;font-weight:500;display:inline-block;border-collapse:collapse;" cellspacing="0" cellpadding="0" border="0" bgcolor="#212121">
                  <tbody>
                     <tr>
                        <td style="padding-top:22px;padding-bottom:22px;">
                           <table>
                              <tbody>
                                 <tr>
                                    <td valign="middle">
                                       <img alt="image" src="{{ $mail_data['path'] }}/images/CS_logo.png" style="vertical-align:super;float:left;margin:0;width:159px;">
                                    </td>
                                 </tr>
                              </tbody>
                           </table>
                        </td>
                     </tr>
                  </tbody>
               </table>
            </td>
         </tr>
         <tr>
            <td colspan="2" style="background-color: #f5f5f5; padding: 0px; border-radius: 0 0 6px 6px; box-shadow: 0 2px 4px 0 rgba(0,0,0,0.16);" bgcolor="#F9F9F9" align="left">
               <table style="font-size: 15px ;color: #424242; line-height: 24px; width: 100%;font-family: arial;" cellspacing="0">
                  <tbody>
                     <tr>
                        <td width="10%"></td>
                        <td width="50%">
                           <table width="100%">
                              <tbody>
                                 <tr>
                                    <td style="padding:40px 0 23px 0;">
                                     @if(strpos($mail_data['email_body'], '[Name],'))
                                        Hi {{ucfirst($mail_data['receiver'])}},
                                        {!! explode("[Name],", nl2br($mail_data['email_body']))[1] !!}
                                      @else
                                        {!! nl2br($mail_data['email_body']) !!}
                                      @endif
                                         <br>
                                    </td>
                                 </tr>
                                 <tr>
                                    <td valign="top">
                                      <table style="font-family:arial;font-size:21px;color:#FFFFFF;border-radius:6px 6px 0 0;font-weight:500;display:inline-block;border-collapse:collapse;" cellspacing="0" cellpadding="0" border="0" bgcolor="#212121">
                                         <tbody>
                                           <tr>
                                             <td valign="top" style="background-color:#fff;color:#8192AA;   border: 1px solid #E0E0E0;height:162px;width:234px;">
                                               <table cellspacing="0" cellpadding="0" border="0" style="width:100%">
                                                 <tr>
                                                   <td valign="top" style="padding:17px 17px 0 17px; height:16px;color:#8192AA;font-size:16px;font-weight:600;line-height:16px;">Community Buyer</td>
                                                 </tr>
                                                 <tr>
                                                   <td style="padding:32px 0 8px 0;color:#0D47A1;font-size:2.3em;  font-weight: 600; line-height: 48px;   text-align: center;">{{$mail_data['community_buyers']}}</td>
                                                 </tr>
                                                 <tr>
                                                   <td align="right" style="padding:0 17px 0 0; ">
                                                      <img src="{{ $mail_data['path'] }}/images/ic_person.png">
                                                   </td>
                                                 </tr>
                                               </table>
                                             </td>
                                             <td style="background-color:#f5f5f5;border-right: 1px solid #E0E0E0;border-top: 1px solid #f5f5f5; height:162px;width:14px;">

                                             </td>
                                             <td valign="top" style="background-color:#fff;color:#8192AA;   border: 1px solid #E0E0E0; height:162px;width:234px;">
                                               <table cellspacing="0" cellpadding="0" border="0" style="width:100%">
                                                 <tr>
                                                   <td valign="top" style="padding:17px 17px 0 17px; height:16px;color:#8192AA;font-size:16px;font-weight:600;line-height:16px;">Community Seller</td>
                                                 </tr>
                                                 <tr>
                                                   <td style="padding:32px 0 8px 0; color: #0D47A1;font-size:2.3em;  font-weight: 600; line-height: 48px;   text-align: center;">{{$mail_data['community_sellers']}}</td>
                                                 </tr>
                                                 <tr>
                                                   <td align="right" style="padding:0 17px 0 0; ">
                                                      <img src="{{ $mail_data['path'] }}/images/ic_person.png">
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
                                             <td valign="top" style="background-color:#fff;color:#8192AA;   border: 1px solid #E0E0E0;height:162px;width:234px;">
                                               <table cellspacing="0" cellpadding="0" border="0" style="width:100%">
                                                 <tr>
                                                   <td valign="top" style="padding:17px 17px 0 17px; height:16px;color:#8192AA;font-size:16px;font-weight:600;line-height:16px;">Posts This Month</td>
                                                 </tr>
                                                 <tr>
                                                   <td style="padding:32px 0 8px 0; color: #0D47A1;font-size:2.3em;  font-weight: 600; line-height: 48px;   text-align: center;">{{$mail_data['month_posts']}}</td>
                                                 </tr>
                                                 <tr>
                                                   <td align="right" style="padding:0 17px 0 0; ">
                                                      <img src="{{ $mail_data['path'] }}/images/ic_file.png">
                                                   </td>
                                                 </tr>
                                               </table>
                                             </td>
                                             <td style="background-color:#f5f5f5;border-right: 1px solid #E0E0E0;border-top: 1px solid #f5f5f5; height:162px;width:14px;">

                                             </td>
                                             <td valign="top" style="background-color:#fff;color:#8192AA;   border: 1px solid #E0E0E0;height:162px;width:234px;">
                                               <table cellspacing="0" cellpadding="0" border="0" style="width:100%">
                                                 <tr>
                                                   <td valign="top" style="padding:17px 17px 0 17px; height:16px;color:#8192AA;font-size:16px;font-weight:600;line-height:16px;">CSI</td>
                                                 </tr>
                                                 <tr>
                                                   <td style="padding:32px 0 8px 0; color: #0D47A1;font-size:2.3em;  font-weight: 600; line-height: 48px;   text-align: center;">{{$mail_data['csi_score']}}</td>
                                                 </tr>
                                                 <tr>
                                                   <td align="right" style="padding:0 17px 0 0; ">
                                                      <img src="{{ $mail_data['path'] }}/images/ic_csi.png">
                                                   </td>
                                                 </tr>
                                               </table>
                                             </td>
                                           </tr>
                                        </tbody>
                                      </table>
                                    </td>
                                 </tr>
                              </tbody>
                           </table>
                        </td>
                        <td width="10%"></td>
                     </tr>
                     <tr>
                        <td style="padding: 0px; font-family: arial;" colspan="4" align="center">
                           <table style="font-size: 13px; color: #A4AAB3; width: 100%; font-family: arial;" align="center">
                              <tbody>
                                 <tr>
                                    <td style="padding: 4px 0 0; font-family: arial;color:#9e9e9e;text-decoration:none;font-size:14px;line-height:20px;" align="center">
                                       <a href="#" style="text-decoration:underline;color:#9e9e9e;">Click here</a> to change your alert preferences
                                    </td>
                                 </tr>
                                 <tr>
                                    <td style="padding: 0 46px 0 46px; font-family: arial;" align="center"><b><a href="mailto:hello@myclientshare.com" style="color:#9e9e9e; text-decoration: none; font-family: arial; font-size:14px;line-height:20px;font-weight:600;">hello@myclientshare.com</a></b></td>
                                 </tr>
                                 <tr>
                                    <td style="padding: 0 46px; color:#9e9e9e; line-height: 19px; padding-bottom: 8px; font-family: arial;" align="center">Copyright © 2018 Client Share, All rights reserved.<br> <a href="javascript:void(0)" style="color:#9e9e9e">12-18 Hoxton Street, London N1 6NG</a></td>
                                 </tr>
                                 <tr>
                                    <td style="padding: 0 46px 24px 46px; font-family: arial;" align="center">
                                       <a href="https://twitter.com/myclientshare">
                                          <img alt="image" src="{{ $mail_data['path'] }}/images/twitterEMAIL.png">
                                       </a>
                                       <a href="https://www.linkedin.com/company/10965081">
                                          <img alt="image" src="{{ $mail_data['path'] }}/images/linkedinEMAIL.png" style="margin-left: 20px;">
                                       </a>
                                    </td>
                                 </tr>
                              </tbody>
                           </table>
                        </td>
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