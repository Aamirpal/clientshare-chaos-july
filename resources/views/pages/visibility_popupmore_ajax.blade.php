  <?php $visibility_imp = explode(',',$postdata[0]['visibility']); ?>
   <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{ env('APP_URL') }}/images/ic_highlight_removegray.svg" alt=""></button>
            <h4 class="modal-title" id="myModalLabel">People</h4>
            <p>This post is shared with the following people</p>
         </div>

         <form method="post" class="visiblity_update_{{$postdata[0]['id'] }}">
                 <div class="modal-body">
                    <ul>           
                      <?php $array_count_usr = array(); ?>
                      <?php //echo '<pre>'; print_r($spacedata); die;?>
                      @foreach($spacedata as $key)  
                          @if($postdata[0]['user_id'] != $key['user']['id'])
                            <?php $array_count_usr[]=$key['user']['id']; ?>
                              <?php if( (in_array($key['user']['id'], $visibility_imp)) || (in_array('All', $visibility_imp)) ){?>
                               @if($key['user_status'] != 1 )
                              <li>
                                 <div class="member-wrap">
                                     @if($key['user']['profile_image_url'] !='')
                                       <span class="view_post_member_profile" style="background-image: url('{{$key['user']['profile_image_url']}}');"></span>
                                     @else
                                       <span class="view_post_member_profile" style="background-image: url('{{ url('/images/dummy-avatar-img.svg',[],env('HTTPS_ENABLE', true)) }}');"></span>
                                     @endif                        
                                      <div class="name-wrap">
                                        <a href="#!" class="title">{{ ucfirst($key['user']['first_name'])}} {{ ucfirst($key['user']['last_name'])}}</a>
                                       <span class="time">
                                  @if(isset($key['metadata']['user_profile']))
                                       {{ $key['metadata']['user_profile']['job_title']??'' }}
                                   @endif    
                                       </span>
                                       </div>
                                </div> 
                              </li>
                              @endif
                             <?php } ?> 
                          @endif
                        
                      @endforeach                     
                    </ul>
                 </div>
                 
                 <div class="modal-footer">
                   <!--  <button type="button" class="btn btn-default cancel_visibility" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary save_edit_visiblity" ediitvisible="{{$postdata[0]['id'] }}" allvisibleuser="{{$postdata[0]['visibility']}}" logedin-and-postuser="{{Auth::user()->id }},{{$postdata[0]['user_id'] }}"  data-dismiss="modal">Save</button>
                    <input type="hidden" class="hidden_count_visibility_{{$postdata[0]['id']}}" value="{{sizeOfCustom($array_count_usr)}}"> -->
                    <button type="button" class="btn btn-primary visibility_setting" ediitvisible="{{$postdata[0]['id'] }}" allvisibleuser="{{$postdata[0]['visibility']}}" logedin-and-postuser="{{Auth::user()->id }},{{$postdata[0]['user_id'] }}"   setting-id="{{$postdata[0]['id']}}" space-id="{{$spaceid}}">
                    
                        <span data-toggle="" data-target="#visiblepopup{{$postdata[0]['id']}}" >
                           Update visibility
                            </span>
                    </button>
                    
                 </div>
         </form>     
      </div>
   </div>
