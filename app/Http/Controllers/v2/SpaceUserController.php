<?php

namespace App\Http\Controllers\v2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\SpaceUser\SpaceUserInterface;
use App\Repositories\Space\SpaceInterface;
use App\Http\Requests\SpaceUser\UpdateShareUserProfileRequest;
use App\Traits\v2\SpaceUserTrait;
use App\Http\Controllers\{
    UserController
};

class SpaceUserController extends Controller
{
    protected $space_user;
    protected $space;

    use SpaceUserTrait;

    public function __construct(SpaceUserInterface $space_user, SpaceInterface $space) {
        $this->space_user = $space_user;
        $this->space = $space;
 	}

	public function communityMemberTile($space_id) {
		return apiResponseComposer(200, [], $this->space_user->communityTile($space_id));
	}
    public function userProfile($space_id) {
        return apiResponseComposer(200, [], $this->space_user->getUserDetails($space_id,\Auth::user()->id));
    }

	public function communityView($space_id)
	{
        $data['is_logged_in_user_admin'] = $this->space_user->isAdmin($space_id, \Auth::user()->id);
        $data['space_twitter'] = $this->space->getTwitterHandler($space_id);
		(new UserController)->updateSpaceSessionData($space_id);
		return view('v2-views.community-page', $data);
    }

	public function communityList(Request $request, $space_id)
	{
		return apiResponseComposer(200, [], 
			$this->space_user->communityMember(
				$space_id,
				$request->company_id??null,
				$request->offset??null,
				$request->limit??null,
				$request->search??null
			)
		);
	}
    public function updateShareUser(UpdateShareUserProfileRequest $request) {
        $this->preUpdateUserProfile($request);
        $user_id = \Auth::user()->id;
        $profile_updated = $this->space_user->updateSpaceUser($request->space_id, $user_id, $request->all());
        if ($profile_updated) {
            $this->postUpdateUserProfile($request);
        }
        return back();
    }
    

    public function searchUser($space_id, $search_key="")
    {
        $output = [
            'users' => $this->space_user->searchSpaceUser($space_id, $search_key),
            'space_id' => $space_id
        ];
        return apiResponseComposer(200, [], $output);
    }

    public function getSpaceUsers($space_id)
    {
        return apiResponseComposer(200, [], $this->space_user->getSpaceUsers($space_id));
    }

    public function spaceUserInformation($space_id, $user_id)
    {
        return $this->getSpaceUser($space_id, $user_id);
    }
}
