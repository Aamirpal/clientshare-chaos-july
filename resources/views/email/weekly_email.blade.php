<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <title>Clientshare</title>
      <link href="https://fonts.googleapis.com/css?family=Lato:300,300i,400,400i,700,700i,900,900i" rel="stylesheet">
   </head>
   <body style="font-family: Arial,Helvetica Neue,Helvetica,sans-serif; background-color: #e0e0e0 ; margin: 0; background-image: url('{{ $data['path'] }}/images/bg-01.png'); background-position: center top; background-repeat: repeat-x; margin: 0; ">
      <div style="font-family: Arial,Helvetica Neue,Helvetica,sans-serif; background-color: #e0e0e0 ; margin: 0; background-image: url('{{ $data['path'] }}/images/bg-01.png'); background-position: center top; background-repeat: repeat-x; margin: 0; ">
         <table cellspacing="0" cellpadding="0" align="center" style="font-family: Arial,Helvetica Neue,Helvetica,sans-serif; font-size:14px; max-width: 600px; margin: 0 auto;  border-radius:6px 6px 0 0;">
            <tr>
               <td colspan="2" height="94"></td>
            </tr>
            <tr>
               <td colspan="2" align="center" style="background-color: #424242; border-radius:6px 6px 0 0; padding: 45px 0px;">
                  <table style="font-family: Arial,Helvetica Neue,Helvetica,sans-serif; font-size:21px;color:#FFFFFF;border-radius:6px 6px 0 0;font-weight:500;display:inline-block;border-collapse:collapse;" cellspacing="0" cellpadding="0" border="0" bgcolor="#424242">
                     <tbody>
                        <tr>
                           <td style="padding-left: 5px;">
                              <img src="{{$data['path']}}/images/ic_clientShare1.png" alt="">
                           </td>
                           <td  style="padding-left:15px; color:#f1f1f1;" color="#f1f1f1">
                              <span style="font-family: Arial,Helvetica Neue,Helvetica,sans-serif;">CLIENTSHARE</span>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </td>
            </tr>
            <tr>
               <td align="left" colspan="3" bgcolor="#ffffff" style="background-color: #ffffff; padding: 0px; border-radius: 0 0 6px 6px; box-shadow: 0 2px 4px 0 rgba(0,0,0,0.16);">
                  <table cellspacing="0" style="font-family: Arial,Helvetica Neue,Helvetica,sans-serif; font-size: 14px ;color: #424242; line-height: 24px; width: 100%;">
                     <tr>
                        <td style="padding:30px 50px 0px 50px; font-family: Arial,Helvetica Neue,Helvetica,sans-serif; font-size: 14px;">Hello {{$data['user_first_name']}},</td>
                     </tr>
                     <tr>
                        <td style="padding:10px 50px 20px 50px; font-family: Arial,Helvetica Neue,Helvetica,sans-serif; font-size: 14px;"> <b>@if($data['total_post'] > 1 || $data['total_post'] == 0 ) {{$data['total_post']}} </b> new posts have @else {{$data['total_post']}} new post has </b> @endif been added to Client Share this week</td>
                     </tr>
                     @foreach ($user_info['share'] as $share)
                     <tr style=" margin: 0; padding: 0;">
                        <td>
                           <hr size="1" style="margin: 0;" width="100%" style="color:#E0E0E0;" />
                        </td>
                     </tr>
                     <tr>
                        <td valign="middle" colspan="3" style="padding:24px 50px 0px 50px;">
                           <table align="center" cellpadding="0" cellspacing="0" style="font-family: Arial,Helvetica Neue,Helvetica,sans-serif;">
                              <tr>
                                 <td valign="middle">
                                    <img width="25" height="25" style="vertical-align: middle; border-radius: 50px" src="{{composeEmailURL($share['company_seller_logo'])}}" alt="">			  							
                                 </td>
                                 <td valign="top">
                                    <img width="40" height="40" style="border-radius: 50px;" src="{{composeEmailURL($share['company_buyer_logo'])}}">				
                                 </td>
                                 <td valign="middle" style="font-size:14px; font-family: Arial,Helvetica Neue,Helvetica,sans-serif; padding: 0px 6px;" align="left">
                                    {{$share['share_name']}}
                                 </td>
                              </tr>
                           </table>
                        </td>
                     </tr>
                     <tr>
                        <td colspan="3" style="font-family: Arial,Helvetica Neue,Helvetica,sans-serif; padding:10px 0px 0px 0px; font-size:14px;">
                           @if(isset($share['posts']))
                           @php
                              $total_post = sizeOfCustom($share['posts']);
                              $left_post = $total_post - 3;
                           @endphp
                           <table cellpadding="0" cellspacing="0" width="100%" style="font-family: Arial,Helvetica Neue,Helvetica,sans-serif; font-size:14px;">
                              @php
                                 $count = 1;
                              @endphp
                              @foreach ($share['posts'] as $posts)
                              @if($count > 3) @break  @endif
                              <tr>
                                 <td style="padding: 0px 50px 3px 50px; font-size:14px;"><a href="{{$data['path']}}/clientshare/{{$posts['space_id']}}/{{$posts['id']}}?alert=true&email={{base64_encode($data['to'])}}&via_email=1" style="color: #0D47A1; font-size:14px; font-family: Arial,Helvetica Neue,Helvetica,sans-serif;">{{$posts['post_subject']}}</a> by {{$posts[0]['post_by']}}</td>
                                 @php
                                    $count++;
                                 @endphp
                                 @endforeach
                              </tr>
                              <tr>
                                 <td style="padding: 0px 50px 0px 50px; font-size:14px; font-family: Arial,Helvetica Neue,Helvetica,sans-serif;">
                                    @if($total_post > 3)
                                    <span style="float: left !important; width: 100%; text-align:left !important; padding:0px;">and</span>
                                 </td>
                              </tr>
                              <tr>
                                 <td colspan="2">
                                    <table width="100%">
                                       <tr>
                                          <td style="padding: 0px 50px 10px 50px; font-family: Arial,Helvetica Neue,Helvetica,sans-serif; font-size:14px;">
                                             <span align="left" style="display:inline-block; float: left;text-align: left; padding-right: 5px; font-size:14px;">{{$left_post}} more post(s) this week. </span> 
                                             <span> <a href="{{$data['path']}}/clientshare/{{$share['space_id']}}?alert=true&email={{base64_encode($data['to'])}}&via_email=1" style="color:#0D47A1 !important; font-size:14px;"> Login</a> to view your feed. </span><br></br>  
                                             @else
                                             @if($total_post >= 1 )
                                       <tr>
                                          <td style="padding: 0px 50px 20px 50px; padding-bottom: 20px !important; font-family: Arial,Helvetica Neue,Helvetica,sans-serif;">
                                             <span style="float: left; width: 100%; font-size:14px; text-align: left !important;"><a style="color:#0D47A1;" href="{{$data['path']}}/clientshare/{{$share['space_id']}}?alert=true&email={{base64_encode($data['to'])}}&via_email=1"> Login </a>to add a post.</span>
                                          </td>
                                       </tr>
                                       @else
                                       <tr>
                                          <td style="padding: 0px 50px 20px 50px; padding-bottom: 20px !important; font-family: Arial,Helvetica Neue,Helvetica,sans-serif;">
                                             <span style="float: left; width: 100%; font-size:14px; text-align: left !important;">No new posts this week. <a style="color:#0D47A1;" href="{{$data['path']}}/clientshare/{{$share['space_id']}}?alert=true&email={{base64_encode($data['to'])}}&via_email=1"> Login </a>to add a post.</span>
                                          </td>
                                       </tr>
                                       @endif
                                       @endif
                                       </td>									
                                       </tr>
                                    </table>
                                 </td>
                              </tr>
                              @else
                              <tr>
                                 <td style="padding: 0px 50px 20px 50px; padding-bottom: 20px !important; font-family: Arial,Helvetica Neue,Helvetica,sans-serif;">
                                    <span style="float: left; width: 100%; font-size:14px; text-align: left !important;">No new posts this week. <a style="color:#0D47A1;" href="{{$data['path']}}/clientshare/{{$share['space_id']}}?alert=true&email={{base64_encode($data['to'])}}&via_email=1"> Login </a>to add a post.</span>
                                 </td>
                              </tr>
                              @endif
                              @endforeach
                              <tr>
                                 <td colspan="2"  style="padding-top: 10px; padding-bottom: 30px; font-family: arial; color: #A4A4A4;font-size: 13px;line-height: 16px;text-align: center;"><font color="#a4a4a4">To unsubscribe from this alert <a href="{{ $data['unsubscribe_share'] }}" style="color: #A4A4A4;">click here</a> to manage your notification settings</font></td>
                              </tr>
                           </table>
                        </td>
                     </tr>
                  </table>
               </td>
            </tr>
         </table>
      </div>
      <table bgcolor="#E0E0E0" width="100%">
         <tr>
            <td align="center" style="padding: 0px;" colspan="2">
               <table align="center" style="font-family: Arial,Helvetica Neue,Helvetica,sans-serif;  font-size: 13px; color:#A4AAB3; width: 100%;">
                  <tr>
                     <td align="center" style="padding:24px 46px 8px 46px; padding-bottom:8px;"><b><a href="mailto:hello@myclientshare.com" style="color: #757575; text-decoration: none;">hello@myclientshare.com</a></b></td>
                  </tr>
                  <tr>
                     <td align="center" style="padding:0 46px; color:#9E9E9; line-height: 19px; padding-bottom: 16px;">Copyright &copy; 2016 Client Share, All rights reserved.<br/> @include('email.office_address')</td>
                  </tr>
                  <tr>
                     <td align="center" style="padding:0 46px 24px 46px;"><a href="https://twitter.com/myclientshare"><img src="{{ $data['path'] }}/images/twitterEMAIL.png"></a><a href="https://www.linkedin.com/company/10965081"><img src="{{ $data['path'] }}/images/linkedinEMAIL.png" style="margin-left: 20px;"></a></td>
                  </tr>
               </table>
            </td>
         </tr>
      </table>
   </body>
</html>