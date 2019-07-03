<?php

namespace App\Http\Controllers\v2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Post\PostInterface;
use App\Repositories\EndorsePost\EndorsePostInterface;
use App\Repositories\PostMedia\PostMediaInterface;
use App\Repositories\RemoveCloudFile\RemoveCloudFileInterface;
use App\Repositories\GroupUser\GroupUserInterface;
use App\Repositories\Group\GroupInterface;
use App\Repositories\SpaceUser\SpaceUserInterface;
use App\Traits\v2\Post as PostTrait;

class PostController extends Controller
{
    use PostTrait;

    protected $post, $group_user, $group, $space_user, $post_media, $remove_cloud_files, $endorse_post;
    const SEARCH_LIMIT = 5;
	
    public function __construct(
        PostInterface $post, GroupUserInterface $group_user,
        GroupInterface $group, SpaceUserInterface $space_user,
        PostMediaInterface $post_media, RemoveCloudFileInterface $remove_cloud_files,
        EndorsePostInterface $endorse_post
    ) {
		$this->post = $post;
        $this->group_user = $group_user;
        $this->group = $group;
        $this->space_user = $space_user;
        $this->post_media = $post_media;
        $this->remove_cloud_files = $remove_cloud_files;
        $this->endorse_post = $endorse_post;
    }

    public function getEndorseUsers($space_id, $post_id)
    {
        return $this->getPostEndorseUsers($space_id, $post_id);
    }

    public function update(Request $request, $post_id)
    {
        return $this->updatePost($request, $post_id);
    }

    public function delete($post_id)
    {
        return $this->deletePost($post_id);
    }

    public function pinPost($space_id, $post_id, $pin_status)
    {
        return $this->setPostPin($space_id, $post_id, $pin_status);
    }

    public function getPost($space_id, $post_id)
    {
        return $this->getSpacePost($space_id, $post_id);
    }

    public function getPosts(Request $request) 
    {
        return $this->getSpacePosts($request);
    }

    public function savePost(Request $request)
    {
        return $this->savePostData($this->post, $request);
    }
    
    public function globalSearch($keywords,$space_id,$user_id,$count)
    {
        if(!is_numeric($count)) abort(404);
        
        $count = $count * $this::SEARCH_LIMIT;
        $result = $this->post->SearchPosts($keywords,$space_id,$user_id,$count+1); 
        $total_count = sizeOfCustom($result);
        $result = array_slice($result, 0,$count);
        return apiResponseComposer(
            200,[],[
                'posts' => $result,
                'count' => $total_count,
                'spaceId' => $space_id,
                'userId' => $user_id
            ]);
    }
}
