<?php

namespace App\Repositories\GroupUser;

use App\Repositories\GroupUser\GroupUserInterface;
use App\Models\{
    GroupUser,
    Space,
    SpaceUser,
    Group
};

class GroupUserRepository implements GroupUserInterface {

    protected $group_user;

    public function __construct(GroupUser $group_user) 
    {
        $this->group_user = $group_user;
    }
    
    public function getGroupUser($group_id, $user_id, $selection_method)
    {
        return $this->group_user::where('user_id', $user_id)->where('group_id', $group_id)->$selection_method();
    }

    public function getGroupUsersId($group_id)
    {
        return $this->group_user
            ->where('group_id', $group_id)
            ->select('user_id')
            ->pluck('user_id')
            ->toArray();
    }

    public function allGroupMembers($share_id, $group_id) 
    {
        $result = [];
        $data = $this->group_user
            ->select(
                    \DB::raw("
                group_users.id as group_user_id,
                group_users.user_id as user_id,
                case
                    when group_users.user_id = '" . \Auth::User()->id . "' Then 'You'
                    Else concat(u.first_name, ' ', u.last_name)
                End as full_name, u.profile_image ,u.circular_profile_image "
            ))
            ->leftJoin('users as u', 'u.id', 'group_users.user_id')
            ->where('group_id', $group_id)
            ->get()
            ->toArray();
        foreach ($data as $filter_result) {
            $space_user = SpaceUser::where(['space_id' => $share_id, 'user_id' => $filter_result['user_id']])->first();
            if($space_user){
                $company = $space_user->userCompany;
                $filter_result['company_name'] = ($company->company_name) ?? '';
            }
            
            if ($filter_result['profile_image'] && $filter_result['circular_profile_image'])
                $filter_result['circular_profile_image'] = wrapUrl(composeFilePath(json_decode($filter_result['circular_profile_image'] ?? $filter_result['profile_image'], true)));
            $result[] = $filter_result;
        }
       return $result;
    }

    public function groupMemberExists($group_user_id) 
    {
        return $this->group_user->whereId($group_user_id)->exists();
    }

    public function deleteGroupMember($group_user_id) 
    {
        try {
            \DB::transaction(function () use($group_user_id) {
                return $this->group_user->whereId($group_user_id)->delete();
            }, 5);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getUserGroups($space_id, $user_id, $user_type) 
    {
        $space_permissions = Space::select(['allow_seller_post', 'allow_buyer_post'])->where(['id' => $space_id])->first();
        $group_users = Group::where(['space_id' => $space_id])
            ->withcount('groupUsers')
            ->where(function($q) use($space_id, $user_id){
                $q->orWhereRaw("groups.id in (select group_users.group_id from group_users where group_users.group_id=groups.id AND group_users.user_id='" . $user_id . "')");
                $q->orWhereRaw("groups.id in (select groups.id from groups where groups.name='Everyone' AND groups.space_id = '".$space_id."')");
            })
            ->orderBy('is_default','desc')
            ->orderBy('created_at','desc')
            ->get()
            ->mapWithKeys(function($group) {
                return ["'".$group['id']."'" => $group->toArray()];
            })
            ->toArray();

        if (isset($space_permissions->allow_seller_post)) {
            $space_permissions['user_type'] = $user_type;
            $space_permissions['allow_current_user_post'] = (($space_permissions->allow_seller_post && $user_type == config('constants.USER.role_tag.seller')) || ($space_permissions->allow_buyer_post && $user_type == config('constants.USER.role_tag.buyer')));
            $space_permissions['groups'] = $group_users;
        }
        return $space_permissions;
    }

    public function DeleteUserFromAllGroups($user_id, $space_id){
        $group_ids = Group::where('space_id',$space_id)->pluck('id');
        return $this->group_user->where('user_id',$user_id)->whereIn('group_id', $group_ids)->delete();
    }

}
