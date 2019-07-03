<?php

namespace Acme\Repository\Eloquent;

use App\Space as SpaceModel;

use Acme\Repository\SpaceInterface;

class Space implements SpaceInterface
{
    
    protected $space;

    public function __construct(SpaceModel $space)
    {
        $this->space = $space;
    }

    public function saveCategories($request)
    {
        return $this->space
            ->where('id', $request['space_id'])
            ->update([
                'category_tags' => json_encode($request['categories'])
            ]);
    }

    public function updateSpace($space_id, $data){
        return $this->space
            ->where('id', $space_id)
            ->update($data);
    }
}