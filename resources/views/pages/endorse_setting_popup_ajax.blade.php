@php
  $visibility_imp = explode(',',$post_data['visibility']);
@endphp
<div class="modal-dialog modal-sm active_pop" role="document">
   <div class="modal-content">
      <div class="modal-header">
         <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{ url('/') }}/images/ic_highlight_removegray.svg" alt=""></button>
         <h4 class="modal-title" id="myModalLabel">People</h4>
         <p>This post is shared with the following people</p>
      </div>
      <form method="post" class="visiblity_update_{{$post_data['id'] }}">
         <div class="modal-body">
            <div class="checkbox fullwidth">
               <input class="checkbox1 chkb1_{{$post_data['id']}}" type="checkbox" @if(sizeOfCustom($space_data)==1) disabled @endif name="checkboxall" value="1" @if(in_array('All',$visibility_imp))  checked="checked" @endif visibiliity-toogleall-edit-id="{{$post_data['id']}}" id="checkbox1"><label for="checkbox1">Everyone</label>
            </div>
            @php
              $array_count_usr = array();
            @endphp
            @foreach( $space_data as $key)
              @if($post_data != $key['user']['id'])
                @php
                  $array_count_usr[]=$key['user']['id'];
                @endphp
                @if($key['user_status'] != 1 )
                  <div class="checkbox fullwidth ">
                    <input id="checkbox4{{$key['user']['id']}}" type="checkbox" name="checkbox[]" value="{{$key['user']['id']}}"
                    @if((in_array($key['user']['id'], $visibility_imp)) || (in_array('All', $visibility_imp)))
                      checked="checked" 
                    @endif
                    class="visibility_checkbox_popup_{{$post_data['id']}} visbility_check" postid="{{$post_data['id']}}">
                    <label for="checkbox4{{$key['user']['id']}}">{{ ucfirst($key['user']['first_name'])}}  {{ ucfirst($key['user']['last_name'])}} </label>
                  </div>
                @endif
              @endif
            @endforeach
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default cancel_visibility" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary save_edit_visiblity" ediitvisible="{{$post_data['id'] }}" allvisibleuser="{{$post_data['visibility']}}" logedin-and-postuser="{{Auth::user()->id }},{{$post_data['user_id'] }}"  data-dismiss="modal" setting-id="{{$post_data['id']}}" space-id="{{$space_id}}">Save</button>
            <input type="hidden" class="hidden_count_visibility_{{$post_data['id']}}" value="{{sizeOfCustom($array_count_usr)}}">
         </div>
      </form>
   </div>
</div>