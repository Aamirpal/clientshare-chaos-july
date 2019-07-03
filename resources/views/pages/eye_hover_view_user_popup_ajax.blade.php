<div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{env('APP_URL')}}/images/ic_highlight_removegray.svg" alt=""></button>
            <h4 class="modal-title" id="post_views_modal_label">Post Views</h4> 
            <p>The following people have viewed this post</p>
         </div>
         <div class="modal-body">
            <ul>  
            @foreach($post_view as $pv)          
               <li>
                  <div class="member-wrap">
                     @if($pv['user']['profile_image_url'] !='')
                     <span class="view_post_member_profile" style="background-image: url('{{$pv['user']['profile_image_url']}}');"></span>
                     @else
                      <span class="view_post_member_profile" style="background-image: url('{{ url('/images/dummy-avatar-img.svg',[],env('HTTPS_ENABLE', true)) }}');"></span>
                     @endif
                     <div class="name-wrap">
                        <a href="#!" class="title">{{$pv['user']['first_name']}} {{$pv['user']['last_name']}} </a>
                        <span class="time">  
                        @if(isset($pv['user']['space_user'][0]['metadata']['user_profile']))
                          {{$pv['user']['space_user'][0]['metadata']['user_profile']['job_title']}}
                        @endif
                        </span>
                     </div>
                  </div>
               </li>   
            @endforeach          
            </ul>
         </div>
      </div>
   </div>