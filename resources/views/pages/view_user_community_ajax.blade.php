 

 <div class="modal-dialog" role="document">
                        <div class="modal-content">
                           <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{ url('/') }}/images/ic_highlight_removegray.svg" alt="" /></button>
                           </div>
                           <div class="modal-body">
                              <div class="modal_image_section">
                                 @if(!isset($user_profile['user']['profile_image_url']))
                                 <span style="background-image:url('{{ url('/') }}/images/default-user-image.png')">
                                 </span>
                                 @else
                                 <span style="background-image:url('{{$user_profile['user']['profile_image_url']}}')">
                                 </span>        
                                 @endif
                              </div>
                              <div class="modal_content_section community_member_info">
                                 <div class="member_info">
                                    <h4>{{ucfirst($user_profile['user']['first_name'])}}@if(isset($user_profile['user']['last_name'])) {{ucfirst($user_profile['user']['last_name'])}} @endif</h4>
<h5>@if(isset($user_profile['metadata']['user_profile']['job_title'])){{ucfirst($user_profile['metadata']['user_profile']['job_title'])}}@endif</h5>
                                    <p>{{$user_profile['metadata']['user_profile']['bio']}}</p>
                                    <div class="contact-info">
                                       <h6>Contact information</h6>
                                       @if(!empty($user_profile['metadata']['user_profile']['user']['contact']['linkedin_url']))
                                       <span class="linkedin-link"><a target="_blank" href="{{$user_profile['metadata']['user_profile']['user']['contact']['linkedin_url']}}">{{$user_profile['metadata']['user_profile']['user']['contact']['linkedin_url']}}</a></span>
                                       @endif

                                       <span class="email-link"><a href="mailto:{{$user_profile['user']['email']}}">{{$user_profile['user']['email']}}</a></span>

                                       @if(!empty($user_profile['metadata']['user_profile']['user']['contact']['contact_number']))
                                       <span class="call-link">{{$user_profile['metadata']['user_profile']['user']['contact']['contact_number']}}</span>
                                       @endif
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>