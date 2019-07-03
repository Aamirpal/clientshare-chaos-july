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
   </head>
   <body style="font-family: arial; background-color: #e0e0e0 ; margin: 0; background-image: url('{{ $data['path'] }}/images/white-bg.png'); background-position: center top; background-repeat: repeat-x; margin: 0; ">
      <div style="font-family: arial; background-color: #e0e0e0 ; margin: 0; background-image: url('{{ $data['path'] }}/images/white-bg.png'); background-position: center top; background-repeat: repeat-x; margin: 0; ">
         <table cellspacing="0" cellpadding="0" align="center" style="font-family: arial; font-size:15px; max-width: 600px !important; margin: 0 auto;  border-radius:6px 6px 0 0;">
            <tr>
               <td colspan="2" height="94"></td>
            </tr>
            <tr>
               <td colspan="2" align="center" style="background-color: #424242; border-radius:6px 6px 0 0;">
                  <table style="font-family:arial;font-size:21px;color:#FFFFFF;border-radius:6px 6px 0 0;font-weight:500;display:inline-block;border-collapse:collapse;" cellspacing="0" cellpadding="0" border="0" bgcolor="#424242">
                     <tbody>
                        <tr>
                           <td colspan="2"><img alt="image"  src="{{ $data['path'] }}/images/email-bg.jpg" ></td>
                        </tr>
                        <tr>
                           <td style="padding-left: 5px;">
                              <table>
                                 <tr>
                                    <td valign="middle">
                                       <img width="48" height="48" alt="image"  src="{{composeEmailURL($data['company_seller_logo'])}}" style="width:48px;vertical-align:super; height:48px; float:left; margin:0; border-radius:50%;"/>
                                    </td>
                                    <td valign="bottom"><img width="58" height="58" alt="image"  src="{{composeEmailURL($data['company_buyer_logo'])}}" style="width:58px;height:58px;border-radius:50%;" /></td>
                                 </tr>
                              </table>
                           </td>
                           <td  style="padding-left:15px; text-align: left;">
                             The&nbsp;<?php echo str_replace(' ','&nbsp;',$data['space_name']); ?><br>Client Share
                           </td>
                        </tr>
                        <tr>
                           <td colspan="2"><img alt="image"  src="{{ $data['path'] }}/images/email-bg.jpg" ></td>
                        </tr>
                     </tbody>
                  </table>
               </td>
            </tr>
            <tr>
               <td align="left" colspan="2" bgcolor="#F9F9F9" style="background-color: #F9F9F9; padding: 0px; border-radius: 0 0 6px 6px; box-shadow: 0 2px 4px 0 rgba(0,0,0,0.16);">
                  <table cellspacing="0" style="font-size: 15px ;color: #424242; line-height: 24px; width: 100%;font-family: arial;">
                     <tbody>
                        <tr>
                           <td width="10%"></td>
                           <td width="80%">
                              <table width="100%">
                                 <tr>
                                    <td style="padding: 30px 65px 10px;">Hi {{ucfirst($data['user_name'])}},</td>
                                 </tr>
                                 <tr>
                                    <td style="padding: 10px 65px;">You requested to download your Client Share Feedback data for {{$data['month']}}. Click the link below to download your data.<br/></td>
                                 </tr>
                                  <tr>
                                    <td style="padding: 20px 65px; text-align: center;"><a href="{{composeEmailURL($data['file_link'])}}" style="display: inline-block;"><img style="display: inline-block;" src="{{ $data['path'] }}/images/feedback-btn.png" height="40" alt="Download link"></a><br/></td>
                                 </tr>

                                 <tr>
                                    <td style="padding: 10px 65px;">If you did not request to download your Client Share Feedback data, please contact <a href="mailto:support@myclientshare.com">support@myclientshare.com</a>.</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 65px 30px;">Regards,<br/>The Client Share Team</td>
                                </tr>
                              </table>
                           </td>
                           <td width="10%"></td>
                        </tr>
                     </tbody>
                  </table>
               </td>
            </tr>
            <tr>
               <td align="center" style="padding: 0px; font-family: arial;" colspan="2">
                  <table align="center" style="font-size: 13px; color: #A4AAB3; width: 100%; font-family: arial;">
                     <tr>
                        <td align="center" style="padding: 24px 46px 8px 46px; padding-bottom: 8px; font-family: arial;"><b><a href="mailto:hello@myclientshare.com" style="color: #757575; text-decoration: none; font-family: arial;">hello@myclientshare.com</a></b></td>
                     </tr>
                     <tr>
                        <td align="center" style="padding: 0 46px; color:#9E9E9; line-height: 19px; padding-bottom: 16px; font-family: arial;">Copyright &copy; 2016 Client Share, All rights reserved.<br/> @include('email.office_address')</td>
                     </tr>
                     <tr>
                        <td align="center" style="padding: 0 46px 24px 46px; font-family: arial;"><a href="https://twitter.com/myclientshare"><img alt="image"  src="{{ $data['path'] }}/images/twitterEMAIL.png"></a><a href="https://www.linkedin.com/company/10965081"><img alt="image"  src="{{ $data['path'] }}/images/linkedinEMAIL.png" style="margin-left: 20px;"></a></td>
                     </tr>
                  </table>
               </td>
            </tr>
         </table>
      </div>
   </body>
</html>