@php
	$formated_comment_text = formatCommentText($comment, Config::get('constants.post_comment_string_limit'));
@endphp

@if(strlen(strip_tags($comment)) <= Config::get('constants.post_comment_string_limit') )
{!! $formated_comment_text['comment_after_process'] !!}
@else
<div class="show_less_comment_ajax{{$comment_id}} post-desc">{!! $formated_comment_text['comment_after_process_short'].'...' !!}</a><span class="show_extra_comment_ajax blue-span" top-id="{{$comment_id}}"> Show more</span></div>
</div>
<div class="show_more_comment_ajax{{$comment_id}}  post-desc" style="display:none;">{!! $formated_comment_text['comment_after_process'] !!}<span class="not_show_comment_ajax blue-span" top-id='{{$comment_id}}'"> Show less</span>
</div>
@endif