@php
$space_user = $session_data['space_user'];
$ssl = env('APP_ENV') == 'local'? false : true;
    $data = $session_data;
    $space_id = $session_data->id;
    @endphp
<div id="on_boarding" class="onboarding-container">
    <div class="feed-col left-content onboarding-flow-wrap" id="left-content">
        <div class="onboarding-left-wrap"></div>
        <div class="feed-left-part full-width theiaStickySidebar onboarding-right-wrap">
            <div class="user-profile-status-show user-profile-status-col">
            </div>
            @include('posts/v2-onboarding/admin_landing_leftbar')
        </div>
        @include('layouts.quick_links')
        @include('layouts.invite_colleague')
        @include('posts/v2-onboarding/twitter_popup')
    </div>
    @include('posts.v2-onboarding.user_profile_progress')
    @include('posts/v2-onboarding/modal')
    @include('posts/v2-onboarding/onboarding_popup', ['onboarding_data'=>objectToArray(Session::get('space_info')), 'user' => objectToArray(Auth::user())])
</div>