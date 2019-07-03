<?php

namespace App\Repositories\PostView;

interface PostViewInterface
{
	public function create($post_view_data);

	public function postViewUserList($post_id);
}
