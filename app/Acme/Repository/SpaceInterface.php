<?php

namespace Acme\Repository;


interface SpaceInterface
{
	public function saveCategories($request);
	public function updateSpace($space_id, $data);
}
