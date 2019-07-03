<!-- <pre><?php //print_r(Session::get('user_spases_space_user'));exit;?>  -->
<form class="add_post_form" method="POST" enctype="multipart/form-data" action="../addpost" style="display:none">
   <div class="shareupdate-wrap box " id="tour2">
      <div class="form-submit-loader" style="display:none">
         <span></span>
      </div>
      <div class="share-inner-wrap post-sbj">
         @if(!empty( Auth::user()->profile_image_url ))
         <span style="background-image: url('{{ Auth::user()->profile_image_url }}');" class="dp pro_pic_wrap" ></span>
         @endif
         @if(empty(Auth::user()->profile_image_url))
         <span style="background-image: url('{{ env('APP_URL') }}/images/dummy-avatar-img.svg');" class="dp pro_pic_wrap" ></span>
         @endif
         {{ csrf_field() }}
         <input type='hidden' name="user[id]" value="{{ Auth::user()->id }}">
         <input type='hidden' name="space[id]" value="{{ $data->id }}">
         <div class="form-group dp-input ">
            <label class="subject_text" style="display:none;">Subject</label>
            <textarea disabled type="text" name="post[subject]" class="post-subject-textarea form-control t1-resize  post_subject remove_border" id="comment_input_area" wrap="virtual" data-validation-engine="validate[required]" placeholder="Click here to add text, files, links etc." rows="1" data-min-rows="1" style="padding-top: 11px;display:block;box-sizing: padding-box; overflow:auto; cursor: wait"></textarea>
            <div class="subject_class" style="display:none">
               <label class="dp-body">Body</label>
               <input type="hidden" name="thumbcheck" id="thumbcheck" value="0" />
               <textarea class="post-description-textarea form-control t2-resize main_post_ta remove_border1" id="comment_input_area" name="space[post]" placeholder="Add your post (you can include URL & YouTube links.)" rows="1" data-min-rows="1" style=" padding-top: 7px;"  data-validation-engine="validate[required]"></textarea>

               <input type="hidden" name="url_preview_data_json">

               <div class="url_embed_div">
               </div>
               <div class="attachment-header" style="margin-bottom: 7px;">
                  <label class="dp-body left">Attachments</label>
                  <label class="remove-all dp-body right remove_all_trigger" style="display:none; color: #0d47a1;cursor: pointer;float: right;min-width: auto;width: auto;">Delete all files</label>
               </div>

               <div class="upload-content post_categories_file post_attachment_skull" style="display:none">
                        <div class="progress">
                       <div class="progress-bar progress-bar-striped active" role="progressbar"
                       aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                       </div>
                     </div>
                  <span class="close">
                     <img src="{{ env('APP_URL') }}/images/ic_deleteBlue.svg" class="close_trigger" id="" onclick="close_preview(this)">
                  </span>
                  <!-- <img src="images/body-img.jpg" alt="" class="img-responsive"> -->
                  <div class="upload-text">
                     <h3>
                        <img src="{{ env('APP_URL') }}/images/ic_IMAGE.svg" alt="">
                        <span class="upload_file_name">
                        </span>
                     </h3>
                     <p></p>
                  </div>
                  <img id="blah" src="" alt="" class="img-responsive" style="display:none" />
                  <video width="400" controls class="post_video_preview" style="display:none">
                     <source src="" type="video/mp4" class="post_video_preview_s">
                     Your browser does not support HTML5 video.
                  </video>
               </div>


               <!-- upload-content -->
               <a href="#!" id="upload_link_stop" onclick="add_attachment_post(this);" class="title">Attach file(s)</a>
            </div>
         </div>
         <button type="button" class="btn btn-primary right post-button">Post</button>
         <!-- Url embed start -->
         <!-- Url embed end -->
         <!-- upload-content -->
      </div>
      <div class="shareupdate-bottom post_categories_maincontent" style="display:none">
         <div class="content-privacy post_categories" style="display:none">
            <div class="col-md-4" id="catg_id_contain">
               <h3>Category</h3>
               <p>Organise your content.</p>
               <span class="category_drop">
               <select  name ="category" class="category-drop form-control" id="catg_id">
               @if($data->category_tags)
               @foreach($data->category_tags as $key=>$category)
               @if($category!='')
               <option value="{{$key}}" @if($key=='category_1') selected @endif>{{$category}}</option>
               @endif
               @endforeach
               @endif
               </select>
               </span>
               <span class="category_drop_share" style="display:none;">
               <button type="button" class="multiselect disabled dropdown-toggle btn btn-default btn-visibility" data-toggle="dropdown" title="">
               <span class="">General</span>
               <b class="caret"></b>
               </button>
               <select  name ="category_2" class="" id="catg_id" style="display:none;">
               @if($data->category_tags)
               @foreach( $data->category_tags as $key=>$category )
               @if($category!='')
               <option value="{{$key}}" @if($key=='category_1') selected @endif>{{$category}}</option>
               @endif
               @php break; @endphp
               @endforeach
               @endif
               </select>
               </span>
            </div>
            <div class="col-md-4 mid-border divRatings1">
               <h3>Visibility</h3>
               <p>Control who sees your content.</p>
               <input value="<?php echo sizeOfCustom($approve_user);  ?>" id="visibilty_count" type="hidden" name="visible_to_count">
               <div class="dropdown-wrap visibilty-drop-wrap">
                     <?php   $cnt = sizeOfCustom($approve_user);  if($cnt > 1) { $dis = ""; } else { $dis = "disabled"; } ?>
                     <button type="button" class="multiselect dropdown-toggle btn btn-default btn-visibility" data-toggle="dropdown" title="" <?php echo $dis;?> >
                     <span class="post-visiblity-label selection_visibility">Everyone</span>
                     <b class="caret"></b>
                     </button>
                     <?php if(sizeOfCustom($approve_user) > '7'){
                        $style = "overflow-y: scroll;max-height:295px;";}else{ $style = ""; }?>
                     <ul class="visibilty-drop" style="<?php echo $style;?>">
                        <li class="member-popup-link" onclick="resetVisibiltyAlers(this)">
                           <a href="#" data-toggle="modal" data-target="#member-popup"><span><img src="{{ env('APP_URL') }}/images/ic_group_add.svg"></span>Manage Groups</a>
                        </li>

                        <div class="members-group">
                           <li class="multiselect-item">
                            <label class="checkbox active blue_check_bx"><input value="multiselect-all" type="checkbox" class="post-visiblity-checkbox select_all_visibility reset_vars" checked="checked">Everyone</label>
                           </li>
                           <!-- <li><label class="checkbox blue_check_bx"><input value="multiselect-all" type="checkbox" class="" checked="checked">Everyone</label></li>
                           <li><label class="blue_check_bx"><input value="multiselect-all" type="checkbox" class="" checked="checked">Buyer</label></li>
                           <li><label class="blue_check_bx"><input value="multiselect-all" type="checkbox" class="" checked="checked">Seller</label></li>-->
                           <div class="groupli">
                            @foreach($group as $groups)
                              <li id="groupli_{{$groups['id']}}">
                                 <label id="{{$groups['id']}}" class="blue_check_bx">
                                    <input id="{{$groups['id']}}" name="group[]" value="" type="checkbox" class="visibility_group">{{$groups['group']}}
                                 </label>
                              </li>
                            @endforeach
                           </div>
                        </div>
                        <li class="search-box">
                             <input type="text" class="visible_search_in" placeholder="Search for people or companies">
                          </li>
                        <!-- <li class="multiselect-item">
                           <label class="checkbox active blue_check_bx"><input value="multiselect-all" type="checkbox" class="select_all_visibility" checked="checked">Everyone</label>
                        </li> -->
                        @foreach( $approve_user as $key)
                        @if(Auth::user()->id!=$key['user']['id'])
                        <li class="checkbox">
                           <label class="checkbox active blue_check_bx"><input name="visibility[]" value="{{ $key['user']['id'] }}" type="checkbox" class="checkbox1 visiblity-checkbox" checked="checked">{{ ucfirst($key['user']['first_name']) }} {{ ucfirst($key['user']['last_name']) }}
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
                  </div>
               <div class="dropdown-wrap visibilty-drop-wrap-share" style="display:none;">
                  <button type="button" class="multiselect disabled dropdown-toggle btn btn-default btn-visibility" data-toggle="dropdown" title="" >
                  <span class="">Everyone</span>
                  <b class="caret"></b>
                  </button>
                  <ul class="visibilty-drop" >
                     <li class="multiselect-item">
                        <label class="checkbox active blue_check_bx"><input value="multiselect-all" type="checkbox" class="post-visiblity-checkbox select_all_visibility" checked="checked" name="share_visibility">Everyone</label>
                     </li>
                  </ul>
               </div>
            </div>
            <div class="col-md-4 divRatings1" >
               <h3>Alerts</h3>
               <p>Control who receives an email alert.</p>
               <div class="dropdown-wrap alert-drop-wrap">
                  <?php   $cnt = sizeOfCustom($approve_user);  if($cnt > 1) { $dis = ""; } else { $dis = "disabled"; } ?>
                  <button type="button" class="multiselect dropdown-toggle btn btn-default" data-toggle="dropdown" title="" <?php echo $dis;?> >
                  <?php if($cnt == '1') { ?>
                  <span class="post-alert-label selection_alert">Nothing Selected</span>
                  <?php } else { ?>
                  <span class="post-alert-label selection_alert">Everyone</span>
                  <?php } ?>
                  <b class="caret"></b>
                  </button>
                  <?php if(sizeOfCustom($approve_user) > '7'){
                     $style = "overflow-y: scroll;max-height:295px;";}else{ $style = ""; }?>
                  <ul class="alert-drop" style="<?php echo $style;?>">
                     <li class="multiselect-item">
                        <label class="checkbox active"><input value="multiselect-all" type="checkbox" class="post-alert-checkbox select_all_alert" checked="checked">Everyone</label>
                     </li>
                     @foreach( $approve_user as $key)
                     @if(Auth::user()->id!=$key['user']['id'])
                     <li class="checkbox">
                        <label class="checkbox active"><input name="alert[]" value="{{ $key['user']['id'] }}" type="checkbox" class="alert-checkbox checkbox2" checked="checked">{{ ucfirst($key['user']['first_name']) }} {{ ucfirst($key['user']['last_name']) }}</label>
                     </li>
                     @endif
                     @endforeach
                  </ul>
               </div>
               <div class="dropdown-wrap alert-drop-wrap-share" style="display:none;">
                  <button type="button" class="multiselect dropdown-toggle btn btn-default" data-toggle="dropdown" title="" >
                  <span class="selection_alert_share active">Everyone</span>
                  <b class="caret"></b>
                  </button>
                  <ul class="alert-drop">
                     <li class="">
                        <label class="checkbox active"><input value="multiselect-all" type="checkbox" class="select_all_alert_share" checked="checked" name="share_alert[]">Everyone</label>
                     </li>
                  </ul>
               </div>
            </div>
            <div class="col-md-4 divRatings1 share-drop-wrap" style="display:none;">
               <h3>Choose Share</h3>
               <p>Which shares's would you to post this in?</p>
               <div class="dropdown-wrap">
                  <button type="button" class="dropdown-toggle btn btn-default" data-toggle="dropdown" title="" >
                  <span class="choose_share">Choose</span>
                  <b class="caret"></b>
                  </button>
                  <ul class="alert-drop multishare">
                     <li class="choose-dropdown">
                        <span>
                        Choose
                        <b class="caret"></b>
                        </span>
                     </li>
                     <li class="">
                        <label class="checkbox ">
                        <input value="multiselect-all" type="checkbox" class="share-everyone">All</label>
                     </li>
                     @php
                     $list_of_admin_spaces = Session::get('user_spases_space_user');
                     $spaces_count=0;
                     $spaces_count1=0;
                     @endphp
                     @if(isset($list_of_admin_spaces))
                     @foreach( $list_of_admin_spaces as $list_of_admin_space )
                     @if($list_of_admin_space['user_type_id'] == 2 && isset(Session::get('space_info')['id']) )
                     @if($list_of_admin_space['share']['id'] != Session::get('space_info')['id'])
                     <li>
                        <label class="checkbox ">
                        <input name="share[]" value="{{ $list_of_admin_space['share']['id'] }}" type="checkbox" class="checkbox_share" >{{ $list_of_admin_space['share']['share_name'] }}
                        </label>
                     </li>
                     @php ($spaces_count++)
                     @endif
                     @php ($spaces_count1++)
                     @endif
                     @endforeach
                     @endif
                  </ul>
               </div>
            </div>
         </div>
         <!-- content-privacy -->
         @if(isset(Session::get('space_info')['space_user'][0]['user_role']['user_type_name']) && Session::get('space_info')['space_user'][0]['user_role']['user_type_name'] == 'admin' && $spaces_count1>1)
         <input id="post-share1" type="checkbox" name="post_share" value="" class="post_share">
         <label for="post-share1" class="post_share">Share this post in multiple Client Shares:</label>
         @endif
      </div>
      @if ($errors->has('error_post'))
      <span class="error-msg1 text-left">
      {{ $errors->first('error_post') }}
      </span>
      @endif
      <!-- share-inner-wrap -->
      <div class="shareupdate-bottom " id="post_button">
         <button type="button" href="" class="btn btn-primary right post_btn" id="save_post_btn_new" style="display:none;">Post</button>
      </div>
      <input type="hidden" name="url_embed_toggle" value="0">
      <input type="hidden" name="uploaded_file_aws" value="0">
   </div>
</form>
<!--  S3 start  -->
<form action="<?php echo $s3FormDetails['url']; ?>"
      method="POST"
      enctype="multipart/form-data"
      class="direct-upload">

    <?php foreach ($s3FormDetails['inputs'] as $name => $value) { ?>
      <input type="hidden" name="<?php echo $name; ?>" value="<?php echo $value; ?>">
    <?php } ?>

    <!-- Key is the file's name on S3 and will be filled in with JS -->
    <input type="hidden" name="key" value="">
    <input id="upload" class="post_file" type="file" name="file" style="display:none">

    <!-- Progress Bars to show upload completion percentage -->
    <div class="progress-bar-area"></div>
</form>
<!-- S3 end -->

@if( Session::get('space_info')['space_user'][0]['user_type_id'] == 3 )
   @if( !Session::get('space_info')['allow_buyer_post'] && (Session::get('space_info')['space_user'][0]['user_company_id'] == Session::get('space_info')['company_buyer_id']) )
      <script>
         $('.add_post_form').remove();
         $('#discardModal').remove();
         $('.mid-content').addClass('no-add-post');
      </script>
   @elseif( !Session::get('space_info')['allow_seller_post'] && (Session::get('space_info')['space_user'][0]['user_company_id'] == Session::get('space_info')['company_seller_id']) )
      <script>
         $('.add_post_form').remove();
         $('#discardModal').remove();
         $('.mid-content').addClass('no-add-post');
      </script>
   @else
      <script>
         $('.add_post_form').show();
      </script>
   @endif
@else
      <script>
         $('.add_post_form').show();
      </script>
@endif


<!-- members popup -->
<div class="modal fade endrose in add_scroll" data-backdrop="static" id="member-popup" tabindex="-1" role="dialog" aria-labelledby="member-popup"><div class="modal-dialog modal-sm" id="group_modal" role="document">
      <div class="modal-content">
         <div class="add-new-group" >
           <form method="post" action="../updategroup" name="group-form1" id="groupform1"><!-- ../updategroup -->
           {{ csrf_field() }}
               <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{ env('APP_URL') }}/images/ic_highlight_removegray.svg" alt=""></button>
                  <h4 class="modal-title" id="myModalLabel">Manage Groups</h4>
                  <p>This makes it easier for you to share posts to the same group of people.</p>
               </div>
               <div class="modal-body">
                  <span class="tag-heading">Groups</span>
                  <a href="javascript:void(0)" class="add_group"><span><img src="{{ env('APP_URL') }}/images/ic_group_add.svg"></span>Add a new group</a>
                  <ul class="popup_group_list">
                     @foreach($group as $groups)

                        <li id="gid_{{$groups['id']}}" class="group_list"><span id="{{$groups['id']}}" onclick="groupMembers(this)">{{$groups['group']}}</span><img src="{{ env('APP_URL') }}/images/ic_deleteBlue.svg" class="del_group"></li>
                      @endforeach
                  </ul>
                  <div class="search-box-input" style="display:none;">
                     <input type="text" class="group_searching" placeholder="Search for people or companies" >
                  </div>
                  <div class="checkbox fullwidth group_list_members" style="display:none;">

                        <div class="label-wrap"><label class="group_everyone1">Everyone</label><input type="checkbox" name="group_everyone"></div>
                         @foreach( $approve_user as $key1)
                           @if(Auth::user()->id!=$key1['user']['id'])

                           <div class="label-wrap edit_list">
                              <input type="checkbox" id="glist_{{ $key1['user']['id'] }}" name="group_visibility1[]" class="v_chk" value="{{ $key1['user']['id'] }}" >
                              <label class="group_list_users1">{{ ucfirst($key1['user']['first_name']) }} {{ ucfirst($key1['user']['last_name']) }}
                              @if(!empty($key1['sub_comp']))
                              <span class="community-member-company">{{$key1['sub_comp']['company_name']}}</span>
                              @else
                              <span class="community-member-company">{{$companies_dictonary[$key1['company_id']]}}
                              </span>
                              @endif</label>

                           </div>

                           @endif
                           @endforeach
                  </div>
               </div>


               <div class="modal-footer" >
               <!-- <span class="group-buttons" style="display:none;"> -->
               <!-- <button type="button" class="btn btn-default " data-dismiss="modal" disabled="disabled">Cancel</button> -->
                 <button type="button" class="btn btn-default" disabled="disabled" id="cancel_group" >Cancel</button>
                 <button type="button" class="btn btn-primary" id="update_group" disabled="disabled" >Save</button>
               <!-- </span>  -->
                 <input type="hidden" name="groupid" class="hidden_group_id" value="">
               <!--   <input type="submit" name="send" value="submit"> -->
               </div>
               <span class="update_gmember_msg"></span>
            </form>
         </div><!-- add new group -->
         <div class="add-people" style="display: none;">
            <form method="post" action="" name="group-form" id="groupform">
            {{ csrf_field() }}
               <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{ env('APP_URL') }}/images/ic_highlight_removegray.svg" alt=""></button>
                  <h4 class="modal-title" id="myModalLabel">Create a Group</h4>
                  <p>To make a group available for selection, add the group name and members below.</p>
               </div>
               <div class="modal-body">
               <ul class="visibilty-drop-groups">
                  <span class="tag-heading">create group name</span>
                  <div class="input-wrap"> <input type="text" name="group" class="group_txt" value=""><span class="erro-group"></span></div>

                  <span class="tag-heading">Choose People</span>
                   <li class="search-box">
                             <input type="text" class="visible_search_in_groups" placeholder="Search for people or companies">
                          </li>
                  <div class="checkbox fullwidth ">

                     <div class="label-wrap"><input type="checkbox" name="group_everyone"><label class="group_everyone">Everyone</label></div>
                      @foreach( $approve_user as $r=>$key)
                        @if(Auth::user()->id!=$key['user']['id'])

                        <div class="label-wrap">
                           <input type="checkbox" id="{{ $key['user']['id'] }}" name="group_visibility[]" value="{{ $key['user']['id'] }}" >
                           <label class="group_list_users">{{ ucfirst($key['user']['first_name']) }} {{ ucfirst($key['user']['last_name']) }}
                            @if(!empty($key['sub_comp']))
                            <span class="community-member-company">{{$key['sub_comp']['company_name']}}</span>
                              @else
                              <span class="community-member-company">{{$companies_dictonary[$key['company_id']]}}</span>
                              @endif
                           </label>
                        </div>

                        @endif
                        @endforeach
                  </div>
               </ul>
               </div>
               <div class="modal-footer">

                 <button type="button" class="btn btn-default" id="cancel_save_group">Cancel</button>
                 <button type="button" class="btn btn-primary save_group" id="save_group">Save</button>


               </div>
            </form>
         </div><!-- add people -->

      </div><!-- modal content -->
   </div></div>
   <!--END members popup -->
<script>
var group_ids = [];
var group_ids1 = [];
   $(document).ready(function(){
      $(document).on('click','.post_share',function(){
         var tag = $(this);
         var status = this.checked;
         if(status){
           $('.category_drop').hide();
           $('.category_drop_share').show();

           $('.visibilty-drop-wrap').hide();
           $('.visibilty-drop-wrap-share').show();

           $('.alert-drop-wrap').hide();
           $('.alert-drop-wrap-share').show();
           //remove disabled class
           $('.alert-drop-wrap-share').find('ul li label').removeClass('disable_check');
           $('.alert-drop-wrap-share').find('ul li label input').removeAttr('disabled');
           $('.share-drop-wrap').find('ul li label').removeClass('disable_check');
           $('.share-drop-wrap').find('ul li label input').removeAttr('disabled');
           $('.select_all_alert_share').parent().addClass('active');
           $('.select_all_alert_share').attr('checked','checked');
           $('.selection_alert_share').text('Everyone');



           $('.share-drop-wrap').show();
           $(this).parent().addClass('share-box');
         }else{
            $('.category_drop_share').hide();
            $('.category_drop').show();

            $('.visibilty-drop-wrap-share').hide();
            $('.visibilty-drop-wrap').show();

            $('.alert-drop-wrap-share').hide();
            $('.alert-drop-wrap').show();
            $('.share-drop-wrap').hide();
            $(this).parent().removeClass('share-box');
         }
      });

      $(document).on('change','.share-everyone',function(){
         var status = this.checked;
         if(!$(this).parent().hasClass('active') && !$(this).attr("checked")){
            $(this).parent().addClass('active');
            $(this).attr('checked','checked');
            $('.checkbox_share').attr('checked','checked');
            $('.checkbox_share').parent().addClass('active');

         }else{
            $(this).parent().removeClass('active');
            $(this).removeAttr('checked');
            $('.checkbox_share').removeAttr('checked');
            $('.checkbox_share').parent().removeClass('active');
         }

         //for showing total share on top
         var checked_length=0;
          $('input.checkbox_share').each(function () {
             if($(this).parent().hasClass('active')){
               checked_length=checked_length+1;
             }
         });
         var selected_share =checked_length+' <span style="text-transform: lowercase;">share(s)</span>';
         if(checked_length==0){
            selected_share = 'Choose';
         }
         $('.choose_share').html(selected_share);
         //--------
      });
      $(document).on('change','.select_all_alert_share',function(){
         var status = this.checked;
         //if(status){
         if(!$(this).parent().hasClass('active') && !$(this).attr("checked")){
           $(this).parent().addClass('active');
            $(this).attr('checked','checked');
            $('.selection_alert_share').text('Everyone');
         }else{

            $(this).parent().removeClass('active');
            $(this).removeAttr('checked');
            $('.selection_alert_share').text('Nothing');
         }
      });


      $(document).on('change','.checkbox_share',function(){
         var status = this.checked;
         if(!$(this).parent().hasClass('active') && !$(this).attr("checked")){
           $(this).parent().addClass('active');
           $(this).attr('checked','checked');
         }else{
           $(this).parent().removeClass('active');
           $(this).removeAttr('checked');
         }
       // var checked_length = $('[name="share[]"]:checked').length;
         var checked_length=0;
          $('input.checkbox_share').each(function () {
             if($(this).parent().hasClass('active')){
               checked_length=checked_length+1;
             }
         });
         var share_length = '{{ $spaces_count }}';

         //for showing total share on top
         var selected_share =checked_length+' <span style="text-transform: lowercase;">share(s)</span>';
         if(checked_length==0){
            selected_share = 'NOTHING';
         }
         $('.choose_share').html(selected_share);
         //--------

          $('.share-everyone').parent().removeClass('active');
          $('.share-everyone').removeAttr('checked');

         if(checked_length==share_length){
            $('.share-everyone').parent().addClass('active');
            $('.share-everyone').attr('checked','checked');
         }
      });

  //-------------GROUP JS START
      $('#member-popup').on('hidden.bs.modal', function () {
         $('#update_group').attr('disabled','disabled');
         $('#update_group').prev().attr('disabled','disabled');
         //location.reload();
      })
      $(document).on('click','.add_group',function(){
         $('.group_list_members').hide();
         $('.add-new-group').hide();
         $('.add-people').show();
         $('.group_list_users').removeClass('active');
         $('.group_list_users').parent().find('input').removeAttr('checked');
         $('.group_txt').val('');
         $('.group_everyone').removeClass('active');
         $('.erro-group').text('');
         $('.visible_search_in_groups').val('');
         $(".visible_search_in_groups").trigger('keyup');
      });
      $(document).on('click','.group_everyone',function(){
         if($(this).hasClass('active')){
            $(this).removeClass('active');
            $('.group_list_users').removeClass('active');
            $('.group_list_users').parent().find('input').removeAttr('checked');


         }else{
            $(this).addClass('active');
            $('.group_list_users').addClass('active');
            //$('.group_list_users').parent().find('input').attr('checked','checked');
            $('.group_list_users').parent().find('input').prop('checked','checked');
         }
      });

      $(document).on('click','.group_everyone1',function(){
         $('#update_group').removeAttr('disabled');
         $('#update_group').prev().removeAttr('disabled');
         if($(this).hasClass('active')){
            $(this).removeClass('active');
            $('.group_list_users1').removeClass('active');
            $('.v_chk').prop('checked',false);
            $('.group_list_users1').parent().find('input').removeAttr('checked');


         }else{
            $(this).addClass('active');
            $('.group_list_users1').addClass('active');
            //$('.group_list_users').parent().find('input').attr('checked','checked');
            $('.v_chk').prop('checked',true);
            $('.group_list_users1').parent().find('input').prop('checked','checked');
         }
      });


      $(document).on('click','.group_list_users',function(){
         var total_length = $('.group_list_users').length;
         if($(this).hasClass('active')){
            $(this).removeClass('active');
             $(this).parent().find('input').removeAttr('checked');
         }else{
            $(this).addClass('active');
            //$(this).parent().find('input').attr('checked','checked');
            $(this).parent().find('input').prop('checked','checked');
            //$(this).closest('input').prop('checked','checked');
         }
         var active_length = $('.group_list_users.active').length;
         if(active_length==total_length){
            $('.group_everyone').addClass('active');
         }else{
            $('.group_everyone').removeClass('active');
         }
      });

      $(document).on('click','.group_list_users1',function(){
         var total_length = $('.group_list_users1').length;

         $('#update_group').removeAttr('disabled');
         $('#update_group').prev().removeAttr('disabled');
         if($(this).hasClass('active')){

            $(this).removeClass('active');
            $(this).parent().find('input').removeAttr('checked');
         }else{
            $(this).addClass('active');
            //$(this).parent().find('input').attr('checked','checked');
            $(this).parent().find('input').prop('checked','checked');
            //$(this).closest('input').prop('checked','checked');
         }
         var active_length = $('.group_list_users1.active').length;
         if(active_length==total_length){
            $('.group_everyone1').addClass('active');
         }else{
            $('.group_everyone1').removeClass('active');
         }
      });

       $(document).on('click','#cancel_save_group',function(e){
         $('.add-people').hide();
         $('.add-new-group').show();
      });

      $(document).on('click','.save_group',function(e){
         e.preventDefault();
         if($('.group_txt').val()==''){
            $('.erro-group').text('Please enter group name');
            return false;
         }
        var form_data = new FormData($(this).closest('form')[0]);
        $.ajax({
                type: "POST",
                data: form_data,
                dataType: "json",
                processData: false,
                contentType: false,
                url: baseurl+'/addgroup',
                success: function(response) {
                  if(response==0){
                     $('.erro-group').text('This group is already exist');
                  }else if(response==1){
                     $('.erro-group').text('Please select at least one member in group');
                  }else{
                     $('.visible_search_in_groups').val('');
                     $(".visible_search_in_groups").trigger('keyup');
                      $('.search-box-input').hide();
                     var lidata = '';
                     $('.groupli').html('');
                     var visibility_groups = '';
                     $(response).each(function(index,data){
                        lidata = lidata+'<li id="gid_'+data.id+'" class="group_list"><span id="'+data.id+'" onclick="groupMembers(this)">'+data.group+'</span><img src="{{ env('APP_URL') }}/images/ic_deleteBlue.svg" class="del_group"></li>';

                        //for visibiity dropdown
                        visibility_groups = visibility_groups+'<li id="groupli_'+data.id+'"><label id="'+data.id+'" class="blue_check_bx"><input id="'+data.id+'" name="group[]" value="" class="visibility_group" type="checkbox">'+data.group+'</label></li>';

                     });
                     if(lidata!=''){
                        $('.popup_group_list').html(lidata);
                     }
                     $('.add-people').hide();
                     $('.add-new-group').show();

                     if(visibility_groups!=''){
                        $('.groupli').html(visibility_groups);
                     }
                  }
                },error: function(xhr, status, error) {   alert(error); }
            });
      });


      //###########################################
      $(document).on('click','#update_group',function(e){
         e.preventDefault();

        var form_data = new FormData($(this).closest('form')[0]);
        $.ajax({
                type: "POST",
                data: form_data,
                dataType: "html",
                processData: false,
                contentType: false,
                url: baseurl+'/updategroup',
                success: function(response) {
                  if(response==1){
                     //$('.update_gmember_msg').text('Please select at least on member in group');
                  }else if(response==0){
                     //$('.update_gmember_msg').text('Group member updated successfully');
                  }
                 // $('.group-buttons').hide();
                  $('.search-box-input').hide();
                  $('.group_list_members').hide();
                  $('.group_list').removeClass('active-line');
                  $('#update_group').attr('disabled','disabled');
                  $('#update_group').prev().attr('disabled','disabled');

                 },error: function(xhr, status, error) {   alert(error); }
            });
      });
    $(document).on('click','#cancel_group',function(e){
                  $('.search-box-input').hide();
                  $('.group_list_members').hide();
                  $('.group_list').removeClass('active-line');
                  $('#update_group').attr('disabled','disabled');
                  $('#update_group').prev().attr('disabled','disabled');
    });

     //##############################################
      $(document).on('click','.del_group',function(e){
       var gid = $(this).prev().attr('id');
        $.ajax({
                type: "GET",
                dataType: "html",
                url: baseurl+'/deletegroup?groupid='+gid,
                success: function(response) {
                  $('#gid_'+gid).hide();
                 // $('.group_list_members').hide();
                  $('.groupli').find('#groupli_'+gid).hide();
                 },error: function(xhr, status, error) {   alert(error); }
            });
      });
      //##############################################

      var member_count = 0;
      $(document).on('click','.select_all_visibility',function(e){
         $(".select_all_alert").addClass('active').removeClass('disable_check');
         $(".select_all_alert").parent().addClass('active').removeClass('disable_check');
      });
      //######################################################
           $(".reset_vars").change(function(){  //"select all" change
            $('.selection_visibility').parents().addClass('open');
            $('.visibility_group').removeAttr("checked");
            $('.visibility_group').parent().removeClass('active');
            group_ids=[];
            group_ids1=[];
            member_count = 0;
         });
     //##########  on click manage members  ################


     //#######################################################
//-------------GROUP JS END
   });
var togglegroupId=0;
function groupMembers(element){
         var groupId = $(element).attr('id');
         $('.search-box-input').show();
         $('.group_searching').show();
         $('.group_searching').val('');
         $(".group_searching").trigger('keyup');
         $('.group_list').removeClass('active-line');
            $(element).parent().addClass('active-line');
         //$('.group-buttons').show();
         $('#update_group').attr('disabled','disabled');
         $('#update_group').prev().attr('disabled','disabled');
         $('.hidden_group_id').val(groupId);
         var group_list_all = $('.edit_list').length;
         $('.group_list_users1').each(function(){
            $(this).removeClass('active');
            $('.v_chk').prop('checked',false);
         });
         //toggle functionality
         if(togglegroupId==0 || togglegroupId==groupId){
            if($('.group_list_members').is( ":visible" )){
               $('.group_searching').hide();
               $('.search-box-input').hide();
               $('.group_list').removeClass('active-line');
               $(element).parent().removeClass('active-line');

            }
            $('.group_list_members').toggle();
         }else{
            $('.group_list_members').show();

         }
         togglegroupId =  groupId;
        //alert(togglegroupId +'------'+ groupId);
         //toggle end

         $.ajax({
                type: "GET",
                dataType:"json",
                url: baseurl+'/get_group_members?gid='+groupId,
                success: function(response) {
                  var i = 0;
                  var selectedids = '';
                  $(response).each(function(index,data){
                  //.get().reverse()
                   /* $('#glist_'+data.space_user.user_id).prop('checked','checked');
                    $('#glist_'+data.space_user.user_id).attr('checked','checked');
                    $('#glist_'+data.space_user.user_id).next('.group_list_users1').addClass("active");*/

                          var pp =$('#glist_'+data.space_user.user_id).next('.group_list_users1').html();

                          //alert(pp); return false;
                          //$( ".group_list_members" ).prepend( '<div class="label-wrap edit_list"><input id="'+data.space_user.user_id+'" name="group_visibility1[]" class="v_chk" value="'+data.space_user.user_id+'" checked="checked" type="checkbox"><label class="group_list_users1 active">'+pp+'</label></div>' );
                         // if($('#glist_'+data.space_user.user_id).is(':visible')){
                           $(".group_list_members div:first-child").after( '<div class="label-wrap edit_list active"><input id="'+data.space_user.user_id+'" name="group_visibility1[]" class="v_chk" value="'+data.space_user.user_id+'" checked="checked" type="checkbox"><label class="group_list_users1 active">'+pp+'</label></div>');

                         // }
                          $('#glist_'+data.space_user.user_id).parent().hide();
                          //$('#'+data.space_user.user_id).parent().hide();
                          /*if(selectedids!=''){
                           selectedids=selectedids+','+data.space_user.user_id;
                          }else{
                           selectedids=selectedids+data.space_user.user_id;
                          }*/
                          selectedids = selectedids+','+data.space_user.user_id;
                          i++;


                  });
                  $('.group_everyone1').removeClass('active');
                  if(i==group_list_all){ $('.group_everyone1').addClass('active'); }
                  var map = {};
                  $('.edit_list').each(function(key,index){

                      var value = $(this).find('input').eq(0).attr('id');
                      if (map[value] == null){
                          map[value] = true;
                      } else {
                          $(this).remove();
                      }

                  });

                },error: function(message) {   alert('Error: Please refresh the page'); }
            });
            $( document ).ajaxSuccess(function( event, request, settings ) {
               $('#load_more').hide();
               selectedids = '';
            });


      }

      function getGroupByUser(userid){
         if(typeof userid!='undefined' || userid!=''){
          var groupids = [];

           /*$.each(group_ids, function( index, value ) {
               if(value == userid){
                  group_ids.splice( $.inArray(userid, group_ids), 1);
               }
               });*/

               $.ajax({
                   type: "GET",
                   dataType:"json",
                   url: baseurl+'/getgroupbyid?uid='+userid,
                   success: function(response) {
                      $(response).each(function(index,data){
                        //groupids.push(data.group_id);
                        groupids =groupids+','+data.group_id;
                        $("input:checkbox[id="+data.group_id+"]").prop("checked", false); // disable child
                        $("input:checkbox[id="+data.group_id+"]").removeAttr("checked");
                        $("input:checkbox[id="+data.group_id+"]").parent().removeClass('active');
                     });
                      //remove all matched goup ids from array
                        if(groupids!=''){
                         $.ajax({
                            type: "GET",
                            dataType:"json",
                            url: baseurl+'/groupmemberall?gid='+groupids,
                            success: function(response) {
                               $(response).each(function(index,data){
                                removeItem = data.space_user.user_id;
                                group_ids.splice( $.inArray(removeItem, group_ids), 1 );
                              });
                            },error: function(message) {   alert('Error: Please refresh the page'); }
                         });
                       }
                      },error: function(message) {   alert('Error: Please refresh the page'); }

            });
         }
      }
      function resetVisibiltyAlers(element){
         $('.selection_visibility').text('Everyone');
         $('.select_all_visibility').parents().addClass('active');

         $('.checkbox1').prop('checked',true);
         $('.checkbox1').attr('checked','checked');
         $('.checkbox1').parents().addClass('active');

         $('.selection_alert').text('Everyone');
         $('.select_all_alert').parents().addClass('active');

         $('.checkbox2').prop('checked',true);
         $('.checkbox2').attr('checked','checked');
         $('.checkbox2').parents().removeClass('disable_check');
         $('.checkbox2').parents().addClass('active');

         $('.groupli').find('.active').removeClass('active');
         $('.groupli').find('input').prop('checked',false);
         $('.groupli').find('input').removeAttr('checked');
         group_ids = [];
         group_ids1 = [];
      }

      /*Search Groups in Visibility*/
      $( document ).ready(function() {
          $('.group_searching').keyup(function(){
            //alert(1);
            var valThis = $(this).val().toLowerCase();

            $('.group_list_members div label').each(function(){
             var text = $(this).text().toLowerCase();
             var match = text.indexOf(valThis);
               if (match >= 0){
                    $(this).show();
             $(this).parent().css('border-bottom','1px solid #e0e0e0 ');
               } else{
                   $(this).hide();
             $(this).parent().css('border-bottom','none');
               }
                //return false;
            });
          });
         $("#groupform1").submit(function(e){
            e.preventDefault();
         });
   });
</script>
