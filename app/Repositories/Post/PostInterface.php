<?php

namespace App\Repositories\Post;

interface PostInterface
{
	public function SearchPosts($space_id, $user_id, $keywords, $count);
	public function SetRandomCategoryId();
	public function mergeCategories();
	public function create($post_data);
	public function getPostsForGroupCommand();
	public function getPosts($filters, $user_id);
	public function getPinPostCount($space_id);
	public function updatePost($post_data);
	public function delete($post_id);
	public function getPostsGroupsByCategory($filters, $user_id);
}
