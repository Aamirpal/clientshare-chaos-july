<?php

namespace App\Repositories\Group;

use App\Models\Group;
use App\Repositories\Group\GroupInterface;
use App\Models\GroupUser;
use App\Models\SpaceUser;
use Validator;

class GroupRepository implements GroupInterface {

    protected $group;

    const DEFAULT_GROUP = 'Everyone';

    public function __construct(Group $group) 
    {
        $this->group = $group;
    }

    public function find($group_id)
    {
        return $this->group->find($group_id);
    }

    public function validateCreateGroup($request){
        $validator = Validator::make($request->all(), 
        [
                'name' => 'required|max:24|not_in:Everyone,everyone',
                'space_id' => 'required',
                'user_ids' => 'required',
                ], [
                'name.required' => 'Please add a group name before saving your group',
                'user_ids.required' => 'Please add users to the group'
        ]);
        if (!$validator->fails()) {
            $validator->after(function($validator) use($request) {
                $group = $this->group->where($request->only('space_id'))->whereRaw("trim(lower(name)) = '".trim(strtolower($request->name))."'");
                $validator = $this->userExistsValidation($group, $validator);
                $validator = $this->validateIfMemberSetExistsInGroups($request, $validator);
            });
        }
        return $validator;
    }

    private function userExistsValidation($group, $validator){
        if($group->exists()){
            if(!$this->isLoginUserExistsInGroup($group->first()->id)){
                $validator->errors()->add('name', 'This group name already exists. But You are not Member of this group. So, Please enter a unique group name.');
            }else{
                $validator->errors()->add('name', 'This group name already exists.');
            }
        }
        return $validator;
    }

    private function isLoginUserExistsInGroup($group_id){
        return GroupUser::where(['group_id'=>$group_id], ['user_id'=>\Auth::User()->id])->exists();
    }

    private function addLoginUserInUsersarray($user_ids){
        array_unshift($user_ids, \Auth::User()->id);
        return $user_ids;
    }
    private function validateIfMemberSetExistsInGroups($request, $validator, $editing_group_id = null) {
        $user_ids = ($editing_group_id) ? $request->user_ids : $this->addLoginUserInUsersarray($request->user_ids);
        $group_id = $this->isMemberSetExistsInGroups($request->space_id, $user_ids, $editing_group_id);
        if ($group_id) {
            $group_name = ($group_id == config('constants.EVERYONE_IN_SHARE')) ? config('constants.EVERYONE_IN_SHARE') : $this->groupNameByGroupId($group_id);
            $validator->errors()->add('user_ids', "This member list already exists in the $group_name group");
        }
        return $validator;
    }

    public function validateUpdateGroup($request) {
        $validator = Validator::make($request->all(), [
                'name' => 'required|max:24|not_in:Everyone,everyone',
                'space_id' => 'required',
                'user_ids' => 'required',
                'group_id' => 'required|numeric',
                ], [
                'name.required' => 'Please add a group name before saving your group',
                'user_ids.required' => 'Please add users to the group'
        ]);
        if (!$validator->fails()) {
            $validator->after(function($validator) use($request) {
                $group = $this->group->where($request->only('space_id'))->whereRaw("trim(lower(name)) = '".trim(strtolower($request->name))."'")->where('id', '!=', $request->group_id);
                $validator = $this->userExistsValidation($group, $validator);
                $validator = $this->validateIfMemberSetExistsInGroups($request, $validator, $request->group_id);
            });
        }
        return $validator;
    }
    private function isMemberSetExistsInGroups($space_id, $user_ids_in_group, $group_id = null) {
        $all_members_count = SpaceUser::where('space_id', $space_id)->active()->count();
        if (sizeOfCustom($user_ids_in_group) == $all_members_count)
            return config('constants.EVERYONE_IN_SHARE');

        $query = $this->group->where('space_id', '=', $space_id)->where('is_default', false);
        if ($group_id) {
             $query->where('id', '!=', $group_id);
        }
        $groups = $query->with(['groupUsers' => function($query) {
                    $query->select('group_id', 'user_id');
                }])->get()->toArray();
       
        $group_users = [];
        if (empty($groups))
            return false;
        foreach ($groups as $key => $group) {
            $group_users[$group['id']] = array_column($group['group_users'], 'user_id');
        }
       
        foreach ($group_users as $key => $member_set) {
            if (arraysAreEqual($user_ids_in_group, $member_set)) {
                return $key;
            }
        }
        return false;
    }
    private function groupNameByGroupId($group_id) {
         return $this->group->whereId($group_id)->value('name');
    }

    public function createGroup($request){
        $group = $this->group->create($request->only('name','space_id'));
        $this->addGroupUsers($group->id, $this->addLoginUserInUsersarray($request->user_ids));
        return $group;
    }

    private function addGroupUsers($group_id, $user_ids){
        foreach($user_ids as $user_id){
            $this->validateAndAddGroupMember($group_id, $user_id);
        }
    }

    private function validateAndAddGroupMember($group_id, $user_id){
        if(!GroupUser::where([['user_id', $user_id], ['group_id', $group_id]])->exists()){
            GroupUser::create([
                'user_id'=>$user_id,
                'group_id'=>$group_id
            ]);
        }
    }

    public function updateGroup($request) {
        $upate_group = $this->group->whereId($request->group_id)->update(['name' => $request->name]);
        if ($upate_group) {
            foreach ($request->user_ids as $user_id) {
                if (SpaceUser::where([['user_id', $user_id], ['space_id', $request->space_id]])->exists()) {
                    if (!GroupUser::where([['user_id', $user_id], ['group_id', $request->group_id]])->exists()) {
                        GroupUser::create([
                            'user_id' => $user_id,
                            'group_id' => $request->group_id
                        ]);
                    }
                }
            }
        }
    }

    public function isGroupExists($group_id)
    {
        return $this->group->whereId($group_id)->exists();
    }

    public function deleteGroup($group_id)
    {
        try{
            \DB::transaction(function () use($group_id) {
                GroupUser::where('group_id', $group_id)->delete();
                $this->group->whereId($group_id)->delete();
            },5);
            return true;
        }
        catch(\Exception $e){
            return false;
        }

    }

    public function makeOldPostGroups($post)
    {
        if(strpos($post->visibility,'All') !== false ){
            $group_data = [
                'space_id'=>$post->space_id,
                'name'=>'Everyone',
                'is_default'=>1 
            ];
            $group = $this->group->where($group_data)->first();
            if(!$group){
                $group = $this->group->create($group_data);
            }
            $post->group_id = $group->id;
            $post->save();
        }
        else{
            $group = $this->groupExistsWithSameUsers(explode(',',$post->visibility), $post->space_id);
            if($group === false){
                $group = $this->group->create([
                    'name' => $this->createUniqueGroupName($post->space_id),
                    'space_id' => $post->space_id
                ]);
                $this->saveGroupUsers($group->id, explode(',',$post->visibility));
            }
            $post->group_id = $group->id;
            $post->save();
        }
    }

    protected function saveGroupUsers($group_id, $user_ids){
        foreach($user_ids as $user_id){
            if(checkUuidFormat($user_id) && $user_id){
                if(!GroupUser::where(['group_id'=>$group_id, 'user_id'=>$user_id])->exists()){
                    GroupUser::create([
                        'user_id' => $user_id,
                        'group_id' => $group_id,
                    ]);
                }
            }
        }
    }

    protected function createUniqueGroupName($space_id){
        $groups = Group::where('space_id', $space_id)->where('name', '!=', Static::DEFAULT_GROUP);
        if($groups->count() <= 0) { return 'group 1'; }
        $group_counter = $groups->count()+1;
        $group_name = '';
        do{
            $group_name = 'group ' . $group_counter;
            $group_counter++;
        } while(Group::where('space_id', $space_id)->where('name', $group_name)->exists());
        return $group_name;
    }

    protected function groupExistsWithSameUsers($user_ids, $space_id){
        $groups = Group::where('space_id', $space_id)->where('name', '!=', Static::DEFAULT_GROUP);
        if($groups->count() <= 0) { return false; }
        foreach($groups->get() as $group){
            if($group->groupUsers->count() == count($user_ids)){
                $user_exist_counter=0;
                foreach($user_ids as $user_id){
                    if(checkUuidFormat($user_id) && $user_id){
                        if(GroupUser::where(['group_id'=>$group->id, 'user_id'=>$user_id])->exists()){
                            $user_exist_counter++;
                        }
                    }
                }
                if(count($user_ids) == $user_exist_counter){
                    return $group;
                }
            }
        }
        return false;
    }

    Public function createDefaultGroup($space_id){
        $group_data = [
            'space_id'=>$space_id,
            'name'=>'Everyone',
            'is_default'=>1 
        ];
        $group = $this->group->where($group_data)->first();
        if(!$group){
            $this->group->create($group_data);
        }
    }
}
