@php 
      $ssl = false;
      if(env('APP_ENV')!='local')
            $ssl = true;
@endphp
      <div class="user-comments comments{{$posts['id']}}">
         @if(!empty($posts['comments']))
            @php
               $post_comments = $posts['comments'];
            @endphp
            @if(sizeOfCustom($post_comments) > config('constants.POST_COMMENT_ROW_LIMIT'))
               @if( isset($view_more) && $view_more )
                  <a class="viewmore-comm view-less-comments" datapostid="{{$posts['id']}}" spaceid="{{$space_id}}" datauserid="{{Auth::user()->id}}" commentlimit="">View fewer comments</a>
               @else
                  <a class="viewmore-comm view-less-comments hidden" datapostid="{{$posts['id']}}" spaceid="{{$space_id}}" datauserid="{{Auth::user()->id}}" commentlimit="">View fewer comments</a>
                  <a class="viewmore-comm view-more-comments" spaceid="{{$space_id}}" datapostid="{{$posts['id']}}" datauserid="{{Auth::user()->id}}" commentlimit="">View {{ sizeOfCustom($post_comments)-config('constants.POST_COMMENT_ROW_LIMIT') }} more comments</a>
               @endif
            @endif
         @endif
         @if(!empty($posts['comments']))
         @php
            $show_last_tree_comments = isset($view_more) && $view_more ? 0:sizeOfCustom($post_comments)-config('constants.POST_COMMENT_ROW_LIMIT');
         @endphp
         @php $i = 1;
         $hidden_post_comment = sizeOfCustom($post_comments)-Config::get('constants.POST_COMMENT_ROW_LIMIT'); @endphp
         @foreach($posts['comments'] as $data_comment)
         <div class="@if(sizeOfCustom($post_comments) > Config::get('constants.POST_COMMENT_ROW_LIMIT') && $i<=$hidden_post_comment) hidden @endif user-comment-post member-wrap single_comment" id="comment_wrap_{{ $data_comment['user']['id'] }}">
            @if(!empty($data_comment['user']['profile_image_url']))
               <?php if($data_comment['spaceuser'][0]['user_status']==1) { ?>
               <span class="pro_pic_wrap dp" style="background-image: url('{{ $data_comment['user']['profile_image_url'] }}');" ></span>
               <?php } else { ?>
               <a href="#!" onclick="liked_info(this);"  data-id="{{$data_comment['user']['id']}}" class="title @if($data_comment['spaceuser'][0]['user_status'] == 1 ) inactive_name @endif  "><span class="pro_pic_wrap dp" style="background-image: url('{{ $data_comment['user']['profile_image_url'] }}');" ></span></a>
               <?php } ?>
            @endif

            @if(empty($data_comment['user']['profile_image_url']))
            <?php if($data_comment['spaceuser'][0]['user_status']==1) { ?>
               <span class="pro_pic_wrap dp" style="background-image: url('{{ url('/',[],$ssl)}}/images/dummy-avatar-img.svg');" ></span>
            <?php } else { ?>
               <a  href="#!" onclick="liked_info(this);"  data-id="{{$data_comment['user']['id']}}" class="title @if($data_comment['spaceuser'][0]['user_status'] == 1 ) inactive_name @endif  "><span class="pro_pic_wrap dp" style="background-image: url('{{ url('/',[],$ssl)}}/images/dummy-avatar-img.svg');" ></span></a>
            <?php } ?>
            @endif
               @php
                  $comment_text = formatCommentText($data_comment['comment']);
               @endphp
               <div class="name-wrap single-cmt-wrap user-comment-detail" id="cmt_inr_wrap_{{$data_comment['id']}}">
               <div id="{{$data_comment['id']}}" class="post-comment @if(checkSeeMoreEligiblity($comment_text['comment_after_process'])) post-comment-text-expand @endif @if($data_comment['spaceuser'][0]['user_status'] == 1 ) user-inactive-comment @endif">
                  <?php if($data_comment['spaceuser'][0]['user_status']==1) { ?>
                     {{ ucfirst($data_comment['user']['first_name']) }} {{ ucfirst($data_comment['user']['last_name']) }} @if($data_comment['spaceuser'][0]['user_status'] == 1 ) (Inactive) @endif
                  <?php } else { ?>
                     <a href="#!" onclick="liked_info(this);"  data-id="{{$data_comment['user']['id']}}" class="title @if($data_comment['spaceuser'][0]['user_status'] == 1 ) inactive_name @endif  ">  {{ ucfirst($data_comment['user']['first_name']) }} {{ ucfirst($data_comment['user']['last_name']) }} @if($data_comment['spaceuser'][0]['user_status'] == 1 ) (Inactive) @endif </a>
                  <?php } ?>
                  <span class="post-comment-text post-desc" id="{{$data_comment['id']}}">{!! $comment_text['comment_after_process'] !!}</span>
               </div>
               @if(checkSeeMoreEligiblity($comment_text['comment_after_process']))
                  <span class="comment-show-less show-more">Show More</span>
               @endif
               <span class="time">
                  {{date('F d, H:i', strtotime($data_comment['created_at'])) }}
                  @if($data_comment['created_at']!=$data_comment['updated_at'])
                  <span class="comment_edited">(edited)</span>
                  @endif
               </span>
               <div class="upload-file-view">
                        <ul>
                           @foreach($data_comment['attachments'] as $attachments)
                              <li><a href="javascript:void(0);" onclick="viewPostAttachment('{{$attachments["file_url"]}}', '{{$attachments["metadata"]["mimeType"]}}', '{{$attachments["metadata"]["originalName"]}}', '{{$posts["id"]}}', '{{$attachments["metadata"]["size"]}}')">{{$attachments['file_name']}}</a></li>
                           @endforeach
                        </ul>
               </div>
            </div>
            @if(Session::get('space_info')->toArray()['space_user'][0]['user_type_id']==config('constants.USER_ROLE_ID') || Auth::user()->id == $data_comment['user_id'])
            <div class="dropdown hover-dropdown white-background edit-comment">
               <a href="#" class="dropdown-toggle  dots" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
               <span></span>
               </a>
               <ul class="dropdown-menu @if(Auth::user()->id != $data_comment['user_id']) del_comment @endif">
                  @if(Auth::user()->id == $data_comment['user_id'])
                  <li class="domain_inp_edit">
                     <a href="javascript:void(0)" id="{{$posts['id']}}" commentid="{{ $data_comment['id'] }}" spaceid="{{$space_id}}" onclick="return edit_comment('{{ $data_comment['id'] }}', this);">Edit comment</a>
                  </li>
                  @endif
                  <li class="domain_inp_delete">
                     <a href="javascript:void(0)" id="{{$posts['id']}}" commentid="{{ $data_comment['id'] }}" spaceid="{{$space_id}}" onclick="return delete_comment('{{$posts['id']}}', '{{$data_comment['id']}}', '{{$space_id}}');">Delete comment</a>
                  </li>
               </ul>
            </div>
            @endif
         </div>
          @php $i++; @endphp
         @endforeach
         @endif
         <!-- post comments end-->
      </div>
      <div class="pined-user-text-box">
         <span class="pro_pic_wrap dp" style="background-image: url('@if($profile_img) {{ $profile_img }} @else {{ url('/',[],$ssl)}}/images/dummy-avatar-img.svg @endif');" ></span>
         <div class="form-group dp-input comment-add-section" data-postid="{{$posts['id']}}">
            <div contenteditable="true" class="form-control no-border comment-area" id="comment_input_area{{$posts['id']}}" data-placeholder="Add a comment or tag someone using @..." areaid="{{$posts['id']}}" style="white-space: pre-line;" wrap="hard"></div>
             <div class="comment-attach-col" style="display:none;">
                  <input type="submit" value="File Attachment" class="comment_attachment comment_attachment_trigger" data-spaceid="{{$space_id}}" data-postid="{{$posts['id']}}" data-userid="{{Auth::user()->id}}" style="float:right;">
               </div>
            <input id="comment_btn_{{$posts['id']}}" type="submit" value="Send" name="sendmessage" class="send_comment invite-btn" spaceid="{{$space_id}}" datapostid="{{$posts['id']}}" datauserid="{{Auth::user()->id}}" style="float:right; display:none">
            <div class="attachment-box-row full-width" style="display:none">
                <div class="feed-post-attachment-box"></div>
               </div>
               <div class="comment_attachment_progress full-width {{$posts['id']}}"></div>
         </div>
      </div>