<?php

namespace Acme\Repository;


interface UserInterface
{
	public function updateUser($user_id, $data);
	public function generateProfileThumbnail();
}
