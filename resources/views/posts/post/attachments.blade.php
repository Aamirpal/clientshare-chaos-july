<div class="file-attachment-col full-width">
   <h4 class="m-0">Attachments</h4>
   @foreach($post['post_media'] as $attachment)
   @php
      $ext = explode('.',$attachment['post_file_url']);
      $ext = array_pop($ext);
   @endphp
   <a class="attach-link full-width" href="javascript:void(0)" onclick="viewFile('{{$attachment['post_file_url']}}', '{{$ext}}', '{{$attachment['metadata']['originalName']}}' )">
   
      <img class="" src="{{ fileIcon($attachment['metadata']['s3_name'])}}"/>
      <span class="attachment-text">{{$attachment['metadata']['originalName']}}</span>
   </a>
   @endforeach
   <span class="more-attachments full-width"><a href="javascript:void(0)">+3 more attachments</a></span>
</div>