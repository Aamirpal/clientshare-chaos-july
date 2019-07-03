<div class="modal fade" id="delete_post_modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
	<div class="modal-dialog modal-sm" role="document">
		 <div class="modal-content">
		    <div class="modal-header">
		       <button type="button" class="close-inner" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		       <h4 class="modal-title" id="myModalLabel">Delete post</h4>
		    </div>
		    <div class="modal-body">
		       <p>Do you want to permanently delete this post?</p>
		    </div>
		    <div class="modal-footer">
		       <button type="button" class="btn btn-default left" data-dismiss="modal">CANCEL</button>
		       <a href="" id="delete" class="delete_posted btn btn-primary modal_initiate_btn" >DELETE POST</a>
		    </div>
		 </div>
	</div>
</div>

<?php
    $length = 0;
   if (!empty($data->executive_summary))
       $length = strlen($data->executive_summary);
  ?>

<div class="modal fade custom-tile-popup" id="executive_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
   <div class="modal-dialog" role="document">
      <div class="modal-content white-popup">
         <div class="modal-header">
            <h2 class="modal-title" id="myModalLabel">Executive Summary <a class="helpicon" data-toggle="popover" title="Client Share biography" data-trigger="hover" data-placement="right" data-content="You can use this to share key information about the purpose of this Client Share that will be visible to all members. Consider embedding a link or uploading a document to add further detail."><i class="fa fa-question-circle"></i></a></h2>
         </div>
   <form method="POST" enctype="multipart/form-data" action="../executive_summary_save" id="executive_summary_save" class="executive_summary_save">
      {{ csrf_field() }}
     <!--  <div class="add_info_popup" style="display:none" id="content"> -->
      <div>
         <div class="executive-textarea-col full-width">
            <textarea class="form-control summary_box"  maxlength="300"  placeholder="Start typing" name="space[executive_summary]" type="text" onkeyup="countCharExec(this)" autofocus>{{ $data->executive_summary }}</textarea>
            <span class="letter-count">
               <span class="character_number"  val="{{$length}}">{{$length}}</span>
               /300
            </span>
         </div>
         @if ($errors->has('errorexecutive'))
         <span class="error-msg text-left">
         {{ $errors->first('errorexecutive') }}
         </span>
         @endif
         <input type='hidden' name="user[id]" value="{{ Auth::user()->id }}">
         <input type='hidden' name="space[id]" value="{{ $data->id }}">
         <div id="upload_video_name" class="pdf_list_file"></div>
         <div id="upload_pdf_name" class="pdf_list_file"></div>
         <div class="fileupload fileupload-new full-width" data-provides="fileupload">
            <div class="upload-preview-wrap">
               <div class="selected_files">
                  <input type="hidden" value="" class="post-media-data">
                  <input type="hidden" class="already_uploaded_pdf_file" value="">
                  <div class="pdf_list_file remove_executive_file" style="display: none;">
                     <span class="link-input-icon">
                        <img src="{{ url('/',[],$ssl) }}/images/ic_link.svg">
                     </span>
                     <span></span>
                     <a href="#!">
                        <img src='{{ url('/',[],$ssl) }}/images/ic_highlight_remove.svg' alt='' id="" class="delete_summary_files">
                     </a>
                  </div>
                  <input type="hidden" class="saved_summary_pdf_del" name="saved_summary_pdf_del" value="">
                  <input type="hidden" class="already_uploaded_video_file" value="">
                  <div class="pdf_list_file remove_executive_file" style="display: none;">
                     <span class="link-input-icon">
                        <img src="{{ url('/',[],$ssl) }}/images/ic_link.svg">
                     </span>
                     <span></span>
                     <a href="#!">
                        <img src='{{ url('/',[],$ssl) }}/images/ic_highlight_remove.svg' alt='' id="" class="delete_summary_files">
                     </a>
                  </div>
                  <input type="hidden" class="saved_summary_video_del" name="saved_summary_video_del" value="">
               </div>
               <div id="upload_video_name"></div>
               <span class="fileupload-preview"></span>
               <a href="#" class="close fileupload-exists" data-dismiss="fileupload" style="float: none; margin-left: 10px; opacity: 1; color: #0d47a1;">x</a>
            </div>
            <span class="invite-btn btn-file" >
               <div class="upload_doc_col" >
                  <span>
                     <img src="{{ url('/',[],$ssl) }}/images/ic_file_upload_icon.svg">
                  </span>
                  <span class="fileupload-new" onclick="upload_executive_file();">Upload key document or video</span>
               </div>
               <span class="fileupload-exists">Upload key document or video</span>
               <span class="file_upload_error"></span>
               <input type="hidden" class="upload_video_hidden" value="">
               <input type="hidden" class="already_uploaded_file" id="already_uploaded_file" value="">
               <input type="hidden" class="upload_pdf_hidden" value="">
            </span>
         </div>
         <div class="btn-section text-right full-width">
            <button class="btn-quick-links btn btn-primary save_executive_btn pull-right" id="save_executive_btn" type="button">Save</button>
            <button class="close btn btn-default summary_cancel btn-quick-links" id="cancel_btn" data-dismiss="modal" type="button" onclick="reset($('#upload_pdf_file'));">Cancel</button>
         </div>
      </div>
      <div style="display: none;">        
         <input type="text" class="executive_aws_files_data" name="aws_files_data">
         <input type="text" class="delete_summary_files_inp" name="delete_summary_files_inp">         
      </div>
      <!-- add info popup -->
   </form>
   <!-- update executive to S3 -->
   <form id="s3_form_details" action=""
      method="POST"
      enctype="multipart/form-data"
      class="direct_upload_s3">

      <!-- Key is the file's name on S3 and will be filled in with JS -->
      <input type="hidden" name="key" value="">
      <input id="upload_s3_file" class="" type="file" name="file" style="display:none">
      <!-- Progress Bars to show upload completion percentage -->
      <div class="progress-bar-area"></div>
   </form>
   <!--  update executive to S3 -->
   </div>
 </div>
</div>

<!-- category delete confirmation modal -->
<div class="modal fade custom-tile-popup twitter-popup category-delete-popup" id="category_delete_popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content white-popup">
            <div class="modal-header">
                <h2 class="modal-title" id="manage_twitter_feed_modal_label">Category</h2>
                <p class="confirm-text">Are you sure you want to delete <span>Account Management</span>? All posts within this category will be reassigned to <span>General</span>.</p>
            </div>
            <div class="text-right">
               <button type="submit" class="btn btn-primary pull-right btn-quick-links delete">Delete</button>
               <button type="button" class="btn btn-default btn-quick-links cancel" data-dismiss="modal">Cancel</button>
            </div>            
        </div>
   </div>
</div>