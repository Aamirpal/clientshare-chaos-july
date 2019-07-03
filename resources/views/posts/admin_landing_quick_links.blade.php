<div class="feed-tile pull-left executive-link feed-tile-small-box">
    <span class="tile-heading pull-left">Quick Links</span>
    @if( isset(Session::get('space_info')['space_user'][0]['user_role']['user_type_name']) && Session::get('space_info')['space_user'][0]['user_role']['user_type_name'] == 'admin')
    <span class="pull-right edit-icon">
        <a href="javescript:void();" data-toggle="modal" data-target="#quick_links_model">
            <img src="{{ url('/',[],$ssl) }}/images/ic_edit.svg">
        </a>
    </span>
    @endif
    <div class="quick-link-col">
        <div class="executive-link-col full-width"> </div>
    </div>
</div>