   <div class="modal fade" id="resendinvites" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
      <div class="modal-dialog " role="document">
         <div class="modal-content white-popup ">
            <div class="modal-header">
               <span class="success-msg white_box_info" style="display:none"></span>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
               <h2 class="modal-title" id="myModalLabel">Resend Invitation</h2>
            </div>
            <div class="modal-body">
               <div class="col-md-6"><label>First name</label><input class="form-control " placeholder="First name" aria-describedby="basic-addon1" name="first_name" type="text"></div>
               <div class="col-md-6"><label>Last name</label><input class="form-control " placeholder="Last name" aria-describedby="basic-addon1" name="last_name" type="text"></div>
               <div class="col-md-12"><label>Email</label><input disabled class="form-control " placeholder="Email" aria-describedby="basic-addon1" name="email" type="text"></div>
               <div class="col-md-12">
                  <label>Subject of email</label>
                  <div contenteditable="false" class="form-control subjectbody" placeholder="subject" type="text" autofocus="" name="subject">
                     <span>{{ ucfirst(Auth::user()->first_name) }} {{ ucfirst(Auth::user()->last_name) }} is inviting you to the {{ Session::get('space_info')['share_name']}} Client Share</span>
                  </div>
               </div>
               <div class="col-md-12">
                  <label>Message</label>
                  <div class="form-control mailbody" placeholder="Message" type="text" autofocus="" contenteditable="true">
                     <span>Hello <span></span></span>
                     <textarea class="form-control mail_body no-border comment-area" type="text" style="overflow: hidden; overflow-wrap: break-word; height: 140px;" autofocus="">I am inviting you to join me on this Client Share which has been set-up to share key information with you. The site is personalised, mobile, easy to share with colleagues and simple to use. It's a great way to ensure you have secure access to the latest updates and content at anytime, anywhere. Feel free to invite colleagues to join via the Client Share community.
       </textarea>
                     <div>Thanks &amp; Regards,</div>
                     <div>The {{  Session::get('space_info')['share_name']  }} Client Share<br/>On behalf of {{ ucfirst(Auth::user()->first_name) }} {{ ucfirst(Auth::user()->last_name) }} </div>
                     <div></div>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <input type="hidden" class="spaceid" value="{{Session::get('space_info')['id']}}" userid="{{Auth::user()->id}}">
               <button type="button" class="btn btn-primary resend_invite_btn" onclick="send_mail_setting(this)">Invite</button>
            </div>
         </div>
      </div>
      <!-- white popoup -->
   </div>
   <!-- Disable feedback popup  -->
   <div class="modal fade" id="disable_feedback" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
      <div class="modal-dialog modal-sm" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h4 class="modal-title" id="myModalLabel">Disable feedback</h4>
            </div>
            <div class="modal-body">
               <p>Are you sure you want to disable feedback?</p>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-default" onclick="$('#feedback_on_off').prop('checked', !$('#feedback_on_off').prop('checked')); $('.thankyou-feedback-button').prop('disabled', !$('.thankyou-feedback-button').prop('disabled'));" data-dismiss="modal">CANCEL</button>
               <button type="button" class="btn btn-primary" onclick="toggleFeedbackStatus('false');">Submit</button>
            </div>
         </div>
      </div>
   </div>
   <!-- Disable feedback popup end-->
   <div class="modal fade" id="cancelinvitepopup" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
      <div class="modal-dialog modal-sm" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h4 class="modal-title" id="myModalLabel">Cancel invite</h4>
            </div>
            <div class="modal-body">
               <p>Are you sure you want to cancel this pending invitation?</p>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-default" data-dismiss="modal">CANCEL</button>
               <button type="button" class="cancel_invitation_trigger btn btn-primary">CANCEL INVITE</button>
            </div>
         </div>
      </div>
   </div>
</div>
<!-- container -->
<!-- Skull of domin row in hidden start -->
<div class="input-group domain-input-grp add_domain_skull" style="display:none">
   <span class="input-group-addon" id="basic-addon1">@</span>
   <input type="text" class="form-control " placeholder="IBM.com" name="rule" value="" autocomplete="off">
   <div class="dropdown hover-dropdown">
      <a href="#" class="dropdown-toggle  dots" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
      <span></span>
      </a>
      <ul class="dropdown-menu">
         <li class="domain_inp_edit"><a href="#!">Edit domain</a></li>
         <li class="domain_inp_delete"><a href="#!" class="delete-link">Delete domain</a></li>
      </ul>
   </div>
</div>
<!-- Skull of domin row in hidden end -->
<!-- Feedback Setting popup start-->
<div class="modal fade in" id="thanku-feedback" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" >
   <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content save-popup">
         <h3>Confirm change to Feedback</h3>
         <p>Feedback is collected Quarterly from the Buyer community. When would you like the first window to open?</p>
         <div class="feedback-save-select">
            <select id="selectFeedbackType" class="selectpicker company_admin form-control">
               <option selected style="display:none;" value=0>Please Select</option>
               @for($i = 1; $i <= 12; $i++)
               <option value="{{Carbon\Carbon::parse(date('Y-m-01'))->addMonth($i)->toDateTimeString()}}" >{{Carbon\Carbon::parse(date("Y-m-01"))->addMonth($i)->format('M Y')}}</option>
               @endfor
            </select>
         </div>
         <br/>
         <div class="modal-footer cancel-btn">
            <button id="cancelReload" type="button" class="btn btn-primary left" data-dismiss="modal">Cancel</button>
         </div>
         <div class="modal-footer immediate">
            <button type="button" disabled class="btn btn-primary left save_feedback_on_off" data-dismiss="modal" >Confirm</button>
         </div>
      </div>
   </div>
</div>
<!-- Feedback Setting popup end-->
<!-- Feedback reminder popup -->
<div class="modal fade in" id="feedback-reminder" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" style=" padding-right: 15px;">
   <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel">Send a reminder</h4>
         </div>
         <div class="modal-body">
            <p>An email reminder and notification will be sent to all Buyers who have not yet given feedback during this window</p>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default left" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary modal_initiate_btn feedback_reminder" data-dismiss="modal">Confirm</button>
         </div>
      </div>
   </div>
</div>
<!-- Feedback reminder popup end -->
<div class="modal fade in" id="Test-dateChange" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" >
   <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content save-popup">
         <div class="feedback-save-select">
            @php if(!empty($space_data)){
            $year = date('Y',strtotime($space_data->feedback_status_to_date));
            $month = date('m',strtotime($space_data->feedback_status_to_date));
            $date = date('d',strtotime($space_data->feedback_status_to_date));
            }else{
            $year = $month = $date = '';
            } @endphp
            <select class="form-control" style="width: 80px; float: left; margin-right: 7px; margin-bottom: 10px;" name="tempyear" id="tempyear">
               <option value="">Year</option>
               @php for($year_start=2016;$year_start<=2020;$year_start++){ @endphp
               <option value="{{$year_start}}" @if($year == $year_start) selected @endif> {{$year_start}}</option>
               @php } @endphp
            </select>
            <select class="form-control" style="width: 80px; float: left; margin-right: 7px; margin-bottom: 10px;" name="tempmonth" id="tempmonth">
               <option value="">Month</option>
               @php for($month_start=1;$month_start<=12;$month_start++){ @endphp
               <option value="{{$month_start}}" @if($month == $month_start) selected @endif> {{$month_start}}</option>
               @php } @endphp
            </select>
            <select class="form-control" style="width: 80px; float: left; margin-bottom: 10px;" name="tempday" id="tempday">
               <option value="">Day</option>
               @php for($day_start=1;$day_start<=31;$day_start++){ @endphp
               <option value="{{$day_start}}" @if($date == $day_start) selected @endif> {{$day_start}}</option>
               @php } @endphp
            </select>
         </div>
         <br/>
         <div class="modal-footer immediate">
            <button type="button" class="btn btn-primary left tempDateUpdate">update</button>
         </div>
      </div>
   </div>
</div>
<!-- update executive to S3 -->
<form action="{{$s3_form_details['url']}}"
   method="POST"
   enctype="multipart/form-data"
   class="direct_upload_s3">
   <?php foreach ($s3_form_details['inputs'] as $name => $value) { ?>
   <input type="hidden" name="<?php echo $name; ?>" value="<?php echo $value; ?>">
   <?php } ?>
   <!-- Key is the file's name on S3 and will be filled in with JS -->
   <input type="hidden" name="key" value="">
   <input id="upload_s3_file" class="" type="file" name="file" style="display:none">
   <!-- Progress Bars to show upload completion percentage -->
   <div class="progress-bar-area"></div>
</form>
<!--  update executive to S3 -->

<!-- bulk-invitation file upload status modal -->
<div class="modal fade" id="bulk-upload-status" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
   <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="modal-title modal_title" id="myModalLabel">Verify data</h4>
         </div>
         <div class="modal-body">
            <div class="data-wrap heading">
              <div class="col-md-4">First name</div>
              <div class="col-md-4">Last name</div>
              <div class="col-md-4">Email</div>
            </div><!-- .data-wrap .heading -->
            <div class="file-preview">
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default left" onclick="initiate_bulk_invitation()" data-dismiss="modal">OK</button>
            <button type="button" class="btn btn-default left" onclick="reset_bulk_invitation()" data-dismiss="modal">Cancel</button>
         </div>
      </div>
   </div>
</div>
<!-- bulk-invitation file upload status modal end -->

<!-- Power BI modals start -->
<div class="modal fade add-report-modal" id="power-bi-modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
   <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="modal-title modal_title h1" id="myModalLabel">Add report</h4>
         </div>
         <div class="modal-body">
            <div class="data-wrap heading">
               <form class="power-bi-reports-form">
                    <div class="form-group">
                     <label for="report_type">Report type</label>
                     <select id="report_type" name="report_type" class="power-bi-reports-type form-control">
                        <option value="">--Select type--</option>
                        <option value="report">Report</option>
                        <option value="dashboard">Dashboard</option>
                     </select>
                  </div>
                  <div class="common-report-columns hidden form-group">
                     <label for="report_name">Report name: </label>
                     <input id="report_name" class="form-control" type="" name="report_name">
                  </div>
                  <div class="bi-credentials">
                  </div>
               </form>
            </div>
         </div>
         <div class="modal-footer hidden">
            <button type="button" class="btn btn-default left" onclick="createReport()">OK</button>
            <button type="button" class="btn btn-default left" onclick="resetReportModal()" data-dismiss="modal">Cancel</button>
         </div>
      </div>
   </div>
</div>
<!-- Power BI modals end -->


<!-- Remove report popup  -->
<div class="modal fade" id="removeReport" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
   <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel">Remove report</h4>
         </div>
         <div class="modal-body">
            <p>Are you sure you want to remove this report?</p>
            <input type="hidden" class="report_id_input">
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" onclick="" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="removeReportTrigger(this);">Remove</button>
         </div>
      </div>
   </div>
</div>
<!-- Remove report popup end-->