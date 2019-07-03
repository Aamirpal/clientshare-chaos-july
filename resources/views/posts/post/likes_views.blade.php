<div class="like-detail full-width">
   <div class="bottom-section left">
      <a href="javascript:void(0)" class="endrose disable">
      <img src="../images/ic_thumb_up.svg">
      </a>
      <div class="endrose-wrap">
         <div class="endrose">
            @php
               $endorse = '';
               if(in_array(Auth::user()->id, array_column($post['endorse'], 'user_id')))
                  $endorse = 'You';
               elseif(sizeOfCustom($post['endorse']))
                  $endorse = $post['endorse'][0]['user']['fullname'];
               
               if($endorse && sizeOfCustom($post['endorse'])>1)
                  $endorse = $endorse.' & '.(sizeOfCustom($post['endorse'])-1).' other(s)';
            @endphp
            <span>{{$endorse}}</span> liked this
         </div>
      </div>
   </div>
   @if(sizeOfCustom($post['postmediaview']))
   <div class="view-right pull-right">
      <a href="javascript:void(0)" data-toggle="modal">
         <button type="button" class="get_view_user btn" data-toggle="popover" data-trigger="hover" data-placement="bottom" title="" data-html="true" data-content="" data-original-title="Who has viewed this content">
         <img src="../images/ic_visibility.svg" data-html="true">
         <span class="view_eye_content"> {{sizeOfCustom($post['postmediaview'])}} views  </span>
      </button>
      </a>
   </div>
   @endif
</div>