<?php

namespace App;
use DB;
use App\Traits\ModelEventLogger;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetClientSharePassword as ResetPasswordNotification;

class User extends Authenticatable {
    use Notifiable;
    use ModelEventLogger;
    
    protected $keyType = 'string';
    protected $appends = ['fullname', 'profile_image_url', 'profile_image_initial', 'circular_image_url'];
    protected $fillable = [
        'name', 'email', 'password','user_type_id','first_name','last_name', 'profile_image_url_old', 'profile_image', 'job_title', 'contact','social_accounts', 'share_setup_steps', 'profile_thumbnail'
    ];

    protected $casts = [
        'contact' => 'json',
        'settings' => 'json',
        'profile_image' => 'json',
        'profile_thumbnail' => 'json'
    ];

    protected $hidden = [
        'password', 'remember_token','profile_image_initial'
    ];
     
    const MAX_REACTIONS = 7;
    const MIN_REACTIONS = 2;
    const REACTIONS_ABOVE_THOUSAND = 1000;

    public static function weeklyEmailUsers($date, $limit = 100, $offset = 0){
        return User::whereHas('SpaceUser', function($space_user_query) {
            $space_user_query->where('user_status', '=', 0)
                ->where('metadata->invitation_code', '=', 1);
            })
        ->with(['SpaceUser' => function($space_user_sub_query) use($date) {
            $space_user_sub_query->where('user_status', '=', 0)
            ->whereRaw("metadata->>'user_profile' !=''")
            ->with(['Share.Posts' => function($share_post_query) use($date) {
                $share_post_query->select(['id', 'user_id', 'space_id', 'post_description', 'post_subject', 'visibility'])->where('created_at', '<=', $date['to'])
                    ->where('created_at', '>=', $date['from']);
            }]);
        }])->skip($offset)->take($limit)
       ->get()
       ->mapWithKeys(function($user) {
            return [$user['id'] => $user->toArray()];
       });
    }

    public function getCircularImageUrlAttribute() {
        if(!$this->profile_image && !$this->circular_profile_image) return '';
        $image = $this->circular_profile_image ?? $this->profile_image;
        if(is_array($image)){
            return wrapUrl(composeFilePath($image));
        }
        return wrapUrl(composeFilePath(json_decode($image, true)));
    }

    public function getNonAWSProfileImages(){
        return $this->whereRaw("profile_image->>'path' ilike '%http%'")->get();
    }

    public static function taggedUsers($user_list){
        return static::selectRaw('id, initcap(first_name) ||\' \'|| initcap(last_name) as fullname, SPLIT_PART(email,\'@\',1) as username')->whereRaw("email like ANY (array['".$user_list."%'])")->get()->toArray();
    }

    public static function updateLastAccessedSpace($space_id, $user_id){
        $space_user = SpaceUser::select('metadata')->where('space_id', $space_id)->where('user_id', $user_id)->first();
        if(!isset($space_user['metadata']['invitation_code']) && $space_user['metadata']['invitation_code'] != 1)
            return false;
        
        return static::where('id', $user_id)->update(['active_space'=>json_encode(['last_space' => $space_id])]);
    }

    public static function updateUser($user_id, $updated_data){
        return static::where('id', $user_id)->update($updated_data);
    } 

    public function getProfileImageUrlAttribute($profile_image_path) {
        if(!$this->profile_image) return '';
        return wrapUrl(composeFilePath($this->profile_thumbnail??$this->profile_image));
    }

    public function getProfileImageInitialAttribute($profile_image_path) {
        return ($this->profile_image_url == "" || empty($this->profile_image_url)) ? strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1)) : composeEmailURL(filePathJsonToUrl($this->profile_image)) ;
    }

    public function getFullNameAttribute() {
        return ucfirst($this->first_name) . " " . ucfirst($this->last_name);
    }

    public function getFirstnameAttribute($value) {
       return ucfirst($value);
    }

    public function getLastnameAttribute($value) {
       return ucfirst($value);
    }

    public function getIdAttribute($value) {
       return (string) $value;
    }

    public static function boot() {
        parent::boot();        
        static::creating(function($user) {
           $user->email = strtolower($user->email);          
        });
        static::updating(function($user) {
           $user->email = strtolower($user->email);      
        });
    }

    public function comments() {
        return $this->hasMany('App\Comment');
    }

    public function posts() {
        return $this->hasMany('App\Post');
    }

    public function endorse() {
        return $this->belongsTo('App\EndorsePost');
    }

    /**/
    public function SpaceUser() {
        return $this->hasMany('App\SpaceUser');
    }

    /**/
    public function Space() {
        return $this->hasMany('App\Space');
    }
    
    public function sendPasswordResetNotification($token) {
        return $this->notify(new ResetPasswordNotification($token));
    }

    public static function getUserIdFromEmail($email) {
        return $user_id = static::where("email", 'ilike',$email)->first();
    }

    public function userType() {
        return $this->hasOne('App\UserType'); 
    }

    public static function getApprovedUsers($space_id) {
        return static::whereHas('SpaceUser', function($q)use($space_id) {
                $q->where('space_id', $space_id);
            })->get()->pluck('full_name', 'id');
    }
    public static function getUserInfo($user_id, $selection_method='get') {
        return static::where('id', $user_id)->$selection_method();
    }
    public static function getUserSettings($id) {
        return static::where('id', $id)->pluck('settings');
    }
    public static function getFirstLastNameOfUser($id) {
        return static::where('id', $id)->get(['first_name', 'last_name'])->first();
    }
    public static function getFirstLastNameOfUsers() {
        return static::select(['id', 'first_name', 'last_name'])->get()
            ->mapWithKeys(function ($user) {
                return [$user['id'] => $user->toArray()];
            });
    }
    public static function getUserByEmail($email) {
        return static::where('email', strtolower($email))->first();
    }
    public static function updateUserShowTour($user_id) {
        return static::where('id', $user_id)->update(['show_tour' => false]);
    }
    public static function executeSearch($userId,$spaceId,$keywords,$coun) {
    return DB::table('users as u')
    ->select(DB::raw("p.id, p.post_subject, p.space_id, p.post_description,u.profile_image as userprofileImage"))            
    ->Join('posts as p','u.id','p.user_id')
    ->where('p.space_id', '=',$spaceId)
    ->where(function($q)use($keywords, $userId){
      $q->orWhere('p.visibility', 'ilike','%'.$userId.'%')
      ->orWhere('p.visibility', 'ilike','%all%');
    })
    ->where('p.deleted_at', '=', null)   
    ->where(function($q)use($keywords){
      $q->orWhere('u.first_name', 'ilike','%'.$keywords.'%')
      ->orWhere('u.last_name', 'ilike','%'.$keywords.'%');
    })   
    ->limit($coun)   
    ->get()->toArray(); 
    }

    public static function searchSpaceByUser($first_name = null, $last_name = null, $email = null) {
        $where_condition =  '';
        if($first_name != '' && $last_name != '' && $email == ''){
            $where_condition = "u.first_name ilike '%$first_name%' AND u.last_name ilike '%$last_name%'";
        }elseif($first_name == '' && $last_name == '' && $email != ''){
            $where_condition = "u.email = '$email'";
        }else{
            $where_condition = "(u.first_name ilike '%$first_name%' AND u.last_name ilike '%$last_name%') OR (u.email = '$email')";
        }

        $shares = DB::table('space_users as su')
        ->select(DB::raw("su.id, su.user_type_id, u.id as user_id, u.first_name as first_name, u.last_name as last_name, u.email, s.share_name, c.company_name, s.user_id as admin_id, s.id as space_id, su.metadata,
            (case when su.user_status=1 then 0 when su.deleted_at is not null then 0 else 1 end) as user_status, (case when s.deleted_at is null then 'Active' else 'Deleted' end) as share_status"))
        ->leftJoin('users as u','u.id','su.user_id')
        ->leftJoin('spaces as s','su.space_id','s.id')
        ->leftJoin('companies as c', 'su.user_company_id', 'c.id')
        ->whereRaw($where_condition)
        ->get()->toArray();
        return arrayValueToKey(objectToArray($shares), 'id');
    }

    public function updateProfileImage($id, $full_url){
        return $this->where('id', $id)->update(['profile_image'=>filePathUrlToJson($full_url)]);
    }

    public function getImageOrInitialsEmail(){
        return empty($this->circular_profile_image) ? strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1)) : filePathJsonToUrl($this->circular_profile_image);
    }
    public function getProfileImages($user_id){
       return static::whereIn('id',$user_id)->select(['id', 'profile_image','circular_profile_image','first_name','last_name'])->get()->toArray();
        
    }

    public function getPeopleReacted($user_ids) {
        $total_users = count($user_ids);
        $output = [];
        if ($total_users < self::MIN_REACTIONS) {
            return $output;
        }

        $ids_to_show = array_slice($user_ids, 0, ($total_users <=  self::MAX_REACTIONS ? self::MAX_REACTIONS :self::MAX_REACTIONS - 1));       
        $users = $this->select(['id', 'circular_profile_image', 'first_name', 'last_name'])->whereIn('id', $ids_to_show)->get();

        foreach ($ids_to_show as $user_id) {
            foreach ($users as $user) {
                if ($user_id == $user->id) {
                    $output[$user->id] = $user->getImageOrInitialsEmail();
                }
            }
        }

        if ($total_users > self::MAX_REACTIONS) {
            $rest_count = ( $total_users - self::MAX_REACTIONS + 1);
            $output['rest_count'] = ($rest_count < self::REACTIONS_ABOVE_THOUSAND) ? "+" . $rest_count : $rest_count . "+";
        }
        return $output;
    }

    public function updateActiveSpace($space_id){
        $this->active_space = json_encode(['last_space' => $space_id]);
        return $this->save();
    } 

}
