<nav class="v2-navbar navbar navbar-laravel">
    <div class="navigation-col">
        <ul class="navbar-nav top-side-nav">
            <li class="{{Request::is('clientshare/*') || Request::is('/') ? 'active' : ''}}">
                <a title="Home" href="{{ env('APP_URL') }}/clientshare/{{ $session_data->id }}"><img src="{{asset('images/v2-images/home-icon.svg')}}" alt="Home" /></a>
            </li>
            <li class="{{Request::is('community_members/*') ? 'active' : ''}}">
                <a title="Community" href="{{ getenv('APP_URL')}}/community_members/{{ $session_data->id }}"><img src="{{asset('images/v2-images/community-icon.svg')}}" alt="Community')}}" /></a>
            </li>
            <li style="display:none">
                <a title="Analytics" href="{{ url('/analytics/'.$session_data->id,[],env('HTTPS_ENABLE', true)) }}"><img src="{{asset('images/v2-images/analytics-icon.svg')}}" alt="Analytics" /></a>
            </li>
            <li class="{{Request::is('post_files/*') ? 'active' : ''}}">
                <a title="File View" href="{{ route('post_files',['space_id'=> $session_data->id ]) }}"><img src="{{asset('images/v2-images/file-icon.svg')}}" alt="File" /></a>
            </li>
            @if($session_data->reports_count)
            <li class="{{Request::is('power_reports/*') ? 'active' : ''}}">
                <a title="Power BI" href="{{ url('/power_reports/'.$session_data->id,[],env('HTTPS_ENABLE', true)) }}">
                    <img src="{{asset('images/v2-images/pb-icon.svg')}}" alt="PowerBI" /></a>
            </li>
            @endif

            @if($feedback_status)
            <li style="display:none">
                <a title="Feedback" href="{{getenv('APP_URL')}}/feedback/{{$current_month}}/{{$current_year}}/{{ $session_data->id }}"><img src="{{asset('images/v2-images/feedback-icon.svg')}}" alt="Feedback" /></a>
            </li>
            @endif

            @if($is_logged_in_user_admin || ($session_data->twitter_handles != config('constants.EMPTY_JSON') && count(json_decode($session_data->twitter_handles, true)) > 0))
                <li id="twitter_convert">
                    <a title="Twitter" href="#"><img src="{{asset('images/v2-images/twitter-icon.svg')}}" alt="Twitter" /></a>
                </li>
            @endif
            <li class="{{Request::is('setting/*') ? 'active' : ''}}">
                <a title="Settings" href="{{getenv('APP_URL')}}/setting/{{ $session_data->id }}">
                    <img width="24" src="{{asset('images/v2-images/setting-icon.svg')}}" alt="Twitter" />
                </a>
            </li>
        </ul>
        <ul class="navbar-nav bottom-side-nav">
            <li class="cs-bottom-logo">
                <a title="myclientshare.com" target="_blank" href="http://www.myclientshare.com/">
                    <img src="{{asset('images/v2-images/cs-icon.svg')}}" alt="CS" /></a>
            </li>
        </ul>
    </div>
</nav>

<nav class="navbar navbar-laravel v2-navbar-mobile">
    <div class="navigation-col">
        <ul class="navbar-nav top-side-nav">
            <li class="{{Request::is('clientshare/*') || Request::is('/') ? 'active' : ''}}">
                <a title="Home" href="{{ env('APP_URL') }}/clientshare/{{ $session_data->id }}"><img src="{{asset('images/v2-images/home-icon.svg')}}" alt="Home" /> Home</a>
            </li>
            <li class="{{Request::is('community_members/*') ? 'active' : ''}}">
                <a title="Community" href="{{ getenv('APP_URL')}}/community_members/{{ $session_data->id }}"><img src="{{asset('images/v2-images/community-icon.svg')}}" alt="Community')}}" /> Community</a>
            </li>
        </ul>
    </div>
</nav>