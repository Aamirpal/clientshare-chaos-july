
<div class="user-comments1 comments{{$postid}}">
<input type="hidden" value="{{ $morecheck }}" class="checkclickedviewmore{{$postid}}">
 
 @if($total_comments!='' && $total_comments >2 && $morecheck == false)   
     <a class="viewmore-comm view-more-comments" datapostid="{{$postid}}" spaceid="{{$spaceid}}" datauserid="" commentlimit="">View {{ $total_comments-2 }} more comments</a>
     <a class="viewmore-comm view-less-comments hidden" datapostid="{{$postid}}" spaceid="{{$spaceid}}" datauserid="" commentlimit="">View fewer comments</a>
   @endif
   @if($total_comments!='' && $total_comments >2 && $morecheck == true)   
     <a class="viewmore-comm view-less-comments" datapostid="{{$postid}}" spaceid="{{$spaceid}}" datauserid="" commentlimit="">View fewer comments</a>
   @endif
 
  @if(!empty($post_comments))
  
    @foreach($post_comments as $data_comment)
    @php 
    $data = (array)$data_comment;
    $data['profile_image_url'] = filePathUrlToJson($data['profile_image']);
    @endphp
      <div class="member-wrap single_comment" id="">
       @if(!empty($data['profile_image_url']))
           <?php if($data['spaceusers']) { ?>
            <span class="pro_pic_wrap dp" style="background-image: url('{{ $data['profile_image_url'] }}" ></span>
           <?php } else { ?>
            <a href="#!" class="title @if($data['spaceusers'])inactive_name @endif" @if(!$data['spaceusers'])onclick="liked_info(this);"  data-id="{{$data['id']}}"@endif>
            <span class="pro_pic_wrap dp" style="background-image: url('{{ $data['profile_image_url'] }}" ></span></a>
            <?php } ?>
       @endif

       @if(empty($data['profile_image_url']))
         <?php if($data['spaceusers']) { ?>
                       <span class="pro_pic_wrap dp" style="background-image: url('{{env('APP_URL')}}/images/dummy-avatar-img.svg" ></span>
                     <?php } else { ?>
                     <a href="#!" class="title @if($data['spaceusers'])inactive_name @endif" @if(!$data['spaceusers'])onclick="liked_info(this);"  data-id="{{$data['id']}}"@endif>
                     <span class="pro_pic_wrap dp" style="background-image: url('{{env('APP_URL')}}/images/dummy-avatar-img.svg" ></span></a>
                     <?php } ?>
        
       @endif


        <div class="name-wrap single-cmt-wrap user-comment-detail" id="cmt_inr_wrap_{{$data['commentid']}}">
        <?php if($data['spaceusers']) { ?>
                       {{ ucfirst($data['first_name']) }} {{ ucfirst($data['last_name']) }} @if($data['spaceusers']) (Inactive) @endif
                     <?php } else { ?>
                     <a href="#!" class="title @if($data['spaceusers'])inactive_name @endif" @if(!$data['spaceusers'])onclick="liked_info(this);"  data-id="{{$data['id']}}"@endif>  {{ ucfirst($data['first_name']) }} {{ ucfirst($data['last_name']) }} @if($data['spaceusers']) (Inactive) @endif</a>
                     <?php } ?>

                    @php
                      $formated_comment_text = formatCommentText($data['comment'], Config::get('constants.post_comment_string_limit'));                    
                      $formated_comment_text['comment_after_process'] = linkMentionUser($formated_comment_text['comment_after_process']);
                    @endphp
                    
                     <span class='full-comment-text' style="display:none;">
                        {!! $formated_comment_text['comment_after_process'] !!}
                     </span>
                     <?php if(strlen(strip_tags($data['comment'])) < Config::get('constants.post_comment_string_limit') ){ ?>
                          <div class="show_less_comment{{$data['commentid']}} post-desc @if($data['spaceusers']) user-inactive-comment @endif"> {!! $formated_comment_text['comment_after_process'] !!}</div>
                      <?php } else { ?>
                        <div class="show_less_comment{{$data['commentid']}} post-desc @if($data['spaceusers']) user-inactive-comment @endif">
                        @if(!empty($formated_comment_text['comment_after_process_short']))
                           {!! $formated_comment_text['comment_after_process_short'].'...' !!}</a><span class="show_extra_comment blue-span" top-id="{{$data['commentid']}}">&nbsp;&nbsp;&nbsp;Show More</span>
                        @else
                           {!! $formated_comment_text['comment_after_process'] !!}
                        @endif   
                        </div>
                        <div class="show_more_comment{{$data['commentid']}} post-desc @if($data['spaceusers']) user-inactive-comment @endif" style="display:none;">{!! $formated_comment_text['comment_after_process'] !!}<span class="not_show_comment blue-span" top-id="{{$data['commentid']}}">&nbsp;&nbsp;&nbsp;Show Less</span>
                         </div>
                     <?php }
                          ?>
           
           <span class="time">
            {{ Carbon\Carbon::parse($data['comment_created'])->timezone(\Auth::user()->timezone??'Europe/London')->format('F d, H:i') }}
             @if($data['comment_created']!=$data['comment_updated'])
                <span class="comment_edited">(edited)</span>
             @endif
           </span>
        </div>
         
        @if(Session::get('space_info')->toArray()['space_user'][0]['user_type_id']=='2' || Auth::user()->id == $data['user_id'])
      
        <div class="dropdown hover-dropdown white-background edit-comment">
            <a href="#" class="dropdown-toggle  dots" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
            <span></span>
            </a>
            <ul class="dropdown-menu @if(Auth::user()->id != $data['user_id']) del_comment @endif">
                @if(Auth::user()->id == $data['user_id'])
              
                 <li class="domain_inp_edit">
                    <a href="javascript:void(0)" id="{{$postid}}" commentid="{{ $data['commentid'] }}" spaceid="{{$spaceid}}" onclick="return edit_comment('{{ $data['commentid'] }}', this);">Edit comment</a> 
                 </li>
                @endif
               <li class="domain_inp_delete">
                  <a href="javascript:void(0)" id="{{$postid}}" commentid="{{ $data['commentid'] }}" spaceid="{{$spaceid}}" onclick="return delete_comment('{{$postid}}', '{{$data['commentid']}}', '{{$spaceid}}');">Delete comment</a>
                </li>
            </ul>
          </div>
        @endif     


      </div>
    @endforeach
  @endif
</div>
<script type="text/javascript">
    // $('.show_extra').click(function(){
       $(document).on('click','.show_extra',function(){
         var id = $(this).attr("top-id");
      $('.show_less'+id).hide();
      $('.show_more'+id).show();
     });
     //$('.not_show').click(function(){
       $(document).on('click','.not_show',function(){
      var id = $(this).attr("top-id");
      $('.show_less'+id).show();
      $('.show_more'+id).hide();
     });

    // $('.show_extra_comment').click(function(){
       $(document).on('click','.show_extra_comment',function(){
         var id = $(this).attr("top-id");
      $('.show_less_comment'+id).hide();
      $('.show_more_comment'+id).show();
     });
     //$('.not_show_comment').click(function(){
       $(document).on('click','.not_show_comment',function(){
      var id = $(this).attr("top-id");
      $('.show_less_comment'+id).show();
      $('.show_more_comment'+id).hide();
     });
</script>
