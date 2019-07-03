@php
$twitter_handles = getTwitterHandlersArray(Session::get('space_info')['twitter_handles']);
@endphp
@if(!empty($twitter_handles) || (isset(Session::get('space_info')['space_user'][0]['user_role']['user_type_name']) && Session::get('space_info')['space_user'][0]['user_role']['user_type_name'] == 'admin'))
<div class="modal fade custom-tile-popup twitter-popup twitter-popup-custom manage_twitter_feed_modal twitter_feed_modal" id="manage_twitter_feed_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content white-popup">
            <div class="modal-header">
                <h2 class="modal-title" id="manage_twitter_feed_modal_label">Twitter Management</h2>
                <p>Manage the feeds that are displayed on the {{$data->share_name}} Client Share.</p>
            </div>
            <form method="post" action="{{ url('/',[],$ssl) }}/save_twitter_feed" enctype="multipart/form-data" class="twitter_feed_form" id="twitter_handles">
            {!! csrf_field() !!}
                <div class="modal-body">
                    <input name="space_id" id="twitter_feed_space_id" autocomplete="off" value="{{$data->id}}" type="hidden">
                    <div class="link-columns twitter-handle-wrap full-width">
                        @if($twitter_handles && !empty($twitter_handles))
                        @foreach($twitter_handles as $handle_index => $handle_value)
                            <div class="col-md-12 twitter-handle">
                               <span class="link-input-icon"><p>{{$handle_index + 1}}</p></span>
                               <div class="twitter-input-col">
                                   <input type="text" name="twitter_handles[]" value="{{$handle_value}}" id="twitter_handle_{{$handle_index}}" class="form-control twitter-feed-input" placeholder="@twitterhandle" autocomplete="off" >
                                   <span class="twitter-close remove-handle"><img src="{{ url('/',[],$ssl) }}/images/ic_delete_hover.svg"></span>
                               </div>
                            </div>
                       @endforeach
                    @else
                        <div class="col-md-12 twitter-handle">
                           <span class="link-input-icon"><p>1</p></span>
                           <div class="twitter-input-col">
                            <input type="text" name="twitter_handles[]" value="" id="twitter_handle_0" class="form-control twitter-feed-input" placeholder="@twitterhandle" autocomplete="off">
                            <span class="twitter-close remove-handle"><img src="{{ url('/',[],$ssl) }}/images/ic_delete_hover.svg"></span>
                            </div>
                        </div>
                    @endif
                    </div>
                    <a href="javascript:void(0)" class="add-twitter-feed add-handle" @php if(!empty($twitter_handles) && sizeOfCustom($twitter_handles) >= 3) echo 'style ="display:none"' @endphp >
                        <span class="handle-add-icon">
                            <img src="{{ url('/',[],$ssl) }}/images/ic_add.svg">
                        </span>Add feed
                    </a>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-twitter-handles btn-quick-links pull-right" data-space="" @php if(empty($twitter_handles)) echo 'disabled ="disabled"' @endphp >Save</button>
                    <button type="button" class="close btn btn-primary btn-quick-links btn-twitter-handles" data-dismiss="modal" aria-label="Close">Cancel</button>
                </div>
            </form>
        </div>
   </div>
</div>
@endif