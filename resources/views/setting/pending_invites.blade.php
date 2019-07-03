<div class="heading_wrap">
                     <h4 class="title">Pending invites</h4>
                  </div>
                  <div class="tab-inner-content">
                     <p>Cancelled invites can be sent again using the standard invite feature.</p>
                     <div class="alert alert-info text-center pending_noti_msg" style="display:none">Invitation Sent Successfully. </div>
                     <div class="form_field_section">
                        <div class="tablerow tablehdrow">
                           <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 tablecell"><span class="approved-small-text">Pending invitee</span></div>
                           <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 tablecell"><span class="approved-small-text">Email address</span></div>
                           <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 tablecell"><span class="approved-small-text">Date invited</span></div>
                           <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 tablecell"><span class="approved-small-text">Invited by</span></div>
                           <div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 tablecell"><span class="approved-small-text">Invite History</span></div>
                           <div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 tablecell"><span class="approved-small-text"></span></div>
                        </div>
                        @if(!empty($pending_invitations))
                        <?php $count =1; $pending_inv_count = sizeOfCustom($pending_invitations); ?>
                        @foreach( $pending_invitations as $pending_invitation )
                           @php
                              $pending_invitation['space_user']['user']['profile_image_url'] = filePathJsonToUrl($pending_invitation['space_user']['user']['profile_image']);
                           @endphp
                        <div class="tablerow tablerow-detail">
                           <input type="hidden" name="space_user_id" value="{{ $pending_invitation['space_user']['id'] }}">
                           <input type="hidden" name="email" value="{{ $pending_invitation['space_user']['user']['email'] }}">
                           <input type="hidden" name="first_name" value="{{ ucfirst($pending_invitation['space_user']['user']['first_name']) }}">
                           <input type="hidden" name="last_name" value="{{ ucfirst($pending_invitation['space_user']['user']['last_name']) }}">
                           <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 tablecell name_cell">
                              @if(!empty($pending_invitation['space_user']['user']['profile_image_url']))
                              <span style="background-image: url('{{ $pending_invitation['space_user']['user']['profile_image_url']}} ');" class="dp pro_pic_wrap"></span>
                              @endif
                              @if(empty($pending_invitation['space_user']['user']['profile_image_url']))
                              <span style="background-image: url('{{asset('/images/dummy-avatar-img.svg', true)}}');" class="dp pro_pic_wrap"></span>
                              @endif
                              <span class="mem_name">{{ucFirst($pending_invitation['space_user']['user']['first_name']).' '.ucFirst($pending_invitation['space_user']['user']['last_name']) }}</span>
                           </div>
                           <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 tablecell"><span class="approved-small-text">Email address</span><span class="userinvite-mail">{{ $pending_invitation['space_user']['user']['email'] }}</span></div>
                           <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 tablecell">
                              <span class="approved-small-text">Date invited</span>
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
                           <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 tablecell">
                              <span class="approved-small-text">Invited by</span>
                              <span>
                              @if(isset($date_invited_by->invited_by))
                              {{ ucFirst($date_invited_by->invited_by) }}
                              @else
                              {{ ucFirst($pending_invitation['space_user']['invitedBy']['first_name']).' '.ucFirst($pending_invitation['space_user']['invitedBy']['last_name']) }}
                              @endif
                              </span>
                           </div>
                           @php $invited_by_count = sizeOfCustom($pending_invitation['invited_by_list'])  @endphp
                           <div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 tablecell">
                              <span class="approved-small-text">Invite history</span>
                              <div class="pending-eye-wrap @if($invited_by_count <= 1) disabled @endif">
                                 @if($invited_by_count <= 1)
                                 <img class="pending-eye disabled" src="{{asset('/images/ic_visibility_off.svg', env('SECURE_COOKIES', true))}}" alt="">
                                 @else
                                 <img class="pending-eye pending-history-{{ $count }}" src="{{asset('/images/ic_visibility.svg', env('SECURE_COOKIES', true) )}}" alt="" data-id="{{ $count }}">
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
                           <div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 tablecell">
                              <div class="dropdown hover-dropdown check_hover_dropdown">
                                 <a href="#" class="dropdown-toggle  dots check_hover_dots" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                 <span></span>
                                 </a>
                                 <ul class="dropdown-menu">
                                    <li class="resend_trigger"><a href="#" data-toggle="modal" data-target="#resendinvites">Resend invitation </a></li>
                                    <li class="cancel_invi_trigger"><a href="#" class="delete-link" data-toggle="modal" data-target="#cancelinvitepopup">Cancel invite</a></li>
                                 </ul>
                              </div>
                           </div>
                        </div>
                        <?php ++$count;?>
                        @endforeach
                        @else
                        <p style="margin-left: 20px; margin-top: 10px;">No Pending Invitations</p>
                        @endif
                     </div>
                     <!-- Pending users pagination start  -->
                      <div class="pagination-wrap">
                        {{  $pending_invitations_pagination->links() }}
                      </div>
                     <!-- Pending users pagination end  -->
                  </div>