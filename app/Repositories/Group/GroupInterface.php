<?php

namespace App\Repositories\Group;

interface GroupInterface
{
    public function validateCreateGroup($request);
    public function createGroup($request);
    public function updateGroup($request);
    public function isGroupExists($group_id);
    public function deleteGroup($group_id);
    public function makeOldPostGroups($post);
    public function createDefaultGroup($space_id);
    public function find($group_id);
}