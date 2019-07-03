<?php

namespace App\Traits\v2;

use Auth;
use Validator;
use App\PostMedia;
use App\Models\{Group, GroupUser};

trait Post 
{
  protected $logged_in_user_id;

  protected function getPostEndorseUsers($space_id, $post_id)
  {
    $validator = Validator::make(compact('space_id', 'post_id'),[
      'post_id'=>'required|uuid',
      'space_id'=>'required|uuid'
    ], [
      'uuid'=> trans('messages.validation.uuid_format')
    ]);
    
    if($validator->fails()){
      return apiResponse([], 400, ['errors'=>$validator->errors()]);
    }

    return apiResponse($this->endorse_post->getUsers($space_id, $post_id));
  }

  protected function updatePost($request, $post_id)
  {
    $post_data = $request->all();
    $post_data['id'] = $post_id;
    $validator = Validator::make($post_data,[
      'id' => 'required|uuid',
      'post_description' => 'required',
      'post_subject' => 'required',
      'user_id'=>'required',
      'space_id'=>'required',
      'space_category_id'=>'required',
      'group_id'=>'required'
    ], [
      'uuid'=> 'Invalid format'
    ]);
    
    if($validator->fails()){
      return apiResponse([], 400, ['errors'=>$validator->errors()]);
    }

    if($request->has('url_preview')){
      $post_data['url_preview'] = json_encode($request->url_preview);
    }
    
    $this->updatePostAttachments($request, $post_id);
    
    unset($post_data['attachments'], $post_data['delete_attachments'], $post_data['user_id']);
    $post_data['visibility'] = $this->setVisibility($request->group_id);
    return apiResponse($this->post->updatePost($post_data));
  }

  protected function updatePostAttachments($request, $post_id)
  {
    if($request->delete_attachments) {
      $request->delete_attachments = array_map(function ($attachment) {return $attachment['attachmentID'];}, $request->delete_attachments);
      $delete_attachments = $this->post_media->getAttachmentsById($request->delete_attachments);
      $this->remove_cloud_files->logFiles($delete_attachments);
      $this->post_media->deleteAttachments($request->delete_attachments);
    }

    $attachments['files'] = $request->attachments??[];
    foreach ($attachments['files'] as $key => $attachment) {
      if($this->post_media->getAttachmentByUrl($attachment['url'])){
        unset($attachments['files'][$key]);
      }
    }

    $attachments['post']['id'] = $post_id;
    $this->savePostAttachment($attachments);
    return $request->all();
  }

  protected function deletePost($post_id)
  {
    $validator = Validator::make(compact('post_id'),[
      'post_id' => 'required|uuid'
    ], [
      'uuid'=> 'Invalid format'
    ]);
    
    if($validator->fails()){
      return apiResponseComposer(400,['errors'=>$validator->errors()],[]);
    }
    
    return apiResponseComposer(200, ['is_removed' => $this->post->delete($post_id)]);
  }

  protected function setPostPin($space_id, $post_id, $pin_status)
  {
    $validator = Validator::make(compact('space_id', 'post_id', 'pin_status'),[
      'space_id' => 'required|uuid',
      'post_id' => 'required|uuid',
      'pin_status' => 'required|boolean'
    ], [
      'uuid'=> 'Invalid format',
      'boolean' => 'The :attribute field must be boolean.'
    ]);
    
    if($validator->fails()){
      return apiResponseComposer(400,['errors'=>$validator->errors()],[]);
    }

    if($pin_status && $this->post->getPinPostCount($space_id) >= $this->post::RULE['pin_post']) 
      return apiResponseComposer(400,['errors'=>trans('messages.validation.pin_post_quota')]);
    $post = $this->post->updatePost([
      'id' => $post_id,
      'pinned_at' => date('Y-m-d h:i:s'),
      'pin_status' => $pin_status
    ]);
    return apiResponseComposer(200, ['post' => $post]);
  }
  
  protected function getSpacePost($space_id, $post_id)
  {
    $validator = \Validator::make(compact('space_id', 'post_id'),[
      'space_id' => 'required|uuid',
      'post_id' => 'required|uuid'
    ], [
      'uuid'=> 'Invalid format'
    ]);
    
    if($validator->fails()){
      return apiResponseComposer(400,['errors'=>$validator->errors()],[]);
    }

    return apiResponse($this->post->getPost($post_id, Auth::user()->id));
    
  }

  protected function getSpacePosts($request)
  {
    $validator = \Validator::make($request->all(),[
      'space_id' => 'required|uuid',
      'offset' => 'numeric',
      'group_id' => 'numeric',
      'space_category_id' => 'numeric'
    ], [
      'uuid'=> 'Invalid format'
    ]);
    
    if($validator->fails()){
      return apiResponseComposer(400,['errors'=>$validator->errors()],[]);
    }

    if(isset($request->space_category_id))
    {
      $space_groups = $this->post->getPostsGroupsByCategory($request->all(), Auth::user()->id);
    }
    return apiResponse([
      'space_groups' => $space_groups??[],
      'posts' => $this->post->getPosts($request->all(), Auth::user()->id),
      'offset' => ((int)$request->offset ? $request->offset : 0) + $this->post::POST_LIST_LIMIT,
    ]);
    
  }

  protected function savePostAttachment($post_data)
  {
    foreach ($post_data['files'] as $key => $value) {
      PostMedia::create([
        'post_id' => $post_data['post']['id'],
        'post_file_url' => $value['url'],
        's3_file_path' => filePathUrlToJson($value['url'], false),
        'metadata' => $value
      ]);
    }
    return $post_data['post'];
  }

  protected function savePostData($post_repo, $request)
  {
    $validator = Validator::make($request->all(),[
      'post_description' => 'required',
      'post_subject' => 'required',
      'user_id'=>'required',
      'space_id'=>'required',
      'space_category_id'=>'required',
      'group_id'=>'required'
    ]);
    
    if($validator->fails()){
      return apiResponseComposer(400,['errors'=>$validator->errors()],[]);
    }
    $this->logged_in_user_id = Auth::User()->id;
    $post_data = $request->all();
    $post_attachments['files'] = $post_data['attachments']??[];
    unset($post_data['attachments']);

    /*For CS:V1*/
    $post_data['metadata']['category'] = 'category_1';
    if(isset($post_data['url_preview']))
    {
      $post_data['metadata']['get_url_data'] = $post_data['url_preview'];
    }
    $post_data['visibility'] = $this->setVisibility($request->group_id);
    $post_data['comment_count'] = 0;
    /**/

    $post_attachments['post'] = $post_repo->create($post_data)->toArray();
    
    if($post_attachments){
      $this->savePostAttachment($post_attachments);
    }
    $this->sendEmailAlerts($post_attachments['post']);
    return apiResponseComposer(200, [], $post_repo->getPost($post_attachments['post']['id'], Auth::user()->id));
  }

  private function setVisibility($group_id){
    $group = Group::find($group_id);
    if($group){
      if($group->name == 'Everyone'){
        return 'All';
      }else{
        $group_users = GroupUser::where('group_id', $group->id)->pluck('user_id')->toArray();
        return implode(',', $group_users);
      }
    }
    return '';
  }

  private function sendEmailAlerts($post)
  {
    $users = $this->group_user->getGroupUsersId($post['group_id']);
    
    if(!$users && $this->group->find($post['group_id'])['name'] == $this->group::DEFAULT_GROUP) {
      $users = $this->space_user->getSpaceUsersId($post['space_id']);
    }

    $job_data = [
      'post'=>[
        'id'=>$post['id'],
        'subject'=>$post['post_subject'],
        'description'=>$post['post_description']
      ],
      'logged_in_user_id' => $this->logged_in_user_id,
      'receiver_ids' => $users,
      'share' => $post['space_id'],
      'current_space' => $post['space_id']
    ];
    dispatch(new \App\Jobs\SendEmailsOnPostSharing($job_data));
  }
}
