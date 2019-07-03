<form class="edit_post_form" method="POST" enctype="multipart/form-data" action="/updatepost">
   <div class="shareupdate-wrap box">
      <div class="share-inner-wrap ">
         <div class="form-submit-loader" style="display:none">
            <span></span>
         </div>
         <h4 class="title">Share Content</h4>
         <input type="hidden" name="_token" class="_token" value="{{ csrf_token() }}" />
         <input type='hidden' name="space[id]" value="">
         <input type='hidden' name="post[id]" value="" class="editing_post_id">
         <div class="form-group dp-input">
            <label class="subject_text" style="">Subject</label>
            <textarea type="text" wrap="virtual" name="editpost[subject]" class="post-subject-textarea form-control t1-resize post_subject subject_validate remove_border" id="" data-validation-engine="validate[required]" placeholder="Add a subject" rows="1" data-min-rows="1"></textarea>
            <div class="" style="">
               <label class="dp-body">Body</label>
               <input name="thumbcheck" id="thumbcheck" value="0" type="hidden">
               <textarea class="post-description-textarea form-control t2-resize main_post_ta2 remove_border1" id="" name="editspace[post]" placeholder="Add your post (you can include URL & YouTube links.)" rows="1" data-min-rows="1" style=" padding-top: 7px;" data-validation-engine="validate[required]"></textarea>
               <div class="url_embed_div_edit">
               </div>
               <div class="attachment-header" style="margin-bottom: 7px;">
                  <label class="dp-body left">Attachments</label>
                  <label class="remove-all dp-body right remove_all_trigger" style="display:none; color: #0d47a1;cursor: pointer;float: right;min-width: auto;width: auto;">Delete all files</label>
               </div>
               <div class="upload-content post_categories_file_edt" style="display:none">
                  <span class="close">
                  <img src="/images/ic_deleteBlue.svg" id="" onclick="close_preview_edit(this)">
                  </span>
                  <div class="upload-text">
                     <h3>
                        <img src="/images/ic_IMAGE.svg" alt="">
                        <span class="upload_file_name_edtt">
                        </span>
                     </h3>
                     <p></p>
                  </div>
                  <img id="blah_edit" src="" alt="" class="img-responsive" style="display:none">
                  <video controls="" class="post_video_preview" style="display:none" width="400">
                     <source src="" type="video/mp4" class="post_video_preview_s"></source>
                     Your browser does not support HTML5 video.
                  </video>
               </div>
            </div>
            <div class="edit_media_div"></div>
            <div class="single-popup-upload-file">
               <div class="upload-preview-wrap">
                  
               </div>             
            </div>
            <div class="single-edit-attach">
               <a href="javascript:void(0)" id="edit_upload_link_attach" class="edit_upload_link_attach" class="title" onclick="return uploadEditPostFile()">Attach file(s)</a>
            </div>
         </div>
         <input type="hidden" class="edit_deleted_files"  name ="edit_deleted_files" value="">
         <input type="hidden" name ="uploaded_file_aws" value="" class="edit_post_aws_files_data">
         <input id="upload_edtt" class="post_file_edtt" name="file[]" style="display:none" type="file">
      </div>
      <div class="shareupdate-bottom" style="">
         <div class="content-privacy" style="">
            <div class="col-md-4">
               <h3>Category</h3>
               <p>Organise your content.</p>
               <select class="single-post-edit-category-select" name="editcategory">
                  <option  style="display: none"></option>
                  @foreach( $share_data['category_tags'] as $category_id => $category )
                     <option value="{{$category_id}}">{{$category}}</option>
                  @endforeach
               </select>
            </div>
            <div class="col-md-4 mid-border">
               <h3>Visibility</h3>
               <p>Control who sees your content.</p>
               <select class="single-post-edit-visiblity-select" name="editvisibility[]" multiple>
                  @foreach( $share_data['spaceUsers'] as $space_user )
                     <option value="{{$space_user['user']['id']}}">{{$space_user['user']['fullname']}}</option>
                  @endforeach
               </select>
            </div>
            <div class="col-md-4">
               <h3>Alerts</h3>
               <p>Control who receives an email alert.</p>
               <select class="single-post-edit-alert-select" name="editalert[]" multiple>
                  @foreach( $share_data['spaceUsers'] as $space_user )
                     <option value="{{$space_user['user']['id']}}">{{$space_user['user']['fullname']}}</option>
                  @endforeach
               </select>
            </div>
         </div>
         <!-- content-privacy -->

         <input id="single-post-repost" class="single-post-repost hidden" name="repost" value="" type="checkbox">
         <label for="single-post-repost" class="single-edit-post-checkbox"> Repost this to the top of Client Share feed </label>
      </div>
      <!-- share-inner-wrap -->
      <div class="shareupdate-bottom single-post-edit" id="post_button">
         <div class="shareupdate-bottom-buttons">
            <button type="button" href="" class="btn btn-primary right post_btn submit-edited-post">Save</button>
            <button type="button" href="" class="btn btn-default right post_btn" onclick="return resetEditedSinglePost();">Cancel</button>
         </div>
      </div>
      <input name="url_embed_toggle" value="0" type="hidden">
      <!-- shareupdate-bottom -->
   </div>
</form>