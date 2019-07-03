<?php

namespace App\Jobs;

use App\Helpers\Logger;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\PostViews;
use App\Helpers\Postmark;

class PostViewCallback implements ShouldQueue {
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $message_id;
    public function __construct($message_id) {
        $this->message_id = $message_id;
    }

    public function handle() {
    	$data_return = (new Postmark)->postmarkCurl($this->message_id);
        $message_data['user_id'] = (new Postmark)->postmarkDataByKey($data_return, 'user_id: ');
        $message_data['space_id'] = (new Postmark)->postmarkDataByKey($data_return, 'share_id: ');
        $message_data['space_id'] = (new Postmark)->postmarkDataByKey($data_return, 'space_id: ')??$message_data['space_id'];
        $message_data['post_id'] = (new Postmark)->postmarkDataByKey($data_return, 'post_id: ');
        $post_views = new PostViews;
        if (isset($message_data['user_id']) && isset($message_data['space_id']) && $message_data['post_id']) {
            $post_views->user_id = $message_data['user_id'];
            $post_views->space_id = $message_data['space_id'];
            $post_views->post_id = $message_data['post_id'];
            $post_views->save();
        }
    }
}
