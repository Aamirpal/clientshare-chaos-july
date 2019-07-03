<?php

namespace App\Http\Controllers\v2;

use App\Repositories\Group\GroupInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    protected $group;

    public function __construct(GroupInterface $group) {
		$this->group = $group;
    }

    public function store(Request $request){
        $validator = $this->group->validateCreateGroup($request);
        
        if ($validator->fails()) return apiResponseComposer(400,['Validation_messages'=>$validator->errors()],[]); 
        $group = $this->group->createGroup($request);
        if($group) return apiResponseComposer(200,[],[['group'=>$group]]); 
        return apiResponseComposer(400,['technical_error'=>'Some technical error found. Please try after some time.'],[]);
    }

    public function update(Request $request) {
        $validator = $this->group->validateUpdateGroup($request);
        if ($validator->fails())
            return apiResponseComposer(400, ['Validation_messages' => $validator->errors()], []);
        return apiResponseComposer(200, [], ['group_updated' => $this->group->updateGroup($request)]);
    }

    public function destroy($group_id){
        if(!$this->group->isGroupExists($group_id)){
            return apiResponseComposer(400, ['error' => 'This group do not exists in our record.'], []);
        }
        if($this->group->deleteGroup($group_id)){
            return apiResponseComposer(200, ['success' => 'Group has been deleted successfully.'], []);
        }
        return apiResponseComposer(400, ['error' => 'Some technical error found. Please try after some time.'], []);
    }
}
