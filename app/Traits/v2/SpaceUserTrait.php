<?php

namespace App\Traits\v2;

use App\Helpers\Logger;
use App\Jobs\{
    CopyProfileImagesToAWS,
    PostProfileUpdation
};
use App\Http\Controllers\{
    UserController,
    ManageShareController
};

trait SpaceUserTrait {

    public function preUpdateUserProfile($request) {
        $this->forget_linkedin_session($request);
        (new Logger)->log(['action' => 'update profile logging', 'description' => 'update profile data logging', 'metadata' => $request->all()]);
    }

    public function postUpdateUserProfile($request) {
        (new UserController)->updateSpaceSessionData($request->space_id);
        (new Logger)->log(['action' => 'update profile', 'description' => 'update profile']);
        (new Logger)->mixPannelInitial(\Auth::user()->id, $request->space_id, Logger::MIXPANEL_TAG['update_profile']);
        dispatch(new PostProfileUpdation());
        dispatch(new CopyProfileImagesToAWS());
    }

    private function forget_linkedin_session($request) {
        $request->session()->forget(['linkedin_company', 'linkedin_sub_company',
            'linkedin_job_title', 'linkedin_link', 'linkedin_phoneno',
            'linkedin_bio', 'linkedin_company_status']);
    }

    private function getSpaceUser($space_id, $user_id)
    {
        $validator = \Validator::make(compact('space_id', 'user_id'),[
          'user_id'=>'required|uuid',
          'space_id'=>'required|uuid'
        ], [
          'uuid'=> trans('messages.validation.uuid_format')
        ]);
        
        if($validator->fails()){
          return apiResponse([], 400, ['errors'=>$validator->errors()]);
        }

        return apiResponse($this->space_user->getSpaceUser($space_id, $user_id));
    }

}
