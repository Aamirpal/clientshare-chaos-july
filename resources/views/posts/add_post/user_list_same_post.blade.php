@if(sizeOfCustom($mail_data['post']['access_user_list']) > config('constants.GENERIC.email_alert.user_listing'))
<tr>
   <td style="padding: 55px 0px 10px;">
      <table align="left" cellspacing="0" cellpadding="0">
         <tr>

         	@for($list = $count = 0; $list < sizeOfCustom($mail_data['post']['access_user_list']); $list++)

	         	@php
	         		if($mail_data['post']['access_user_list'][$list]['id'] == $mail_data['recevier_space']['user_id'])
	         			continue;

	         		if($mail_data['post']['access_user_list'][$list]['id'] == $mail_data['from_user']['id'])
	         			continue;

	         		if(sizeOfCustom($mail_data['post']['access_user_list']) > 9 && $count == 6)
	         			continue;
	         		
	         		$count++;
	         	@endphp


	         	@if($mail_data['post']['access_user_list'][$list]['circular_profile_image'])
		            <td style="padding-right: 8px;">
		               <table align="center" height= "32" width= "32" style="border-radius: 50px; text-align: center;" bgcolor="#FFFFFF" cellspacing="0" cellpadding="0">
							<tr>
							 <td height= "32" width= "32" style="height: 32px; width: 32px;">
		                     	<img alt="image" height= "32" width= "32" src="{{composeEmailUrl(composeUrl($mail_data['post']['access_user_list'][$list]['circular_profile_image']))}}" style="border-radius: 50px; height: 32px; width: 32px; vertical-align: middle;">
		                     </td>
		                  </tr>
		               </table>
		            </td>
		        @else 
		        	<td style="padding-right: 8px;">
		               <table align="center" height= "32" width= "32" style="border-radius: 50px; color: #293248; font-weight: bold; font-size: 12px; text-align: center;" bgcolor="#ffffff" cellspacing="0" cellpadding="0">
							<tr>
								<td height= "32" width= "32" style="height:32px; width: 32px; text-align: center;">
                                    
                                    <img height="32" width="32" style="border-radius: 50px; -ms-border-radius: 32px; height: 32px; width: 32px; vertical-align: middle; display: block;"
                                         src="{{ asset('images/name_initials/'.strtolower(ucfirst($mail_data['post']['access_user_list'][$list]['first_name'][0]).''.ucfirst($mail_data['post']['access_user_list'][$list]['last_name'][0])).'.png')}}" />
                                </td>
		                  </tr>
		               </table>
		            </td>
		        @endif
            @endfor

            @if(sizeOfCustom($mail_data['post']['access_user_list']) > 8)
            <td style="padding-right: 8px;">
               <table class="user-count" align="center" height= "32" width= "32" style="border-radius: 50px; color: #0D47A1; font-weight: bold; font-size: 12px; text-align: center;" bgcolor="#FFFFFF" cellspacing="0" cellpadding="0">
					<tr>
						<td height= "32" width= "32" style="height:32px; width: 32px; text-align: center;">+{{sizeOfCustom($mail_data['post']['access_user_list'])-8}}</td>
                  </tr>
               </table>
            </td>
            @endif
         </tr>
      </table>
   </td>
</tr>

<tr>
   <td style="padding: 10px 0 0;font-weight: 500;"><span class="reacted-col">...are also included in this post</span></td>
</tr>
@endif