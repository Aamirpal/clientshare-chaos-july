<?php

namespace App\Repositories\SpaceUser;

use App\Models\SpaceUser as SpaceUserModel;
use App\Models\Space as SpaceModel;
use App\Repositories\SpaceUser\SpaceUserInterface;

use App\Company;

use App\User;
use App\Components\AwsComponent;

class SpaceUser implements SpaceUserInterface
{
    const COMMUNITY_TILE_USER_LIST = 7;
    protected $space_user;

    public function __construct(SpaceUserModel $space_user)
    {
      $this->space_user = $space_user;
    }

    public function getSpaceUser($space_id, $user_id)
    {
        $community_members = $this->space_user
        ->select('user.first_name', 'user.last_name', 'user.email', 'user.profile_image', 'user.circular_profile_image', 'space_users.user_company_id', 'space_users.sub_company_id')
        ->selectRaw("space_users.metadata->'user_profile'->'user'->'contact'->>'linkedin_url' as linkedin_url")
        ->selectRaw("space_users.metadata->'user_profile'->'user'->'contact'->>'contact_number' as contact_number")
        ->selectRaw("space_users.metadata->'user_profile'->>'bio' as bio")
        ->selectRaw("space_users.metadata->'user_profile'->>'job_title' as job_title")
        ->join('users as user', 'user.id','=','space_users.user_id')
        ->with('user', 'share', 'company', 'subCompany')
        ->where('space_id', $space_id)
        ->where('user_id', $user_id)
        ->get();

        foreach ($community_members as $key => $community_member) {
            $community_members[$key]['profile_image'] = wrapUrl(composeUrl($community_member['circular_profile_image']??$community_member['profile_image']));
        }
        return $community_members;
    }

    public function getSpaceUsersId($space_id)
    {
        return $this->space_user->where('space_id', $space_id)
        ->select('user_id')
        ->active()
        ->pluck('user_id')
        ->toArray();
    }

    public function communityTile($space_id) 
    {
      return [
        'users_count'  => $this->getSpaceUserActiveCount($space_id),
        'users_preview_list' => $this->communityMemberProfileImage($space_id, $this::COMMUNITY_TILE_USER_LIST)
      ];
    }

    public function getSpaceUserActiveCount($space_id, $selection_method='count')
    {
      return $this->space_user->space($space_id)->with('User')->active()->$selection_method();
    }

    public function validateOrRetriveSpaceId($request_data)
    { 
        $space_id = isset($request_data->space_id) ?
        $this->validateSpaceId($request_data->space_id, \Auth::user()->id)
        :$this->getUserSpaceId(\Auth::user());
      return $space_id;
    }

    private function validateSpaceId($space_id, $user_id)
    {
        return $this->getAactiveOrInvitedSpaceUser($space_id, $user_id, 'first') ? $this->getAactiveOrInvitedSpaceUser($space_id, $user_id, 'first')->space_id : null;
    }

    private function getUserSpaceId($user)
    {
      return $this->userLastAccesedSpace($user)??$this->space_user->userSpaces($user->id, 'first')->space_id;
    }
    public function getOneSpaceUserInfo($share_id, $user_id) {
        return $this->space_user->where(['space_id' => $share_id, 'user_id'=> $user_id])->first();
    }
    public function updateUserDataInSpaceUser($space_id, $user_id, $userdata) {
        return $this->space_user->where('space_id', $space_id)->where('user_id',$user_id) ->update($userdata);
    }
    private function userLastAccesedSpace($user)
    {
      $last_active_space = json_decode($user->toArray()['active_space'], true);
      $space = isset($last_active_space['last_space']) ? $this->getAactiveOrInvitedSpaceUser($last_active_space['last_space'], $user->id, 'count'):'';
      return $space ? $last_active_space['last_space'] : null;
    }

    public function getAactiveOrInvitedSpaceUser($space_id,$user_id, $selection_method)
    {
      return $this->space_user->space($space_id)->mySpace($user_id)->activeOrInvited()->$selection_method();
    }
    
    public function userSpaces($user_id, $selection_method)
    {
      return $this->where('user_id', $user_id)
        ->active()->orderBy('created_at', 'desc')
        ->$selection_method();
    }

    public function communityMember($space_id, $company_id=null, $offset = 0, $limit = null, $search = null) 
    {
      
      $community_members = $this->communityMemberList($space_id, $company_id, $offset, $limit, $search);
      foreach ($community_members as $key => $community_member) {
        $community_members[$key]['profile_image'] = wrapUrl(composeUrl($community_member['circular_profile_image']??$community_member['profile_image']));
      }
      $community_count = $this->communityCount($space_id, $company_id, $search);

      return compact('community_members', 'community_count', 'offset', 'limit', 'search');
    }

    private function communityMemberList($space_id, $company_id, $offset, $limit, $search)
    {
      $limit = is_null($limit) ? config('constants.COMMUNITY_USERS_AJAX_LIMIT') : $limit;
      $query = $this->space_user
        ->select('user.first_name', 'user.last_name', 'user.email', 'user.profile_image', 'user.circular_profile_image', 'space_users.user_company_id', 'space_users.sub_company_id')
        ->selectRaw("space_users.metadata->'user_profile'->'user'->'contact'->>'linkedin_url' as linkedin_url")
        ->selectRaw("space_users.metadata->'user_profile'->'user'->'contact'->>'contact_number' as contact_number")
        ->selectRaw("space_users.metadata->'user_profile'->>'bio' as bio")
        ->selectRaw("space_users.metadata->'user_profile'->>'job_title' as job_title")
        ->join('users as user', 'user.id','=','space_users.user_id')
        ->orderBy('user.first_name')->orderBy('user.last_name')
        ->with('user', 'share', 'company', 'subCompany')
        ->active();
      
      $company_id ? $query->usersByCompany($space_id, $company_id) : '';
      
      if($search) {
        $query->where( function ($query) use ($search) {
          $query->where('usr.first_name', 'ilike', "%$search%")
            ->orWhere('usr.last_name', 'ilike', "%$search%")
            ->orWhereRaw("usr.first_name || ' ' || usr.last_name ilike '%$search%'");
        });
      }
      
      return $query->where('space_id', $space_id)->active()
        ->skip($offset)->take($limit)
        ->get()->toArray();
    }

    private function communityCount($space_id, $company_id, $search)
    {
      $query = $this->space_user->active();
      
      $company_id ? $query->usersByCompany($space_id, $company_id) : null;
      
      if($search) {
        $query->where( function ($query) use ($search) {
          $query->where('usr.first_name', 'ilike', "%$search%")
            ->orWhere('usr.last_name', 'ilike', "%$search%")
            ->orWhereRaw("usr.first_name || ' ' || usr.last_name ilike '%$search%'");
        });
      }      
      return $query->where('space_id', $space_id)->count();
    }

    public function communityMemberProfileImage($space_id, $limit) 
    {
      $space_members =  $this->space_user->select("user.profile_image", "user.profile_thumbnail", "user.circular_profile_image")
        ->join('users as user', 'user.id','=','space_users.user_id')
        ->orderBy('user.profile_image->file')
        ->active()
        ->where('space_id', $space_id)
        ->take($limit)
        ->get()->toArray();

      foreach ($space_members as $key => $member) {
        $space_members[$key]['profile_image_url'] = wrapUrl(composeUrl($member['circular_profile_image']??$member['profile_image']));
      }
      return $space_members;
    }
    public function getUserDetails($space_id, $user_id) {
        $user_details = $this->space_user->
            select(['user.first_name', 'user.last_name', 'user.email', 'user.contact',
                'user.active_space', 'user.profile_image', 'user.circular_profile_image', 'space_users.metadata',
                'space_users.user_company_id', 'space_users.sub_company_id'])
            ->join('users as user', 'user.id', '=', 'space_users.user_id')
            ->join('spaces as space', 'space.id', '=', 'space_users.space_id')
            ->where('space_users.user_id', $user_id)
            ->where('space_users.space_id', $space_id)
            ->with('subCompany','company')
            ->first();
        $meta = $user_details->metadata;
        $user_details->job_title = $user_details->bio = null;
        if ($meta['invitation_status'] == 'member') {
            $user_details->job_title = $meta['user_profile']['job_title'];
            $user_details->bio = $meta['user_profile']['bio'];
        }
        $user_details->circular_profile_image = wrapUrl(composeUrl($user_details->circular_profile_image, true));
        return $user_details;
    }
    public function updateSpaceUser($space_id, $user_id, $data) {
        $space_user = $this->getOneSpaceUserInfo($data['space_id'], $user_id);
        if (!sizeOfCustom($space_user))
            return 0;

        $default_metadata = [
            'invitation_status' => 'member',
            'invitation_code' => 1,
            'user_profile' => [],
            'job_title' => $data['job_title'],
            'bio' => $data['bio'],
            'company' => $data['company']
        ];
        if (!empty($data['check_user_is_new'])) {
            $metadata = $default_metadata;
        } else {
            $metadata = $space_user['metadata'] ?? $default_metadata;
        }
        unset($data['check_user_is_new'], $data['file']);

       $metadata['user_profile'] = $data;
        if (!empty($_FILES['file']['tmp_name'])) {
            $data['user']['profile_image'] = (new AwsComponent)->uploadImage($_FILES['file']);
        } else {
            $user_exist = User::getUserInfo($user_id, 'first');
            if ($user_exist->profile_image == '' || $user_exist->profile_image_url == '') {
                if (isset($data['linkedin_image']) && $data['linkedin_image'] != '')
                    $data['user']['profile_image'] = filePathUrlToJson($data['linkedin_image']);
            }
        }

        $metadata['user_profile']['bio'] = (!empty($metadata['user_profile']['bio'])) ? trim($metadata['user_profile']['bio']) : '';
        $this->updateUserDataInSpaceUser(
            $space_id, $user_id, ['metadata' => json_encode($metadata), 'user_company_id' => $data['company'], 'sub_company_id' => $this->getUserSubCompany($data)]
        );
        $data['user']['contact'] = json_encode($data['user']['contact']);

        if (isset($data['user']['profile_image'])) {
            $data['user']['profile_thumbnail'] = null;
            $data['user']['circular_profile_image'] = ($this->createCircularImage($data['user']['profile_image'], $user_id)) ?? null;
        }
        return User::updateUser($user_id, $data['user']);
    }

    private function getUserSubCompany($data) {
        if (isset($data['sub_company']) && $data['sub_company'] != '') {
            $sub_comp = Company::whereRaw("lower(company_name) = lower('" . str_replace("'", "", $data['sub_company']) . "')")->first();
            if (!sizeOfCustom($sub_comp)) {
                $sub_comp[0] = Company::create(['company_name' => $data['sub_company']]);
                $sub_company = $sub_comp[0]['id'];
            } else {
               $sub_company = $sub_comp['id'];
            }
        } else {
           $sub_company = config('constants.DUMMY_UUID')[0];
        }
        return $sub_company;
    }
    
    private function createCircularImage($user_profile_image, $user_id) 
    {
        $image_path = composeUrl($user_profile_image, true);
        if ($image_path && strpos($image_path, config('constants.LINKED_IN_URL')) !== false)
            return false;
        $file_name = 'circular_' . $user_id . '.png';
        $url = getCircleImage($image_path, $file_name, true, 'user_profile_thumbnail');
        if ($url != '') {
            return json_encode($url);
        }
    }

    public function searchSpaceUser($space_id, $search_key="")
    {
        $space_users = \DB::table('space_users as su')
                        ->select(\DB::raw("su.id, su.user_id, concat(u.first_name,' ',u.last_name) as full_name"))
            ->leftJoin('users as u', 'u.id', 'su.user_id')
                        ->where('space_id',$space_id)
                        ->where(function($query)use($search_key){
                            $query->orWhere('u.first_name', 'ilike', '%'.$search_key.'%')
                            ->orWhere('u.last_name', 'ilike', '%'.$search_key.'%');
                          })
                        ->where('su.user_id', '!=', \Auth::User()->id)  
                        ->where('su.user_status', '0')
                        ->whereNull('su.deleted_at')
                        ->whereRaw("su.metadata->>'user_profile' !=''")
                        ->orderBy('u.first_name')
                        ->get()
                        ->toArray();
        return $space_users;
    }
    public function getActiveSpaceUser($space_id, $user_id, $selection_method = 'get') {
        return $this->space_user->with('userRole')->space($space_id)->mySpace($user_id)->active()->$selection_method();
    }

    public function checkUserBuyerOrSeller($space_id, $user_id) {
        $space_user = $this->getActiveSpaceUser($space_id, $user_id);
        if (!sizeOfCustom($space_user))
            return '';
        $seller = SpaceModel::select('id')->where('id', $space_id)->where('company_seller_id', $space_user[0]['user_company_id'])->get();
        $buyer = SpaceModel::select('id')->where('id', $space_id)
                ->where('company_buyer_id', $space_user[0]['user_company_id'])->get();
        if (sizeOfCustom($seller))
            return 'seller';
        if (sizeOfCustom($buyer))
            return 'buyer';
        return '';
    }

    

    public function isAdmin($space_id, $user_id){
        $space_user = $this->space_user->with('userRole')->where(['space_id' => $space_id, 'user_id' => $user_id])->first();
        return isset($space_user->userRole->user_type_name) ? $space_user->userRole->user_type_name == 'admin' ? 1 : 0 : 0;
    }

    public function getSpaceUsers($space_id){
      return $space_users = $this->space_user->select('user_id', 'user_company_id')
        ->with(['company' => function($user){
            return $user->select('id', 'company_name');
        }, 'subCompany' => function($user){
            return $user->select('id', 'company_name');
        }, 'user' => function($user){
            return $user->select('id', 'first_name', 'last_name', 'profile_image', 'profile_thumbnail', 'circular_profile_image');
        }])
        ->where('space_id', $space_id)
        ->active()
        ->get()
        ->keyBy('user_id')
        ->toArray();
    }

    public function spaceUserExists($space_id, $user_id){
        return $this->space_user->where(compact('space_id','user_id'))->exists();
    }

    public function getSpaceAdmin($space_id){
      return $this->space_user->where('space_id',$space_id)->where('user_type_id',config('constants.USER_ROLE_ID'))
                  ->where('user_id', '!=', \Auth::user()->id)
                  ->whereNull('deleted_at')
                  ->with(array('User'=>function($query) {
                      $query->select('id', 'email');
                  }))
                  ->get()
                  ->toArray();
    }

}
