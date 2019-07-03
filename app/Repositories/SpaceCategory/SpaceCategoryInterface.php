<?php

namespace App\Repositories\SpaceCategory;

interface SpaceCategoryInterface
{
	Public function getAllSpace();
	public function setDefaultCategory($space);
	public function getSpaceCategories($space_id);
	public function renameCategories($categories, $space_id);
}