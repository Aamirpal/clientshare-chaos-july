<?php

namespace App\Repositories\EndorsePost;

use App\Repositories\EndorsePost\EndorsePostInterface;
use App\Models\EndorsePost;

class EndorsePostRepository implements EndorsePostInterface {

    protected $endorse_post;

    public function __construct(EndorsePost $endorse_post) 
    {
        $this->endorse_post = $endorse_post;
    }

    public function getUsers($space_id, $post_id)
    {
        return $this->endorse_post
            ->where('post_id', $post_id)
            ->with('user')
            ->orderBy('created_at')->get()
            ->keyBy('id')
            ->toArray();
    }
}