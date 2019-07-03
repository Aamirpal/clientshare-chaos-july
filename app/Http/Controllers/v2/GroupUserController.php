<?php

namespace App\Http\Controllers\v2;

use App\Repositories\GroupUser\GroupUserInterface;
use App\Repositories\SpaceUser\SpaceUserInterface;
use App\Http\Controllers\Controller;

class GroupUserController extends Controller
{
    protected $group_users;

    public function __construct(GroupUserInterface $group_users, SpaceUserInterface $space_user) 
    {
        $this->group_users = $group_users;
		$this->space_user = $space_user;
    }

    public function groupMembers($space_id, $group_id) {
        return apiResponseComposer(200, [], ['group_members' => $this->group_users->allGroupMembers($space_id, $group_id)]);
    }

    public function destroy($group_user_id) 
    {
        if (!$this->group_users->groupMemberExists($group_user_id)) {
           return apiResponseComposer(400, ['error' => 'This member do not exists in our record.'], []);
        }
        if ($this->group_users->deleteGroupMember($group_user_id)) {
            return apiResponseComposer(200, ['success' => 'Group has been deleted successfully.'], []);
        }
        return apiResponseComposer(400, ['error' => 'Some technical error found. Please try after some time.'], []);
    }

    public function getUserGroups($space_id, $user_id) 
    {
        $user_type = $this->space_user->checkUserBuyerOrSeller($space_id, $user_id);
        return apiResponseComposer(200, [], $this->group_users->getUserGroups($space_id, $user_id, $user_type));
    }

}
