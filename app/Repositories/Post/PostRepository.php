<?php

namespace App\Repositories\Post;

use App\Repositories\Post\PostInterface;
use App\Models\Post;
use App\Models\Space;
use App\Models\SpaceCategory;
use DB;
use function GuzzleHttp\json_encode;

class PostRepository implements PostInterface
{
  const MAX_SEARCH_STRING = 40;
  const POST_LIST_LIMIT = 3;
  const RULE = ['pin_post'=>2];
  protected $post;
  protected $space; 

  
  public function __construct(Post $post, Space $space) 
  {
    $this->post = $post;
    $this->space = $space;
  }

  public function getPostsGroupsByCategory($filters, $user_id)
  {
    return $this->post
      ->select('group_id')->distinct('group_id')
      ->where('space_id', $filters['space_id'])
      ->when(isset($filters['space_category_id']), function($query) use ($filters) {
        return $query->where('space_category_id', $filters['space_category_id']);
      })
      ->hasPostAccess($user_id)
      ->whereNotNull('group_id')
      ->get()->keyBy('group_id');
  }

  public function delete($post_id)
  {
    return $this->post->where('id', $post_id)->delete();
  }
  
  public function updatePost($updated_data)
  {
    $this->post->where('id', $updated_data['id'])->update($updated_data);
    return $this->post->where('id', $updated_data['id'])->first();
  }

  public function getPinPostCount($space_id) 
  {
    return $this->post->where([ 'space_id' => $space_id, 'pin_status' => true])->get()->count();
  }

  public function getPost($post_id, $user_id)
  {
    return $this->post->where('id', $post_id)
      ->with(['images', 'videos', 'documents', 'comments', 'endorseByMe', 'endorse' => function($endorse) use ($user_id) {
        return $endorse
                ->where('user_id', '!=', $user_id)
                ->orderBy('created_at', 'desc');
      }, 'categoryName' => function($category){
        return $category->select('id', 'name');
      }, 'user'=>function($user){
        return $user->select('id', 'first_name', 'last_name', 'profile_image', 'profile_thumbnail', 'circular_profile_image');
      }, 'postView'=>function($post_view){
        return $post_view->select('user_id', 'post_id')->distinct('user_id', 'post_id');
      }])
      ->withCount('endorse')
      ->first();
  }

  public function getPosts($filters, $user_id)
  {
    return $this->post->where('space_id', $filters['space_id'])
      ->when(isset($filters['group_id']), function($query) use ($filters) {
        return $query->where('group_id', $filters['group_id']);
      })
      ->when(isset($filters['space_category_id']), function($query) use ($filters) {
        return $query->where('space_category_id', $filters['space_category_id']);
      })
      ->with(['images', 'videos', 'documents', 'comments', 'comments.attachments', 'endorseByMe', 'endorse' => function($endorse) use ($user_id){
        return $endorse
                ->where('user_id', '!=', $user_id)
                ->orderBy('created_at', 'desc');
      }, 'categoryName' => function($category){
        return $category->select('id', 'name');
      }, 'user'=>function($user){
        return $user->select('id', 'first_name', 'last_name', 'profile_image', 'profile_thumbnail', 'circular_profile_image');
      }, 'postView'=>function($post_view){
        return $post_view->select('user_id', 'post_id')->distinct('user_id', 'post_id');
      }])
      ->hasPostAccess($user_id)
      ->withCount('endorse')
      ->take(self::POST_LIST_LIMIT)
      ->skip($filters['offset']??0)
      ->orderBy('pin_status', 'desc')
      ->orderBy('created_at', 'desc')
      ->get()
      ->keyBy('id')->toArray();
  }

  public function getPostsForGroupCommand($space_ids = '')
  {
      $post = $this->post->where('visibility', '!=', '');
      if($space_ids != ''){
          $post->whereIn('space_id', $space_ids);
      }
      return $post->latest()->get();
  }

  public function create($post_data)
  {
    return $this->post->create($post_data);
  }

  public function SearchPosts($keywords,$space_id,$user_id,$count) {

    $search_data = DB::table('posts as p')
      ->select(DB::raw("p.id as post_id,p.post_description,p.post_subject"))
      ->Join('users as u','p.user_id','u.id')
      ->where('p.space_id', '=',$space_id)
      ->where(function($q)use($user_id){
        $q->orWhere('p.visibility', 'ilike','%'.$user_id.'%')
        ->orWhere('p.visibility', 'ilike','%all%');
      })
      ->where('p.deleted_at', '=', null)            
      ->where(function($q)use($keywords){
        $q->orWhere('p.post_subject', 'ilike','%'.$keywords.'%')
        ->orWhere('p.post_description', 'ilike','%'.$keywords.'%')
        ->orWhere('u.first_name', 'ilike','%'.$keywords.'%')
        ->orWhere('u.last_name', 'ilike','%'.$keywords.'%')
        ;
      })     
      ->groupby('p.id','u.id')
      ->limit($count)
      ->get()->toArray();
    return $this->formatSearchData($search_data);
  }

  private function formatSearchData($search_data){
    foreach($search_data as $key => $data){
      if($this->removeUnwantedSearchString($data->post_subject) == ""){
        unset($search_data[$key]);
      }else{
        $search_data[$key]->post_subject =  $this->removeUnwantedSearchString($data->post_subject);
        $search_data[$key]->post_description = $this->removeUnwantedSearchString($data->post_description);
      }
    }
    return $search_data;
  }

  private function removeUnwantedSearchString($string){
    $string =  preg_replace('/[^A-Za-z0-9\-@&()-=+*!?, ]/', '',strip_tags($string));
    return strlen($string) > self::MAX_SEARCH_STRING ? substr($string, 0,self::MAX_SEARCH_STRING).'...' : $string;
  }

  public function SetRandomCategoryId(){
    $posts = Post::get();      
    foreach($posts as $post){
      if($post->space_id){
        $space_category = \App\Models\SpaceCategory::where('space_id',$post->space_id)->inRandomOrder()->first();
        if(isset($space_category->id)){
          $post->space_category_id = $space_category->id;
          $post->save();
        }
      }
    }
  }

  public function mergeCategories(){
    $csv = env('MAP_CATEGORIES_CSV');
    if(!$csv){
      return false;
    }
    $csv_data = array_map('str_getcsv', file($csv));
    $csv_header = $csv_data[0];
    unset($csv_data[0]);
    $mapping_data = [];
    foreach($csv_data as $row){
        $mapping_data[] = array_combine($csv_header, $row);
    }
    foreach($mapping_data as $row){
      $share = $this->space->find($row['share_id']);
      $share_categories_v1 = json_decode($share->category_tags, true);
      $space_cat_v2 = SpaceCategory::where('space_id',$row['share_id'])->pluck('id');
      $posts = $this->post->where('space_id', $row['share_id'])
        ->where(function($posts) use ($space_cat_v2) {
          $posts->whereNotIn('space_category_id', $space_cat_v2)
          ->orWhereNull('space_category_id');
        })->get();

      foreach($posts as $post){ 
        $post_category = is_array($post->metadata) ? json_decode(json_encode($post->metadata), true) : json_decode($post->metadata, true);
        if(isset($post_category['category'])){ 
          if(isset($share_categories_v1[$post_category['category']])){
            if($share_categories_v1[$post_category['category']] == $row['share_category_v1']){
              $space_category = SpaceCategory::where([['space_id',$row['share_id']], ['name',$row['share_category_v2']]]);
              if($space_category->exists()){
                $share_category_id_v2 = $space_category->first()->id;
                $post->space_category_id= $share_category_id_v2;
                $post->save();
              }
            }
          }
        }
      }
    }
  }
}
