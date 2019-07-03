<!-- title start -->
<tr>
    <td colspan="2">
      <table width="100%" cellspacing="0" style="width: 100%;">
        <tr>
          <td width="32" height="32" algin="left" valign="middle" style="text-align: center;vertical-align: middle; width: 32px; height: 32px;">
              @php
                $profile_image = $mail_data['from_user']['profile_image_url']?composeEmailUrl(composeUrl($mail_data['from_user']['profile_image'])):$mail_data['app_url'].'/images/user-icon.png';
              @endphp
              <img {{ generateImageThumbnail(composeUrl($mail_data['from_user']['profile_image']), 32, 32) }} src="{{ $profile_image }}" alt="image" />
          </td>
          <td width="5" valign="middle"></td>
          <td valign="middle">
            <p style="margin-top: 0px;margin-bottom:0px;color:#424242;font-weight: 600;line-height: 20px;font-size: 15px;float: left;width: 100%;">{{$mail_data['from_user']['first_name'].' '.$mail_data['from_user']['last_name']}}</p>
            <p style="margin-top: 0px;margin-bottom:0px;color:#9e9e9e;font-size: 12px;line-height: 18px;float: left;width: 100%;">{{ucfirst($mail_data['from_user']['space_user'][0]['metadata']['user_profile']['job_title'])??''}} | {{getCompanyName($mail_data['from_user']['space_user'][0]['company_id'])??''}}</p> 
          </td>
        </tr>
      </table>
</tr>
<!-- title end -->
<!-- paragrph start -->
<tr>
  <td colspan="2" style="color:#212121;line-height:24px;padding-top:16px;">
     {!!nl2br(linkToTest($mail_data['mail']['post_body'], $mail_data['post_page']))!!}
  </td>
</tr>
<!-- paragrph end -->