<?php

namespace App\Repositories\GroupUser;

interface GroupUserInterface
{
    public function allGroupMembers($space_id, $group_id);

    public function groupMemberExists($group_user_id);
    public function deleteGroupMember($group_user_id);
    public function getUserGroups($space_id, $user_id, $user_type);
    public function DeleteUserFromAllGroups($user_id, $space_id);
    public function getGroupUsersId($group_id);
    public function getGroupUser($group_id, $user_id, $selection_method);
}