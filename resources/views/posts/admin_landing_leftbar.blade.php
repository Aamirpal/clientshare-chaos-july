<?php 
$length = 0;
$style='';
if(!empty($data->executive_summary))
{
    $length = strlen($data->executive_summary);
    $style = 'display:none';
}
?>
@include('posts/admin_landing_executive')
@include('posts/admin_landing_community')
@include('posts/admin_landing_quick_links')
@include('posts/admin_landing_file_viewer')
<div class="twitter-feed-section-dashboard">
@include('posts/admin_landing_twitter_feed')
</div>
