@extends(session()->get('layout'))
@section('content')
@if(Session::has('message')) 
<div class="alert alert-info text-center"> {{Session::get('message')}} </div>
@endif
<?php 
   $ssl = false;
   if(env('APP_ENV')!='local')
   $ssl = true;
   ?>
<section class="main-content">
   <div class="container-fluid feed-content">
      <div class="col-lg-10 col-md-12 col-md-12 col-md-12 mid-content settings_page_content">
         <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 settings_tabs_wrap">
            <div class="box">
               <ul class="nav nav-tabs" role="tablist">
                  <li role="presentation" class="active"><a href="#Invitation-tab" aria-controls="profile" role="tab" data-toggle="tab">Invitation Log</a></li>
                  <li role="presentation"><a href="#post-tab" aria-controls="messages" role="tab" data-toggle="tab">Post Log</a></li>
                  <li role="presentation"><a href="#attachments-tab" aria-controls="notifications" role="tab" data-toggle="tab">Attachment Log</a></li>
                  <li role="presentation"><a href="#Comments-tab" aria-controls="notifications" role="tab" data-toggle="tab">Comments Log</a></li>
                  <li role="presentation"><a href="#Endorse-tab" aria-controls="notifications" role="tab" data-toggle="tab">Endorse Log</a></li>
                  <li role="presentation"><a href="#Usermgm-tab" aria-controls="notifications" role="tab" data-toggle="tab">User-mgm</a></li>
                  <li role="presentation"><a href="#new_user-tab" aria-controls="notifications" role="tab" data-toggle="tab">New User(s)</a></li>
                  
               </ul>
            </div>
         </div>
         <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12 settings_content_wrap">
            <div class="box">
               <div class="tab-content">
                  <div role="tabpanel" class="tab-pane" id="domain-management-tab">
                     <div class="heading_wrap">
                        <h4 class="title">Log</h4>
                     </div>
                     <div class="tab-inner-content">
                        <table class="table">
                           <thead>
                              <tr>
                                 <th>Firstname</th>
                                 <th>Lastname</th>
                                 <th>Email</th>
                              </tr>
                           </thead>
                           <tbody>
                              <tr>
                                 <td>John</td>
                                 <td>Doe</td>
                                 <td>john@example.com</td>
                              </tr>
                              <tr>
                                 <td>Mary</td>
                                 <td>Moe</td>
                                 <td>mary@example.com</td>
                              </tr>
                              <tr>
                                 <td>July</td>
                                 <td>Dooley</td>
                                 <td>july@example.com</td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
                  <div role="tabpanel" class="tab-pane active" id="Invitation-tab">
                     <div class="heading_wrap">
                        <h4 class="title">Invitation</h4>
                     </div>
                     <div class="tab-inner-content">
                        <div class="form_field_section">
                           <div class="tablerow tablehdrow">
                              <div class="col-lg-4 col-md-4 col-sm-5 col-xs-5 tablecell"><span class="approved-small-text">Invitation logs</span>
                              </div>
                           </div>
                           @foreach( $data['invitations'] as $invitation)
                           <div class="tablerow">
                              <div class="col-lg-5 col-md-5 col-sm-5 col-xs-4 tablecell">
                                <span>{{ ucFirst($invitation->invited_by).' '}} <small>{{ $invitation->action }}</small>{{' '.ucFirst($invitation->invited_to) }}</span>
                              </div>
                           </div>
                           @endforeach
                        </div>
                     </div>
                  </div>
                  <div role="tabpanel" class="tab-pane" id="post-tab">
                     <div class="heading_wrap">
                        <h4 class="title">Pending invites</h4>
                     </div>
                     <div class="tab-inner-content">
                        <div class="form_field_section">
                           <div class="tablerow tablehdrow">
                              <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 tablecell"><span class="approved-small-text">Post Log</span>
                              </div>
                           </div>
                           @foreach($data['posts'] as $post)
                            <div class="tablerow">
                              <div class="col-lg-5 col-md-5 col-sm-5 col-xs-4 tablecell">
                                <span>{{ ucFirst($post->user['first_name']).' '}} <small> created a new post with </small>{{ sizeOfCustom($post->postmedia)}}
                                    <small> attachment(s) {{ isset(json_decode($post->metadata,true)['get_url_data'])?' and with link':'' }}</small>{{ $post->created_at}}
                                </span>
                              </div>
                           </div>
                           @endforeach
                        </div>
                     </div>
                  </div>

                  <div role="tabpanel" class="tab-pane" id="new_user-tab">
                     <div class="heading_wrap">
                        <h4 class="title">New User </h4>
                     </div>
                     <div class="tab-inner-content">
                        <div class="form_field_section">
                           <div class="tablerow tablehdrow">
                              <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 tablecell"><span class="approved-small-text">New User</span>
                              </div>
                           </div>
                           @foreach($data['new_users'] as $user)
                            <div class="tablerow">
                              <div class="col-lg-5 col-md-5 col-sm-5 col-xs-4 tablecell">
                                <span>{{ ucFirst($user->invited_to).' '}} <small> join Client Share on {{ $user->updated_at }} and invited by  </small>{{ ucFirst($user->invited_by)}}
                                </span>
                              </div>
                           </div>
                           @endforeach
                        </div>
                     </div>
                  </div>
                  <div role="tabpanel" class="tab-pane" id="attachments-tab">
                     <div class="heading_wrap">
                        <h4 class="title">Attachments Logs</h4>
                     </div>
                     <div class="tab-inner-content">
                        <div class="form_field_section">
                           <div class="tablerow tablehdrow">
                              <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 tablecell"><span class="approved-small-text">Attachment Log</span>
                              </div>
                           </div>
                           @foreach($data['attachments'] as $attachment)
                            <div class="tablerow">
                              <div class="col-lg-5 col-md-5 col-sm-5 col-xs-4 tablecell">
                                <span>{{ ucFirst($attachment->first_name).' '}} 
                                    <small>
                                       @php
                                          if($attachment->action=='click link' || $attachment->action=='view embedded url')                                             
                                             echo $attachment->description;
                                          else 
                                             echo $attachment->action;
                                       @endphp
                                    </small> {{ ucFirst($attachment->file) }} 
                                    <small>#{{ $attachment->viewed}}</small>
                                </span>
                              </div>
                           </div>
                           @endforeach
                        </div>
                     </div>
                  </div>
                  <div role="tabpanel" class="tab-pane" id="Comments-tab">
                     <div class="heading_wrap">
                        <h4 class="title">Comments Logs</h4>
                     </div>
                     <div class="tab-inner-content">
                        <div class="form_field_section">
                           <div class="tablerow tablehdrow">
                              <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 tablecell"><span class="approved-small-text">Comment Log</span>
                              </div>
                           </div>
                           @foreach($data['comments'] as $comment)
                            <div class="tablerow">
                              <div class="col-lg-5 col-md-5 col-sm-5 col-xs-4 tablecell">
                                <span>{{ ucFirst($comment->first_name).' '}} <small> comment on {{ ucFirst($comment->post_subject)}} post {{ $comment->count }}</small>
                                </span>
                              </div>
                           </div>
                           @endforeach
                        </div>
                     </div>
                  </div>
                  <div role="tabpanel" class="tab-pane" id="Endorse-tab">
                     <div class="heading_wrap">
                        <h4 class="title">Like/Endorse Logs</h4>
                     </div>
                     <div class="tab-inner-content">
                        <div class="form_field_section">
                           <div class="tablerow tablehdrow">
                              <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 tablecell"><span class="approved-small-text">Like/Endorse Log</span>
                              </div>
                           </div>
                           @foreach($data['likes'] as $like)
                            <div class="tablerow">
                              <div class="col-lg-5 col-md-5 col-sm-5 col-xs-4 tablecell">
                                <span>{{ ucFirst($like->first_name).' '}} <small> like a post {{ucFirst($like->post_subject)}} </small>
                                </span>
                              </div>
                           </div>
                           @endforeach
                        </div>
                     </div>
                  </div>
                  <div role="tabpanel" class="tab-pane" id="Usermgm-tab">
                     <div class="heading_wrap">
                        <h4 class="title">User-mgm Logs</h4>
                     </div>
                     <div class="tab-inner-content">
                        <div class="form_field_section">
                           <div class="tablerow tablehdrow">
                              <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 tablecell"><span class="approved-small-text">User-mgm Log</span>
                              </div>
                           </div>
                           @foreach($data['user_mgms'] as $user)
                            <div class="tablerow">
                              <div class="col-lg-5 col-md-5 col-sm-5 col-xs-4 tablecell">
                                <span>{{ ucFirst($user->removed_by).' '}} <small> removed </small> {{ucFirst($user->removed_user)}} <small> from Client Share </small>{{ucFirst($user->created_at)}}
                                </span>
                              </div>
                           </div>
                           @endforeach
                        </div>
                     </div>
                  </div>
                  <div role="tabpanel" class="tab-pane" id="password-tab">
                     <div class="heading_wrap">
                        <h4 class="title">Password</h4>
                     </div>
                     <div class="tab-inner-content">
                        <p>Change your password here.</p>
                        <form class="change_password_form">
                           <div class="form_field_section">
                              <div class="col-lg-8 col-md-8 col-sm-10 col-xs-12 input-group">
                                 <label>Current password</label>
                                 <input type="text" class="form-control" placeholder="Type your password here" name="rule" value="">
                              </div>
                              <div class="col-lg-8 col-md-8 col-sm-10 col-xs-12 input-group">
                                 <label>New password</label>
                                 <input type="text" class="form-control" placeholder="Type your new password here" name="rule" value="">
                              </div>
                              <div class="col-lg-8 col-md-8 col-sm-10 col-xs-12 input-group">
                                 <label>Verify password</label>
                                 <input type="text" class="form-control no-margin" placeholder="Verify your password here" name="rule" value="">
                              </div>
                           </div>
                           <button class="btn btn-primary left disabled" href="">Save</button>
                        </form>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- col-md-8 -->
   </div>
   <!-- container -->
</section>
@endsection