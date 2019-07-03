<?php

namespace App\Repositories\SpaceCategory;

use App\Repositories\SpaceCategory\SpaceCategoryInterface;
use App\Models\Space;
use App\Models\{SpaceCategory, BusinessReview};

class SpaceCategoryRepository implements SpaceCategoryInterface
{
  const DEFAULT_CATEGORIES=[
    'General Updates',
    'Business Reviews',
    'Management Information',
    'Innovation & Added Value',
    'Company & Employee News',
    'Management Messages'
  ];

  public function getAllSpace(){
    return Space::get();
  }

  public function setDefaultCategory($space_id){
    if(SpaceCategory::where('space_id', $space_id)->count() == count(self::DEFAULT_CATEGORIES)){
      return 0;
    }
    foreach(self::DEFAULT_CATEGORIES as $category_name){
      $space_category['space_id'] = $space_id;
      $space_category['name'] = $category_name;
      if(!SpaceCategory::where($space_category)->exists()){
        $space_category['logo'] = config('constants.category_logos.'.strtolower(getCategorySlug($category_name)));
        SpaceCategory::create($space_category);
      }else{
        SpaceCategory::where($space_category)->update(['logo'=>config('constants.category_logos.'.strtolower(getCategorySlug($category_name)))]); 
      }
    }
  }
  
	public function getSpaceCategories($space_id){

    $space = Space::select('spaces.id', 'spaces.is_business_review_enabled')->where('id', $space_id)->first();
        $space->spaceCategories->each(function($space_category){
        $space_category->load([
            'posts' => function($post) {
                $post->with(['user'])->latest()->first();
            },
            'likes'=> function($like) {
                $like->with(['user'])->latest()->first();
            }
        ]);
    });

    return $this->formatResult($space);
  }

    private function formatResult($space) {
        $result = [];
        if ($space->spaceCategories->count() > 0) {
            foreach ($space->spaceCategories as $space_category) {
                if ($space_category->name == config('constants.BUSINESS_REVIEWS') && !$space->is_business_review_enabled) {
                    continue;
                }
                $category = [];
                $category['category_name'] = $space_category->name;
                $category['category_id'] = $space_category->id;
                $category['category_logo'] = $space_category->logo;
                $category['last_posted_by'] = $category['found_useful'] = null;
                if($space_category->name == 'Business Reviews') {

                  $br_last_posted = $this->businessReviewLastPosted($space->id);
                  $category['last_posted_by'] = $br_last_posted['last_posted_by'];
                  $category['last_posted_on'] = $br_last_posted['last_posted_on'];
                }
                if ($space_category->posts->count() > 0) {

                    $category['last_posted_by'] = ($space_category->posts[0]->user->count() > 0) ? $space_category->posts[0]->user->full_name : "";
                }
                if ($space_category->likes->count() > 0) {

                    $category['found_useful'] = ($space_category->likes[0]->user->count() > 0) ? $space_category->likes[0]->user->full_name : "";
                }
                $result[$space_category->id] = $category;
            }
        }
        return $result;
    } 

    private function businessReviewLastPosted($space_id){

        $business_review =  BusinessReview::select('user_id', 'updated_at')
        ->with(['user' => function($query){
            $query->select('id', 'first_name', 'last_name');
        }])
        ->where('space_id', $space_id)
        ->latest()
        ->first();
        $output['last_posted_by'] = $output['last_posted_on'] = null;
        if(isset($business_review->user->first_name)){
            $output['last_posted_by'] = $business_review->user->first_name . ' ' .  $business_review->user->last_name;
            $output['last_posted_on'] = date('M d, Y' ,strtotime($business_review->updated_at));
         }
        return $output;
    }

    public function renameCategories($categories, $space_id)
    {
      foreach($categories as $category_id => $category_name){
        if($category_name && $category_name != config('constants.BUSINESS_REVIEWS')){
          $space_category = SpaceCategory::where(['name'=>$category_name, 'space_id'=>$space_id]);
          if($space_category->count() > 0){
            continue;
          }
          $space_category = SpaceCategory::find($category_id);
          if($space_category){
            $space_category->name = $category_name;
            $space_category->save();
          }
        }
      }
      return 1; 
    }

    public function getSpaceCategoriesExceptBR($space_id)
    {
      return SpaceCategory::select('id', 'name')
                        ->where('space_id', $space_id)
                        ->where('name', '!=', config('constants.BUSINESS_REVIEWS'))
                        ->orderBy('id')
                        ->get();
    }
}
