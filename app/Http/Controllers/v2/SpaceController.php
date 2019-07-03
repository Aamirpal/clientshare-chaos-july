<?php

namespace App\Http\Controllers\v2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\{UserController, ManageShareController};
use App\Repositories\SpaceCategory\SpaceCategoryInterface;
use App\Repositories\Space\SpaceInterface;
use App\Repositories\SpaceUser\SpaceUser as SpaceUserRepository;
use Validator;

class SpaceController extends Controller {
    
    const MAX_TWITTER_LIMIT = 3;
    const MINIMUM_POSTS_LIMIT = 5;
    const ATLEAST_ONE = 1;

    protected $space_user;
    protected $space_category;
	protected $space;

	function __construct(SpaceInterface $space, SpaceUserRepository $space_user, SpaceCategoryInterface $space_category){
		$this->space = $space;
        $this->space_user = $space_user;
        $this->space_category = $space_category;
	}
    public function userProfile() {
        return apiResponseComposer(200, [], $this->user->getUserDetails(\Auth::user()->id));
    }

    public function show(Request $request)
    {
        $space_id = $this->space_user->validateOrRetriveSpaceId($request);
        $space_id ?: abort(404);
        (new UserController)->updateSpaceSessionData($space_id);
        (new ManageShareController)->setClientShareList();
        $data['is_logged_in_user_admin'] = $this->space_user->isAdmin($space_id, \Auth::user()->id);
        $data['space_twitter'] = $this->space->getTwitterHandler($space_id);
        $share_progress = ($this->shareStatus($space_id)['data']['progress']) ?? 0;
        $data['share_progress_percentage'] = (int) $share_progress;
        return view('v2-views.feed-page', $data);
    }

    public function communitySpaceInfo($space_id)
    {
    	return apiResponseComposer(200, [], $this->space->communitySpaceInfo($space_id));
    }

    public function shareCategorySheet()
    {
        
        $spaces = $this->space->allSpacesWithSelectedColoumn(['id','share_name','category_tags']);
        $data = [];
        foreach($spaces as $key => $space){
            foreach(json_decode($space->category_tags,true) as $key_cat => $category){
                $data[$key.'_'.$key_cat]['share_id']=$space->id;
                $data[$key.'_'.$key_cat]['share_name']=$space->share_name;
                $data[$key.'_'.$key_cat]['share_category']=$category;
            }
        }
        
        \Excel::create('testing', function($excel) use($data){
            $excel->sheet('General', function($sheet) use($data){
                $sheet->fromArray($data);
            });
        })->download('xls');
    }

    public function updateShareVersion(Request $request)
    {
        $data['version'] = $request->version?true:false;
        $this->space->update($data, $request->space_id);
        return redirect('/');
    }
    public function updateBusinessReviewVisibility(Request $request) {
        return $this->space->update(['is_business_review_enabled' => $request->show_business_review], $request->space_id);
    }
    
    public function isBusinessReviewEnabled(Request $request) {
        return apiResponseComposer(200, [], $this->space->isBusinessReviewEnabled($request->space_id));
    }

    public function saveTwitterHandler(Request $request)
    {   
        $validator = $this->validateTwitter($request);
        if($validator->fails()){
            return apiResponseComposer(400,['validation_messages'=>$validator->errors()],[]); 
        }
        $twitter_handles = $this->space->saveTwitterHandler($request->all());
        return apiResponseComposer(200, ['success_messages'=>'Twitter handler has been saved successfully.'], ['twitter_handles'=> $twitter_handles]);
    }

    private function validateTwitter(Request $request){
        $validator = Validator::make($request->all(), [
            'space_id' => 'required',
            'twitter_handles' => 'required',
            'user_id' => 'required'
        ]);
        if (!$validator->fails()) {
            $validator->after(function ($validator) use ($request) {
                if(count($request->twitter_handles) <= 0){
                    $validator->errors()->add('twitter_handles', 'Please add valid twitter handle e.g. @handle');
                }

                if(count($request->twitter_handles) > self::MAX_TWITTER_LIMIT){
                    $validator->errors()->add('twitter_handles', 'You can save maximum '.self::MAX_TWITTER_LIMIT.' twitter accounts.');
                }

                foreach($request->twitter_handles as $twitter_handle){
                    if(substr($twitter_handle, 0, 1) != '@' || trim(substr($twitter_handle, 1)) == ""){
                        $validator->errors()->add('twitter_handles', 'Please add valid twitter handle e.g. @handle');
                        break;
                    }
                }
            });
        }
        return $validator;
    }

    public function getTwitterHandler($space_id){
        $twitter_handles = $this->space->getTwitterHandler($space_id);
        return apiResponseComposer(200, [], ['twitter_handles'=> $twitter_handles]);
    }
    private function shareStatus($share_id) {
        $share_profile_status = $this->space->getShareProfileData($share_id);
        $space_admin = $this->space_user->getSpaceAdmin($share_id);
        $data['space_admin_data'] = [];
        if (sizeOfCustom($space_admin) > 0)
            $data['space_admin_data'] = $space_admin;

        if (!empty($share_profile_status)) {
            $data['logo'] = $data['background_image'] = false;
            $data['twitter_handles'] = $data['domain'] = false;
            $data['posts'] = $data['space_users'] = $data['space_admin'] = false;
            $data['progress'] = $task_completed = config('constants.COUNT_ZERO');

            if ($share_profile_status['space_admin_count'] > self::ATLEAST_ONE || $share_profile_status['invite_admin_status']) {
                $data['space_admin'] = true;
                $task_completed++;
            }

            if ($share_profile_status['seller_logo'] && $share_profile_status['seller_logo'] !== '""' && $share_profile_status['buyer_logo'] && $share_profile_status['buyer_logo'] !== '""') {
                $data['logo'] = true;
                $task_completed++;
            }

            if ($share_profile_status['background_image']) {
                $data['background_image'] = true;
                $task_completed++;
            }

            if (strlen($share_profile_status['twitter_handles']) > config('constants.COUNT_ZERO')) {
                $data['twitter_handles'] = true;
                $task_completed++;
            }
            if (!$share_profile_status['domain_restriction'] || $share_profile_status['domain_restriction'] && (isset($share_profile_status['metadata']['rule']) && sizeOfCustom($share_profile_status['metadata']['rule']) > 0)) {
                $data['domain'] = true;
                $task_completed++;
            }

            if ($share_profile_status['posts_count'] >= self::MINIMUM_POSTS_LIMIT) {
                $data['posts'] = true;
                $task_completed++;
            }
            $data['progress'] = number_format(config('constants.SHARE_PROFILE_COMPLETION_INDEX') * $task_completed);
            $data['posts_count'] = $share_profile_status['posts_count'];

            if ($share_profile_status['space_member_count'] >= self::ATLEAST_ONE)
                $data['space_users'] = true;


            return ['result' => true, 'data' => $data];
        }
        return ['result' => false, 'data' => ''];
    }

    public function shareProfileStatus(Request $request) {
        $request_data = $request->all();
        if (isset($request_data['space_id']) && $request_data['space_id']) {
            return $this->shareStatus($request_data['space_id']);
        }
        return ['result' => false, 'data' => ''];
    }

    public function updateTourStep(Request $request) {
        $share = \App\Models\Space::find($request->space_id);
        if ($share['share_profile_progress'] < $request->step) {
           $this->space->update(['share_profile_progress' => $request->step], $request->space_id);
        }
        $share_status = $this->shareStatus($request->space_id);
        if ($share_status['result'] && $share_status['data']['progress'] === config('constants.HUNDRED_PERCENT')) {
            return ['share_profile_completed' => true]; 
        } 
        return ['share_profile_completed' => false];
    }
    public function updateInviteAdminStatus(Request $request) {
        if (checkUuidFormat($request->space_id)) {
            return $this->space->update(['invite_admin_status' => $request->invite_admin_status], $request->space_id);
        }
    }

    function updateCategories(Request $request)
    {
        $business_review = $request->has('business_review') ? $request->business_review : 0;
        $space_id = $request->space_id;
        $this->space->update(['is_business_review_enabled' => $business_review], $space_id);
        $data = $request->except('business_review', 'space_id');
        if(count($data) <= 0){
            return 1;
        }
        return $this->space_category->renameCategories($data, $space_id);
    }

    function getCategories($space_id)
    {
        $data['is_business_review_enabled'] =  $this->space->isBusinessReviewEnabled($space_id)->is_business_review_enabled;
        $data['categories'] = $this->space_category->getSpaceCategoriesExceptBR($space_id);
        return apiResponseComposer(200, [], $data);
    }

}
