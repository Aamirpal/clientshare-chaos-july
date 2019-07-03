<?php

namespace App\Repositories\EndorsePost;

interface EndorsePostInterface
{
    public function getUsers($space_id, $post_id);
}