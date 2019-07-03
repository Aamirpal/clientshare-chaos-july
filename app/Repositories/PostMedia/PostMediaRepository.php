<?php

namespace App\Repositories\PostMedia;

use App\Repositories\PostMedia\PostMediaInterface;
use App\Models\PostMedia;
use App\Helpers\Post as PostHelper;
use App\Models\Post;
use App\Models\Group;

class PostMediaRepository implements PostMediaInterface
{
  protected $post_media;
  protected $post;

  public function __construct(PostMedia $post_media, Post $post) 
  {
    $this->post_media = $post_media;
    $this->post = $post;
  }

  public function getAttachments($post_id)
  {
  	return $this->post_media->where('post_id', $post_id)->get()->toArray();
  }

  public function getAttachmentsById($attachments_id)
  {
  	return $this->post_media->whereIn('id', $attachments_id)->get()->toArray();
  }

  public function getAttachmentByUrl($url)
  {
    return $this->post_media->whereRaw("metadata::text ilike '%$url%' or post_file_url ilike '%$url%'")->get()->toArray();
  }

  public function deleteAttachments($attachments_id)
  {
  	return $this->post_media->whereIn('id', $attachments_id)->delete();
  }

  public function getPostIdsForMedia($request, $login_user)
  {
    $post_ids = $this->post->where('space_id', $request['space_id']);
    if(isset($request['filters']['catgories'])){
      $post_ids->whereIn('space_category_id', $request['filters']['catgories']);
    }
    $post_ids->where('post_subject', 'ilike', '%'.$request['filters']['post_subject'].'%');
    $post_ids->hasPostAccess($login_user);
    if(isset($request['filters']['users'])){
      if(count($request['filters']['users']) >0){
        $post_ids->whereIn('user_id', $request['filters']['users']);
      }
    }
    return $post_ids->pluck('id');
  }

  public function postFiles($request, $login_user)
  {
    parse_str(urldecode($request['filters']??''), $filtered_data);
    $request['filters'] = $filtered_data;
    
    $post_ids = $this->getPostIdsForMedia($request, $login_user);

    $post_media = $this->post_media->select(\DB::raw("metadata->>'size' as file_size, s3_file_path as file_url, metadata->>'originalName' as post_file_name, post_id, id, created_at, metadata"));
    $post_media->whereIn('post_id', $post_ids);
    if(isset($request['filters']['date_range'])){
      if($request['filters']['date_range']){
          $post_media->whereBetween('created_at', explode("-", $request['filters']['date_range']));
      }
    }
    if(isset($request['file_type'])){
      if($request['file_type']){
        $extensions = $this->fileExtensionsList($request);
        if($extensions){
          $post_media->whereRaw("substring(metadata->>'originalName' from '\.([^\.]*)$') in ($extensions)");
        }
      }
    }
    
    $post_media = $post_media->where('metadata->originalName', 'ILIKE', '%'.$request['filters']['file_name'].'%')
            ->with(['post' => function($post_query){
                $post_query->select('id', 'user_id', 'post_subject', 'space_category_id');
                $post_query->with(['user' => function($user_query){
                  $user_query->select('id', 'first_name', 'last_name');
                }]);
                $post_query->with(['categoryName' => function($categpry_query){
                  $categpry_query->select('id', 'name');
                }]);
            }])
            ->orderBy($request['order_by']??1, $request['order']??1)
            ->limit($request['limit'])
            ->offset($request['offset']??0)
            ->get()
            ->toArray();
    foreach ($post_media as $key => $file) {
      $post_media[$key]['file_url'] = filePathJsonToUrl($file['file_url']);
    }

		return $post_media;
  }

  private function fileExtensionsList($request)
  {
    $output = "";
    $file_conversion = $this->post_media::FILE_FILTERS;
    foreach($request['file_type'] as $filetype){
      if(isset($file_conversion[$filetype])){
        foreach($file_conversion[$filetype] as $extension){
          $output .= "'".$extension."',";
        }
      }
    }
    return rtrim($output,',');;
  }

}