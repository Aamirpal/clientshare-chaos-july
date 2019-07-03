<div class="update-attachment full-width">
   @foreach($post['post_media'] as $attachment)
      @if( !is_numeric(stripos($attachment['metadata']['mimeType'], 'image')) ) @continue @endif
      <div class="attachment-wrap {{$images_count==1?'single-image':''}}">
         <a href="#">
            <img src="{{getAwsSignedURL($attachment['post_file_url'])}}" alt="image"/>
         </a>
      </div>
   @endforeach
</div>