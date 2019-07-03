<!-- Invite popup start -->
<div class="modal fade invite-user-pop-up" id="myModalInvite" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
   <div class="modal-dialog " role="document">
      
      <div class="modal-content white-popup">
         <div class="modal-header">
            <span class="success-msg white_box_info" style="display:none">Restricted email access to domains </span>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">
                <img src="{{ url('/images/ic_delete_round.svg',[],env('HTTPS_ENABLE', true)) }}" alt="Step 1" />
              </span>
            </button>
            @if($space_user[0]['user_role']['user_type_name'] == 'admin')
            <div class="invite_tab_head_col text-center">
              <ul class="nav nav-tabs">
                <li class="active user-invite-type for-user-invite">
                  <a href="#">Invite Colleague</a>
                </li>
                <li class="user-invite-type for-admin-invite">
                  <a href="#">Invite Admin</a>
                </li>
              </ul>
            </div>
            @endif
            <div class="invite-admin-content for-admin" style="display: none;">
              <p>Admins have additional permissions to manage the community and edit or delete share content.</p>
            </div>
         </div>
         <div class="invite-colleague" id="invite_colleague">
           <div class="modal-body">
              <input type="hidden" name="user_type" value="user">
              <div class="col-md-6">
                 <label>First name</label>
                 <input type="text" class="form-control " placeholder="First name"  aria-describedby="basic-addon1" name="first_name" autocomplete="off">
              </div>
              <div class="col-md-6">
                 <label>Last name</label>
                 <input type="text" class="form-control " placeholder="Last name"  aria-describedby="basic-addon1" name="last_name" autocomplete="off">
              </div>
              <div class="col-md-12">
                 <label>Email</label>
                 <input type="text" class="form-control invite_email_inp" placeholder="Email"  aria-describedby="basic-addon1" name="email" autocomplete="off">
              </div>
              <div class="bulk-checkbox-wrap col-md-12">
                 <div>
                    <input type="radio" name="optradio"  class="radio-invite" checked="checked" id="radio-invite-mail">
                    <label for="radio-invite-mail">
                       <span></span>
                    Send email invite via Client Share</label>
                 </div>
                 <div>
                    <input type="radio" name="optradio" class="radio-invite" id="radio-invite-url">
                    <label for="radio-invite-url">
                       <span></span>
                    Generate invite URL</label>
                 </div>
              </div>
              <div class="col-md-12 invite-email">
                 <label>Subject of email</label>
                 <div contenteditable="false" class="form-control subjectbody" placeholder="subject" type="text" autofocus="" name="subject">
                    <span class="for-user">{{ $data->share_name }} Client Share</span>
                   
                    <span class="for-admin" style="display: none;">{{ $data->share_name }} Client Share</span>
                 </div>
              </div>

              <div class="col-md-12 invite-email">
                 <label>Message</label>
                 <div contenteditable="true" class="form-control brfrfbgrgr mailbody" placeholder="Message" type="text" autofocus="">
                    <span style="display:none">Hello</span>
                    <span></span>

                    <textarea class="form-control mail_body for-user" type="text" autofocus="">Please join me on this Share – a unique platform that together will help us build a closer, more productive relationship. It’s powerful, simple to use and you can easily invite your colleagues to join too. It's a great way to ensure you have secure access to the latest information and contract insight in the best format, anytime, anywhere.</textarea>

                    <textarea class="form-control mail_body for-admin" type="text" autofocus="" style="display: none;">You have been invited to become an administrator of the {{ $data->share_name }} Client Share.</textarea>
   
                    <div>Thanks,</div>
                    <div>{{ ucfirst(Auth::user()->first_name) }} {{ ucfirst(Auth::user()->last_name) }}<br/>{{$data->SellerName->company_name}}</div>
                    <div></div>
                 </div>
              </div>
              <div class="col-md-12 short_url" style="display:none;">
                 <label>url</label>
                 <input type="text" readonly class="form-control" id="invite_url" placeholder="Email"  aria-describedby="basic-addon1" autocomplete="off" value="">
                 <button class="invite-btn copy-link">Copy link</button>
              </div>
           </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-primary create_url" style="display:none;">CREATE URL</button>
            <button type="button" class="btn btn-primary btn-invite" onclick="send_mail(this)">Invite</button>
            <button type="button" class="btn btn-primary btn-done" style="display:none;" >DONE</button>
         </div>
      </div>
         <!-- white popoup -->
   </div>
</div>
<!-- Invite popup end -->