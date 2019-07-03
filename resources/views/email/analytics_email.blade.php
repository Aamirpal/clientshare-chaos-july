<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>Clientshare</title>
</head>
<body style="font-family: arial; background-color: #e0e0e0 ; margin: 0; background-image: url('<?php echo $data['path'];?>/images/white-bg.png'); background-position: center top; background-repeat: repeat-x; margin: 0; ">
<div style="font-family: arial; background-color: #e0e0e0 ; margin: 0; background-image: url('<?php echo $data['path'];?>/images/white-bg.png'); background-position: center top; background-repeat: repeat-x; margin: 0; ">
<table cellspacing="0" cellpadding="0" align="center" style="font-family: arial; font-size:15px; max-width: 600px; margin: 0 auto;  border-radius:6px 6px 0 0;">
	<tr>
 		 <td colspan="2" height="94"></td>
  </tr>
	<tr>
        <td colspan="2" align="center" style="background-color: #424242; border-radius:6px 6px 0 0;">
                  <table style="font-family:arial;font-size:21px;color:#FFFFFF;border-radius:6px 6px 0 0;font-weight:500;display:inline-block;border-collapse:collapse;" cellspacing="0" cellpadding="0" border="0" bgcolor="#424242">
                    <tbody>
					<tr><td colspan="2"><img alt="image"  src="<?php echo $data['path'];?>/images/email-bg.jpg" ></td></tr>
					<tr>
                      <td style="padding-left: 5px;">
                        <img alt="image"  src="<?php echo $data['path'];?>/images/ClientShare_Branding.png" >
                      </td>
                    </tr>
					<tr><td colspan="2"><img alt="image"  src="<?php echo $data['path'];?>/images/email-bg.jpg" ></td></tr>
                  </tbody></table>
                </td>
       </tr>

<tr>
	<td colspan="2" align="left" bgcolor="#ffffff" style="background-color: #ffffff; padding: 0px; border-radius: 0 0 6px 6px; box-shadow: 0 2px 4px 0 rgba(0,0,0,0.16);">
		<table cellspacing="0" style="font-family: arial; font-size: 15px ;color: #424242; line-height: 24px; width: 100%;font-family: arial;">
			  <tr>
				<td style="padding: 30px 65px 10px;">Hello {{$data['user_first_name']}},</td>
			  </tr>
			   <tr>
			   <td style="padding: 10px 65px;"><?php echo date('F', strtotime(date('Y-m')." -1 month"));?> Analytics report(s) are now available on Client Share. </td>
			   </tr>
			   <tr>
			   <?php $cur_date = date('Y-m-d'); ?>    

				<td style="padding: 10px 65px 30px;">To view your Analytics 
				@if((isset($data['last_space']['last_space'])) && (isset($data['last_space_created'])) )
					@if((strtotime($data['last_space_created']) < strtotime($cur_date)))
						@php $space_ID = $data['last_space']['last_space']; @endphp
					@else
						@php $space_ID = $data['space_id']; @endphp 
					@endif
				@else 
					@php $space_ID = $data['space_id']; @endphp
				@endif
				<a href="{{$data['path']}}/analytics/{{$space_ID}}/<?=date('n').'/'.date('Y')?>?alert=true&email={{$data['to']}}&via_email=1">click here</a></td>
			   </tr>
			   <tr>
			   	<td style="padding: 10px 65px 0px;">Thanks</td>
			   </tr>
			   <tr>
			   	<td style="padding: 10px 65px 30px;">The Client Share Team</td>
			   </tr>
		</table>
	</td>
    </tr>
   
	<tr>
	<td align="center" style="padding: 0px;" colspan="2">
		<table align="center" style="font-family: arial; font-size: 13px; color: #A4AAB3; width: 100%;">

		<tr>
			<td align="center" style="padding: 24px 46px 8px 46px; padding-bottom: 8px;"><b><a href="mailto:hello@myclientshare.com" style="color: #757575; text-decoration: none;">hello@myclientshare.com</b></a></td>
		</tr>
		<tr>
			<td align="center" style="padding: 0 46px; color:#9E9E9; line-height: 19px; padding-bottom: 16px;">Copyright &copy; 2016 Client Share, All rights reserved.<br/> @include('email.office_address')</td>
		</tr>

	<tr>
			<td align="center" style="padding: 0 46px 24px 46px;"><a href="https://twitter.com/myclientshare"><img alt="image"  src="<?php echo $data['path'];?>/images/twitterEMAIL.png"></a><a href="https://www.linkedin.com/company/10965081"><img alt="image"  src="<?php echo $data['path'];?>/images/linkedinEMAIL.png" style="margin-left: 20px;"></a></td>
		</tr>
 </table>
	</td>
    </tr>

</table>

</div>



</body>
</html>
