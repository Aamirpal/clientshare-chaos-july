<?php

namespace App\Repositories\PostView;

use App\Repositories\PostView\PostViewInterface;
use App\Models\PostView;

class PostViewRepository implements PostViewInterface
{
  protected $post_view;
  
  public function __construct(PostView $post_view) 
  {
    $this->post_view = $post_view;
  }

  public function create($post_view_data)
  {
  	return $this->post_view->create($post_view_data);
  }

  public function postViewUserList($post_id)
  {
  	return $this->post_view->where('post_id', $post_id)
  		->select('user_id', 'post_id')
  		->distinct('user_id', 'post_id')
  		->get();
  }
  
}