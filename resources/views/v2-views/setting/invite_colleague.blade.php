<!-- Invite popup start -->
<div class="modal fade invite-user-pop-up md-popup" id="myModalInvite" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
   <div class="modal-dialog modal-dialog-centered" role="document">
      
      <div class="modal-content white-popup">
         <div class="modal-header">
            <h5 class="modal-title hidden-mbl" id="myModalLabel">Invite Colleague</h5>
            <h5 class="modal-title hidden-desktop" id="myModalLabel">Add a new member</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true"><img src="{{asset('images/v2-images/close-icon.svg')}}" alt="close"></span>
            </button>
         </div>
         <div class="invite-colleague" id="invite_colleague">
           <div class="modal-body">
           <span class="success-msg white_box_info" style="display:none">Restricted email access to domains </span>
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
              <div class="invite-admin-content for-admin" style="display: none;">
                <p class="info-text"><img src="{{asset('images/v2-images/info-icon.svg')}}" alt="Email Invitation Information" /> Admins have additional permissions to manage the community and edit or delete share content.</p>
              </div>
              <input type="hidden" name="user_type" value="user">
              <div class="form-row">
                <div class="col-md-6 form-group">
                  <label>First name</label>
                  <input type="text" class="form-control " placeholder="Type your first name"  aria-describedby="basic-addon1" name="first_name" autocomplete="off">
                </div>
                <div class="col-md-6 form-group">
                  <label>Last name</label>
                  <input type="text" class="form-control " placeholder="Type your last name"  aria-describedby="basic-addon1" name="last_name" autocomplete="off">
                </div>
                <div class="col-md-12 form-group">
                  <label>Email</label>
                  <input type="text" class="form-control invite_email_inp" placeholder="Add your email address"  aria-describedby="basic-addon1" name="email" autocomplete="off">
                </div>
              </div>
              <div class="bulk-checkbox-wrap">
                 <div class="custom-radiobtn-group">
                    <label for="radio-invite-mail">Send email invite via Client Share                   
                    <input type="radio" name="optradio"  class="radio-invite" checked="checked" id="radio-invite-mail">
                    <span class="custom-radiobtn"></span>
                    </label>
                 </div>
                 <div class="custom-radiobtn-group">
                    <label for="radio-invite-url">Generate invite URL
                    <input type="radio" name="optradio" class="radio-invite" id="radio-invite-url">
                    <span class="custom-radiobtn"></span>
                    </label>
                 </div>
              </div>
              <div class="invite-email form-group">
                 <label>Subject of email</label>
                 <div contenteditable="false" class="form-control subjectbody" placeholder="subject" type="text" autofocus="" name="subject" spellcheck="false">
                    <span class="for-user">{{ $data->share_name }} Client Share</span>
                   
                    <span class="for-admin" style="display: none;">{{ $data->share_name }} Client Share</span>
                 </div>
              </div>

              <div class="invite-email form-group">
                 <label>Message</label>
                 <div contenteditable="true" class="form-control brfrfbgrgr mailbody" placeholder="Message" type="text" autofocus="" spellcheck="false">
                    <span style="display:none">Hello</span>
                    <span></span>

                    <textarea class="form-control mail_body for-user" type="text" autofocus="" spellcheck="false">Please join me on this Share – a unique platform that together will help us build a closer, more productive relationship. It’s powerful, simple to use and you can easily invite your colleagues to join too. It's a great way to ensure you have secure access to the latest information and contract insight in the best format, anytime, anywhere.</textarea>

                    <textarea class="form-control mail_body for-admin" type="text" autofocus="" style="display: none;" spellcheck="false">You have been invited to become an administrator of the {{ $data->share_name }} Client Share.</textarea>
   
                    <div>Thanks,</div>
                    <div>{{ ucfirst(Auth::user()->first_name) }} {{ ucfirst(Auth::user()->last_name) }}<br/>{{$data->SellerName->company_name}}</div>
                    <div></div>
                 </div>
              </div>
              <div class="short_url form-group" style="display:none;">
                 <label class="hidden-mbl">url</label>
                 <label class="hidden-desktop">Copy this URL to share:</label>
                 <input type="text" readonly class="form-control" id="invite_url" placeholder="Email"  aria-describedby="basic-addon1" autocomplete="off" value="">
                 <button class="invite-btn copy-link">Copy link</button>
              </div>
              <div class="btn-group">
           <button type="button" class="btn btn-secondary invite-cancel-btn" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary create_url" style="display:none;">Create URL</button>
            <button type="button" class="btn btn-primary btn-invite" onclick="send_mail(this)">Invite</button>
            <button type="button" class="btn btn-primary btn-done" style="display:none;" >Done</button>
           </div>
           </div>
           
         </div>
      </div>
         <!-- white popoup -->
   </div>
</div>
<!-- Invite popup end -->