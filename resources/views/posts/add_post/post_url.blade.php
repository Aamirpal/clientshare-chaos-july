<!-- Title of link section start -->
<tr>
  <td colspan="2" height="16"></td>
</tr>
<tr>
 <td colspan="2"  cellspacing="0" cellpadding="0">
   <table width="100%" cellspacing="0" cellpadding="0">
   <tr>
       <td colspan="3" height="16px;"></td>
     </tr>
     <tr>
       <td cellspacing="0" cellpadding="0" algin="middle" width="124" style="text-align: left;vertical-align: middle;">
          <a style="padding-top: 5px;float: left;width: 124px;text-align: left;padding-bottom: 5px;" href="{{$mail_data['post_page']}}"> <img {{generateImageThumbnail($mail_data['post']['metadata']['get_url_data']['api_response']['thumbnail_url'], 128, 80, false)}} src="{{ $mail_data['post']['metadata']['get_url_data']['api_response']['thumbnail_url'] }}" alt="image" style="vertical-align: middle;" />
        </a>
      </td>
       <td width="16"></td>
  <td valign="middle" cellspacing="0" cellpadding="0" style="padding-right:16px;">
     <table cellspacing="0" cellpadding="0" style="padding:0px;">
     <tr>
       <td cellspacing="0" cellpadding="0" style="width:8%;padding:0px;margin:0;font-size:14px;vertical-align: top;padding-top: 4px;">
         <a href="{{$mail_data['post_page']}}">
          <img height="16" width="16" style="padding:0px;margin:0; width: 16px; height: 16px; " src="{{getFavicon( $mail_data['post']['metadata']['get_url_data']['url'])}}" align="bottom" alt="image" />
       </td>
       <td cellspacing="0" cellpadding="0" style="width: 92%;padding:0px;margin:0;font-size:14px;">
        <a href="{{$mail_data['post_page']}}" style="display:block;text-decoration: none; color: #424242;padding:0px; margin:0;">{{ $mail_data['post']['metadata']['get_url_data']['api_response']['title'] }}</a>
       </td>
     </tr>
     <tr>
       <td colspan="2" cellspacing="0" cellpadding="0" style="color: #424242; text-decoration: none;margin-top:0px;margin-bottom:0px;font-size:12px;line-height:16px;">
      @php
        $link_description = $mail_data['post']['metadata']['get_url_data']['api_response']['description'] ?? '';
      @endphp
       {{ strlen($link_description)>95 ? substr($link_description, 0,95).'...':$link_description }}
     </td>
     </tr>
   </table>
  </td>
     </tr>
   </table>
 </td>
    
</tr>
<!-- Title of link section end -->