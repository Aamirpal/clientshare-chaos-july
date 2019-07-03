<?php

namespace App\Repositories\SpaceUser;

interface SpaceUserInterface
{
	public function communityTile($space_id);
	
	public function getSpaceUserActiveCount($space_id, $selection_method);
	
	public function communityMemberProfileImage($space_id, $limit);
    
	public function getUserDetails($space_id,$user_id);
    
	public function updateSpaceUser($space_id, $user_id, $data);

	public function searchSpaceUser($space_id, $search_key = "");

	public function getSpaceUsersId($space_id);
	
	public function getSpaceUsers($space_id);

	public function spaceUserExists($space_id, $user_id);

	public function getSpaceAdmin($space_id);

	public function getSpaceUser($space_id, $user_id);
}