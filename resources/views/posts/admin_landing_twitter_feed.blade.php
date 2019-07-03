@php
$twitter = Session::get('space_info')['twitter_handles'];
$twitter_handles = getTwitterHandlersArray($twitter);
@endphp
@if(!empty($twitter_handles) || (isset(Session::get('space_info')['space_user'][0]['user_role']['user_type_name']) && Session::get('space_info')['space_user'][0]['user_role']['user_type_name'] == 'admin'))
    <div class="twitter-feed full-width">
        <span class="tile-heading pull-left">Twitter Feed</span>
        @if( isset(Session::get('space_info')['space_user'][0]['user_role']['user_type_name']) && Session::get('space_info')['space_user'][0]['user_role']['user_type_name'] == 'admin' && !empty($twitter_handles))
        <span class="pull-right edit-icon"><a href="javescript:void(0);" data-toggle="modal" data-target="#manage_twitter_feed_modal"><img src="{{ env('APP_URL') }}/images/ic_edit.svg"></a></span>
        @endif
        <div class="twitter-content-column text-center full-width">
            @if(!empty($twitter_handles))
                @if(sizeOfCustom($twitter_handles) == 1)
                    @foreach($twitter_handles as $handle_index => $handle_value)
                        <div id="twitter_feed_{{$handle_index}}" class="tab-pane fade in @php echo ($handle_index == 0) ? 'active': ''; @endphp">
                            <a class="twitter-timeline" href="https://twitter.com/{{str_replace('@', '', $handle_value)}}"  data-chrome="noheader nofooter noscrollbar" data-width="429" data-height="900"></a>
                        </div>
                    @endforeach
                @else
                    <ul class="nav nav-tabs">
                        @foreach($twitter_handles as $handle_index => $handle_value)
                            <li class="@php echo ($handle_index == 0) ? 'active': ''; @endphp"><a data-toggle="tab" href="#twitter_feed_{{$handle_index}}">{{$handle_value}}</a></li>
                        @endforeach
                    </ul>
                    <div class="tab-content">
                        @foreach($twitter_handles as $handle_index => $handle_value)
                            <div id="twitter_feed_{{$handle_index}}" class="tab-pane fade @php echo ($handle_index == 0) ? 'active in': ''; @endphp">
                                <a class="twitter-timeline" href="https://twitter.com/{{str_replace('@', '', $handle_value)}}"  data-chrome="noheader nofooter noscrollbar" data-width="429" data-height="900"></a>
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
            	<h4>Pull in company specific updates from Twitter straight to Client Share.</h4>
            	<p>(All you need is the Twitter handle e.g. @myclientshare)</p>
            	<span class="community-invite add-twitter-feed full-width"><a class="btn btn-primary" href="javescript:void(0);" data-toggle="modal" data-target="#manage_twitter_feed_modal" >Add Twitter Feeds</a></span>
            	<h5>As admin, the feeds you set will be accessible by all users within this Share.</h5>
            @endif
        </div>
    </div>
    <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
    <script rel="text/javascript" src="{{ url('js/custom/twitter_feed.js?q='.env('CACHE_COUNTER', rand()),[],env('HTTPS_ENABLE', true)) }}"></script>
@endif
