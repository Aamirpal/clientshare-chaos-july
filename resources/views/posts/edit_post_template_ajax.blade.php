<!-- edit_popup_skull start -->

  <form class="edit_post_form" method="POST" enctype="multipart/form-data" action="{{ url('/updatepost',[],env('SSL', true)) }} ">
   {{ csrf_field() }}
   <div class="shareupdate-wrap box highlight" id="tour2">
    <div class="share-inner-wrap ">
      <div class="form-submit-loader" style="display:none">
        <span></span>
      </div>
      <h4 class="title">Share Content</h4>
      <input type='hidden' name="user[id]" value="{{ Auth::user()->id }}">
      <input type='hidden' name="space[id]" value="{{ $data->id }}">
      <input type='hidden' name="post[id]" value="" class="editing_post_id">
      <div class="form-group dp-input">
        <label class="subject_text" style="">Subject</label>
        <textarea type="text" wrap="virtual" name="editpost[subject]" class="post-subject-textarea form-control t1-resize post_subject subject_validate remove_border" id="comment_input_area" data-validation-engine="validate[required]" placeholder="Add a subject" rows="1" data-min-rows="1" style="padding-top: 5px; display: block; box-sizing: padding-box; overflow: auto;"></textarea>
        <div class="subject_class" style="">
         <label class="dp-body">Body</label>
         <input name="thumbcheck" id="thumbcheck" value="0" type="hidden">
         <textarea class="post-description-textarea form-control t2-resize main_post_ta2 remove_border1" id="comment_input_area" name="editspace[post]" placeholder="Add your post (you can include URL & YouTube links.)" rows="1" data-min-rows="1" style=" padding-top: 7px;" data-validation-engine="validate[required]"></textarea>
         <div class="url_embed_div_edit">
         </div>
         <div class="attachment-header" style="margin-bottom: 7px;">
          <label class="dp-body left">Attachments</label>
          <label class="remove-all dp-body right remove_all_trigger" style="display:none; color: #0d47a1;cursor: pointer;float: right;min-width: auto;width: auto;">Delete all files</label>
        </div>
        <div class="upload-content post_categories_file_edt" style="display:none">
          <span class="close">
            <img src="{{url('/',[],env('SSL', true))}}/images/ic_deleteBlue.svg" id="" onclick="close_preview_edit(this)">
          </span>
          <!-- <img src="images/body-img.jpg" alt="" class="img-responsive"> -->
          <div class="upload-text">
           <h3>
            <img src="{{url('/',[],env('SSL', true))}}/images/ic_IMAGE.svg" alt="">
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
   <a href="javascript:void(0)" id="edit_upload_link_attach" class="edit_upload_link_attach" class="title" onclick="return d_edig()">Attach file(s)</a>
 </div>
 <input type="hidden" class="edit_deleted_files"  name ="edit_deleted_files" value="">
 <input type="hidden" name ="uploaded_file_aws" value="">
 <input id="upload_edtt" class="post_file_edtt" name="file[]" style="display:none" type="file">
</div>
<div class="shareupdate-bottom post_categories_maincontent" style="">
 <div class="content-privacy post_categories_edit" style="">
  <div class="col-md-4">
   <h3>Category</h3>
   <p>Organise your content.</p>

   <div class="btn-group" style="width: 100%;">
    <button type="button" class="multiselect dropdown-toggle btn btn-default" data-toggle="dropdown" style="width: 100%; overflow: hidden; text-overflow: ellipsis;" title="">
      <span class="multiselect-selected-text category_heading"></span>
      <b class="caret"></b>
    </button>
    <ul class="multiselect-container dropdown-menu">
     @if($data->category_tags)
     @foreach($data->category_tags as $key=>$category)
     @if($category!='')
     <li class=""><a tabindex="0"><label class="radio"><input class="cat_id_edtt" value="{{$key}}" catgoryname="{{$category}}" type="radio">{{$category}}</label></a></li>
     @endif
     @endforeach
     @endif
   </ul>
 </div>
 <input type="hidden" class="editcategory" name="editcategory" value="">
</div>
<div class="col-md-4 mid-border divRatings1 r1">
 <h3>Visibility</h3>
 <p>Control who sees your content.</p>
 <div class="dropdown-wrap">
   <?php   $approve_user_count = sizeOfCustom($approve_user);  if($approve_user_count > 1) { $disable = ""; } else { $disable = "disabled"; } ?>
   <button type="button" class="multiselect dropdown-toggle btn btn-default" data-toggle="dropdown" title="" <?php echo $disable;?> >
     <span class="post-visiblity-label selection_visibility_edt edit_selection_visibility">Everyone</span>
     <b class="caret"></b>
   </button>
   <?php if(sizeOfCustom($approve_user) > Config::get('constants.APPROVED_USER')){
    $style = "overflow-y: scroll;max-height:295px;";}else{ $style = ""; }?>
    <ul class="visibilty-drop visibilty-drop-edt" style="<?php echo $style;?>">
      <li class="multiselect-item">
       <label class="checkbox blue_check_bx">
        <input value="multiselect-all" type="checkbox" class="post-visiblity-checkbox select_all_visibility_edt select_all_visibility_edita" >Everyone</label>
      </li>
      @foreach( $approve_user as $key)
      @if(Auth::user()->id!=$key['user']['id'])
      <li class="checkbox edit-checkbox">
       <label class="checkbox blue_check_bx">
         <input name="editvisibility[]" value="{{ $key['user']['id'] }}" type="checkbox" class="edit-post-checkbox visiblity-checkbox" checked="">{{ ucfirst($key['user']['first_name']) }} {{ ucfirst($key['user']['last_name']) }}
         @if(!empty($key['sub_comp']))
         <span class="community-member-company">{{$key['sub_comp']['company_name']}}</span>
         @else
         <span class="community-member-company">{{$companies_dictonary[$key['company_id']]}}</span>
         @endif
       </label>

     </li>
     @endif
     @endforeach
   </ul>
   <input type="hidden" class="hidden_edit_everyone_box" name="hidden_edit_everyone_box" value="false">
 </div>
</div>
<div class="col-md-4 divRatings1 r2">
 <h3>Alerts</h3>
 <p>Control who receives an email alert.</p>
 <div class="dropdown-wrap edit-alert-bx-disable"  >
  <button type="button" class="multiselect dropdown-toggle btn btn-default" data-toggle="" title="">
    <span class="selection_alert_edit_disable">Disabled</span>
    <b class="caret"></b>
  </button>
</div>

<div class="dropdown-wrap edit-alert-bx" style="display:none;">
 <?php   $approve_user_count = sizeOfCustom($approve_user);  if($approve_user_count > 1) { $disable = ""; } else { $disable = "disabled"; } ?>
 <button type="button" class="multiselect dropdown-toggle btn btn-default" data-toggle="dropdown" title="" <?php echo $disable;?> >
   <?php if($approve_user_count == Config::get('constants.COUNT_ONE')) { ?>
     <span class="selection_alert_edt">Nothing Selected</span>
     <?php } else { ?>
       <span class="post-alert-label selection_alert_edt">Everyone</span>
       <?php } ?>
       <b class="caret"></b>
     </button>
     <?php if(sizeOfCustom($approve_user) > Config::get('constants.APPROVED_USER')){
      $style = "overflow-y: scroll;max-height:295px;";}else{ $style = ""; }?>
      <ul class="alert-drop alert-drop-edt" style="<?php echo $style;?>">
        <li class="multiselect-item">
         <label class="checkbox"><input value="multiselect-all" type="checkbox" class="post-alert-checkbox select_all_alert_edt" checked="checked">Everyone</label>
       </li>
       @foreach( $approve_user as $key)
       @if(Auth::user()->id!=$key['user']['id'])
       <li class="checkbox">
         <label class="checkbox"><input name="editalert[]" value="{{ $key['user']['id'] }}" type="checkbox" class="edit-post-alert-checkbox alert-checkbox" >{{ ucfirst($key['user']['first_name']) }} {{ ucfirst($key['user']['last_name']) }}</label>
       </li>
       @endif
       @endforeach
     </ul>
   </div>
 </div>
</div>
<!-- content-privacy -->
</div>
<!-- share-inner-wrap -->
<div class="shareupdate-bottom top-repost" id="post_button">
 <!--  <div class="top-repost" style="text-align: right; padding-right: 20px;"> -->
 <input id="top-post1" class="repost" name="repost" value="" type="checkbox">
 <label for="top-post1" >Repost this to the top of Client Share feed </label>
 <!--  </div> -->
 <div class="shareupdate-bottom-buttons">
   <button type="button" href="" class="btn btn-primary right post_btn submit_edit_post" id="edit_post_btn_new">Save</button>
   <button type="button" href="" class="btn btn-default right post_btn" onclick="return cancel_edit_post()">Cancel</button>
 </div>
</div>
<input name="url_embed_toggle" value="0" type="hidden">
<!-- shareupdate-bottom -->
</div>
</form>
<!-- edit_popup_skull end -->