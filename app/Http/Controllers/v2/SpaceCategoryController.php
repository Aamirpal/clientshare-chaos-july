<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Repositories\SpaceCategory\SpaceCategoryInterface;

class SpaceCategoryController extends Controller
{
    protected $space_category;
    
    public function __construct(SpaceCategoryInterface $space_category) {
		$this->space_category = $space_category;
    }

    public function getCategories($space_id){
        $space_categories = $this->space_category->getSpaceCategories($space_id);
        return apiResponseComposer(200,[],['space_categories'=>$space_categories,'space_id'=> $space_id]);
    }
}
