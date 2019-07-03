<div class="heading-wrap">
                     <h2 class="title">Pending invites</h2>
                  </div>
                  <div class="heading-wrap-mobile">
    <h2 class="title">Pending invites</h2>
</div>
                  <div class="tab-inner-content user-management-inner-content pending-invites-content">
                     <p class="info-text"><img src="{{asset('images/v2-images/info-icon.svg')}}" alt="Email Invitation Information" /> Cancelled invites can be sent again using the standard invite feature.</p>
                     <div class="alert alert-info text-center pending_noti_msg" style="display:none">Invitation Sent Successfully. </div>
                     <div class="form_field_section">
                        <div class="tablerow tablehdrow">
                           <div class="tablecell member-wrap"><span class="approved-small-text">Pending invitee</span></div>
                           <div class="tablecell email-wrap"><span class="approved-small-text">Email address</span></div>
                           <div class="tablecell date-wrap"><span class="approved-small-text">Date invited</span></div>
                           <div class="tablecell invitedby-wrap"><span class="approved-small-text">Invited by</span></div>
                           <div class="tablecell history-wrap"><span class="approved-small-text">History</span></div>
                           <div class="tablecell more-options-wrap"><span class="approved-small-text"></span></div>
                        </div>
                        @if(!empty($pending_invitations))
                        <?php $count =1; $pending_inv_count = sizeOfCustom($pending_invitations); ?>
                        @foreach( $pending_invitations as $pending_invitation )
                           @php
                              $pending_invitation['space_user']['user']['profile_image_url'] = filePathJsonToUrl($pending_invitation['space_user']['user']['profile_image']);
                           @endphp
                        <div class="tablerow tablerow-detail">
                        <div class="table-inner-row">
                        <div class="member-pic-mobile">
                          @if(!empty($pending_invitation['space_user']['user']['profile_image_url']))
                          <span style="background-image: url('{{ $pending_invitation['space_user']['user']['profile_image_url']}} ');" class="dp pro_pic_wrap"></span>
                          @endif
                          @if(empty($pending_invitation['space_user']['user']['profile_image_url']))
                          <span style="background-image: url(' {{ url('/images/v2-images/user-placeholder.svg',[],env('HTTPS_ENABLE', true)) }}');" class="dp pro_pic_wrap"></span>
                          @endif
                        </div>
                        <div class="member-info-mobile">
                              <input type="hidden" name="space_user_id" value="{{ $pending_invitation['space_user']['id'] }}">
                              <input type="hidden" name="email" value="{{ $pending_invitation['space_user']['user']['email'] }}">
                              <input type="hidden" name="first_name" value="{{ ucfirst($pending_invitation['space_user']['user']['first_name']) }}">
                              <input type="hidden" name="last_name" value="{{ ucfirst($pending_invitation['space_user']['user']['last_name']) }}">
                           <div class="tablecell name_cell member-wrap">
                           <div class="member-info">
                              @if(!empty($pending_invitation['space_user']['user']['profile_image_url']))
                              <span style="background-image: url('{{ $pending_invitation['space_user']['user']['profile_image_url']}} ');" class="dp pro_pic_wrap"></span>
                              @endif
                              @if(empty($pending_invitation['space_user']['user']['profile_image_url']))
                              <span style="background-image: url(' {{ url('/images/v2-images/user-placeholder.svg',[],env('HTTPS_ENABLE', true)) }}');" class="dp pro_pic_wrap"></span>
                              @endif
                              <span class="mem_name">{{ucFirst($pending_invitation['space_user']['user']['first_name']).' '.ucFirst($pending_invitation['space_user']['user']['last_name']) }}</span>
                           </div>
                           </div>
                           <div class="tablecell email-wrap"><span class="userinvite-mail">{{ $pending_invitation['space_user']['user']['email'] }}</span></div>
                           <div class="tablecell date-wrap">
                             <span class="date-invited-mobile">Date invited: </span>
                              <span>
                              @php
                              $date_invited_by = reset($pending_invitation['invited_by_list']);
                              if(isset($date_invited_by->created_at)){
                              echo date("d/m/Y", strtotime($date_invited_by->created_at));
                              }else{
                              $date_created_by = (array)$pending_invitation['space_user']['created_at'];
                              echo date("d/m/Y", strtotime($date_created_by['date']));
                              }
                              @endphp
                              </span>
                           </div>
                           <div class="tablecell invitedby-wrap">
                              <span>
                              @if(isset($date_invited_by->invited_by))
                              {{ ucFirst($date_invited_by->invited_by) }}
                              @else
                              {{ ucFirst($pending_invitation['space_user']['invitedBy']['first_name']).' '.ucFirst($pending_invitation['space_user']['invitedBy']['last_name']) }}
                              @endif
                              </span>
                           </div>
                           @php $invited_by_count = sizeOfCustom($pending_invitation['invited_by_list'])  @endphp
                           <div class="tablecell history-wrap">
                              <div class="pending-eye-wrap @if($invited_by_count <= 1) disabled @endif">
                                 @if($invited_by_count <= 1)
                                 <img class="pending-eye disabled" src="{{asset('/images/v2-images/eye-hide-icon.svg', env('SECURE_COOKIES', true))}}" alt="">
                                 @else
                                 <img class="pending-eye pending-history-{{ $count }}" src="{{asset('/images/v2-images/eye-see-icon.svg', env('SECURE_COOKIES', true) )}}" alt="" data-id="{{ $count }}">
                                 @endif  
                                 <div class="invite-history @if($pending_inv_count > 8 ) @if(($pending_inv_count-5) < $count) newclass @endif @endif" id="invite-history-{{ $count }}">
                                    <ul class="name-of-invitor left clickopen">
                                       <li class="list-title">Invited by<span>Date invited</span></li>
                                       @foreach( $pending_invitation['invited_by_list'] as $invite_b )
                                       <li>{{$invite_b->invited_by}}<span><?php echo date('d-m-y',strtotime($invite_b->created_at)); ?></span></li>
                                       @endforeach
                                    </ul>
                                 </div>
                              </div>
                              <!-- .pending-eye -->
                           </div>
                           <div class="tablecell more-options-cell">
                           <div class="more-options-wrap">
                              <div class="dropdown show hover-dropdown check_hover_dropdown more-options-dropdown">
                                 <a href="#" class="dropdown-toggle  dots check_hover_dots" data-toggle="dropdown" role="button" id="dropdownMenuLink" aria-haspopup="true" aria-expanded="true">
                                 <span class="more-options-text">more options</span>
                                </a>
                                 <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                    <li class="resend_trigger"><a href="#" data-toggle="modal" data-target="#resendinvites"><img src="{{asset('images/v2-images/resend-icon.svg')}}" alt="Resend invitation" />Resend invitation </a></li>
                                    <li class="cancel_invi_trigger cancel-invite"><a href="#" data-toggle="modal" data-target="#cancelinvitepopup"><img src="{{asset('images/v2-images/delete-icon-red.svg')}}" alt="Cancel invite" />Cancel invite</a></li>
                                 </ul>
                              </div>
                           </div>
                            </div>
                            </div>

                            <div class="more-options-mobile ">
                            <div class="more-options-wrap">
                                  <div class="dropdown show hover-dropdown check_hover_dropdown more-options-dropdown">
                                    <a href="#" class="dropdown-toggle  dots check_hover_dots" data-toggle="dropdown" role="button" id="dropdownMenuLink" aria-haspopup="true" aria-expanded="true">
                                    <span class="more-options-text">more options</span>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                        <li class="resend_trigger"><a href="#" data-toggle="modal" data-target="#resendinvites"><img src="{{asset('images/v2-images/resend-icon.svg')}}" alt="Resend invitation" />Resend invitation </a></li>
                                        <li class="cancel_invi_trigger cancel-invite"><a href="#" data-toggle="modal" data-target="#cancelinvitepopup"><img src="{{asset('images/v2-images/delete-icon-red.svg')}}" alt="Cancel invite" />Cancel invite</a></li>
                                    </ul>
                                  </div>
                              </div>
                            </div>

                        </div>

                        

                        </div>
                        <?php ++$count;?>
                        @endforeach
                        @else
                        <p class="no-data-wrap">No Pending Invitations</p>
                        @endif
                     </div>
                     <!-- Pending users pagination start  -->
                      <div class="pagination-wrap">
                        {{  $pending_invitations_pagination->links() }}
                      </div>
                     <!-- Pending users pagination end  -->
                  </div>