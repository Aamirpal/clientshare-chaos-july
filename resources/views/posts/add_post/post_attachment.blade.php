@php
   $image_count = anyImage($mail_data['post_media']);
   $video_count = $image_count ? 0 : anyVideo($mail_data['post_media']);
   $previewed_video = anyVideo($mail_data['post_media'], true);
   $previewed_video = $previewed_video?$previewed_video-1:null;
@endphp

@if( sizeOfCustom($mail_data['post_media']) > ($image_count+($video_count-1)) )
<!-- File attached section Start -->
<tr>
   <td style="margin-top:1px;">
      <table cellspacing="0" cellpadding="0" style="width: 100%;">
         @php
            $attachment_counter=0;
            $img_preview_limit=0;
         @endphp
         @foreach($mail_data['post_media'] as $file_index => $attachment)
            @if($attachment_counter >=2 ) @break; @endif
            @if( is_numeric(stripos($attachment['metadata']['mimeType'], 'image')) && $img_preview_limit<4) @php $img_preview_limit++; @endphp @continue @endif
            @if($video_count && $file_index == $previewed_video) @continue @endif
            @php $attachment_counter++; @endphp
            <tr>
               <td style="padding-top: 17px;">
                  <table cellpadding="0" cellspacing="0" width="100%" style="border: 1px solid #E8F0F8;box-sizing: border-box;border-radius: 4px; padding-left: 13px; padding-right: 13px; padding-top: 13px; padding-bottom: 13px; width: 100%;">
                     <tbody>
                        <tr>
                           <td cellspacing="0" cellpadding="0" width="21" style="padding-right: 15px; width: 21px; line-height: normal;">
                              <a href="{{$mail_data['post_page']}}"><img width="21" style="height: auto; width: 21px;" src="{{ fileIcon($attachment['metadata']['s3_name'])}}" alt="image" /></a>
                           </td>
                           <td cellspacing="0" cellpadding="0" style="padding-left: 0px;">
                              <a href="{{$mail_data['post_page']}}" style="color:#212121;text-decoration:none;float: left;line-height: 20px;font-size: 15px;margin-top: 0; margin-bottom: 0px;">{{$attachment['metadata']['originalName']}}</a>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </td>
            </tr>
         @endforeach

         @php
            $listed_attachments = sizeOfCustom($mail_data['post_media']);
            $listed_attachments -= $img_preview_limit;
            $listed_attachments -= $video_count?1:0;
         @endphp
         
         @if( $listed_attachments - config('constants.email.post_alert.display_attachment') > 0 )
            <tr>
               <td cellspacing="0" cellpadding="0" colspan="2" style="color: #0D47A1;font-size: 13px;line-height: 13px;padding-top:16px">
                  <a style="color: #0D47A1; text-decoration: none;" href="{{$mail_data['post_page']}}">+{{$listed_attachments - config('constants.email.post_alert.display_attachment')}} more attachment(s)</a>
               </td>
            </tr>
         @endif
      </table>
   </td>
</tr>
<!-- File attached section End -->
@endif