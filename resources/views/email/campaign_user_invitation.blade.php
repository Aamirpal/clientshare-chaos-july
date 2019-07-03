@include('email.mailer_header')
  <tr>
      <td colspan="2" height="94"></td>
  </tr>
	<tr>
          <td colspan="2" align="center" style="background-color: #424242; border-radius:6px 6px 0 0;">
                  <table style="font-family:arial;font-size:21px;color:#FFFFFF;border-radius:6px 6px 0 0;font-weight:500;display:inline-block;border-collapse:collapse;" cellspacing="0" cellpadding="0" border="0" bgcolor="#424242">
                    <tbody>
					<tr><td colspan="2"><img alt="image"  src="{{ $data['mail']['path'] }}/images/email-bg.jpg"></td></tr>
					<tr>
                      <td style="padding-left: 5px;">
						<table>
                          <tr>
                          <td valign="middle">
                             <img width="48" height="48" alt="image"  src="{{composeEmailURL($data['mail']['company_seller_logo'])}}" style="width:48px; border-radius:50px; vertical-align:super; height:48px; float: left; margin-top: 0"/>
                          </td>                           
                          <td valign="bottom">
                          	<img width="58" height="58" alt="image"  src="{{composeEmailURL($data['mail']['company_buyer_logo'])}}" style="width:58px;height:58px;border-radius:50px;" />
                          </td>
                          </tr>
                        </table>
						
                      
						
                      </td>
                      <td  style="padding-left:15px;">
                        <span style="color: #f1f1f1;">The {{ $data['mail']['share_name'] }} Client Share</span>
                      </td>
                    </tr>
					<tr><td colspan="2"><img alt="image"  src="{{ $data['mail']['path'] }}/images/email-bg.jpg"></td></tr>
                  </tbody></table>
                </td>
       </tr>




   <tr>
	<td align="left" colspan="2" bgcolor="#ffffff" style="background-color: #ffffff; padding: 0px; border-radius: 0 0 6px 6px; box-shadow: 0 2px 4px 0 rgba(0,0,0,0.16);">
		<table cellspacing="0" style="font-family: arial; font-size: 15px ;color: #424242; line-height: 24px; width: 100%;font-family: arial;">
			  <tr>
				<td style="padding: 30px 65px 10px;">Hello {{ $data['mail']['receiver_first_name'] }},</td>
			  </tr>

			  <tr>
				<td style="padding: 10px 65px;">I am inviting you to join me on this Client Share which has been set-up to share key information with you. The site is personalised, mobile, easy to share with colleagues and simple to use. It's a great way to ensure you have secure access to the latest updates and content at anytime, anywhere. Feel free to invite colleagues to join via the Client Share community.</td>
			  </tr>
			  <tr>
				<td style="padding: 10px 65px;"><a href="{{ $data['mail']['link'] }}" style="color: #0d47a1;">Please click here to accept the invitation.</a></td>
			   </tr>
			   <tr>
				<td style="padding: 10px 65px 30px;">Thanks, <br/>The&nbsp;<?php echo str_replace(' ','&nbsp;',$data['mail']['share_name']); ?> Client Share<br/>On behalf of {{$data['mail']['sender_first_name']}} {{$data['mail']['sender_last_name']}}</td>
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
			<td align="center" style="padding: 0 46px 24px 46px;"><a href="https://twitter.com/myclientshare"><img alt="image"  src="{{ $data['mail']['path'] }}/images/twitterEMAIL.png"></a><a href="https://www.linkedin.com/company/10965081"><img alt="image"  src="{{ $data['mail']['path'] }}/images/linkedinEMAIL.png" style="margin-left: 20px;"></a></td>
		</tr>
 </table>
	</td>
    </tr>

</table>

</div>


</body>
</html>
