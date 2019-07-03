
 
 <div class="modal-dialog modal-sm" role="document">

      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{env('APP_URL')}}/images/ic_highlight_removegray.svg" alt="" /></button>
            <h4 class="modal-title" id="myModalLabel">People</h4> 
            <p>The following people found this post useful</p>
         </div>
         <div class="modal-body">
            <ul>
               

 				<?php $data1 = array_reverse($data); ?>
            <?php 
                   usort($data1, function($a, $b) {
                    
                   $a1 = $a['user']['first_name']; //get the name string value
                   $b1 = $b['user']['first_name'];
                  
                   $out = strcasecmp($a1,$b1);
                   if($out == 0){ return 0;} //they are the same string, return 0
                   if($out > 0){ return 1;} // $a1 is lower in the alphabet, return 1
                   if($out < 0){ return -1;} //$a1 is higher in the alphabet, return -1
               }); ?>

 				@foreach($data1 as $endorsed_user)
        <?php //echo'<pre>'; print_r($endorsed_user['space_user']['metadata']['user_profile']['job_title']); die;?>
 				
               <li>
                  <div class="member-wrap">
                  	@if($endorsed_user['user']['profile_image_url'] !='')
                    <span class="view_post_member_profile" style="background-image: url('{{$endorsed_user['user']['profile_image_url']}}');"></span>
                    @else
                    <span class="view_post_member_profile" style="background-image: url('{{ url('/images/dummy-avatar-img.svg',[],env('HTTPS_ENABLE', true)) }}');"></span>
                    @endif
                    
                     <div class="name-wrap">
                        <a href="#!" class="title">{{ ucfirst($endorsed_user['user']['fullname'])}}</a>
                        <span class="time"> 
                        @if(isset($endorsed_user['space_user']['metadata']['user_profile']))
                          {{$endorsed_user['space_user']['metadata']['user_profile']['job_title']}}
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