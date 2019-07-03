<?php

namespace App;

use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;

class SpaceUser extends Model {
    
    protected $appends = ['company_id'];
    protected $keyType = 'string';
    const AUTO_INVITE_CANCEL = 28;
    const ROLES = ['buyer'=>'buyer', 'seller'=>'seller'];
    const STATUS = [
      'invitation'=>[
        'message'=>[
          'canceled'=>'Canceled'
        ],
        'code'=>[
          'canceled'=>'-1'
        ],
        'log_message'=>[
          'canceled'=>'cancel invitation'
        ]
      ]
    ];
    protected $casts = [
        'metadata' => 'json'
    ];

    protected $fillable = [
        'id','space_id','user_id','metadata', 'user_type_id','created_by','user_company_id','sub_company_id', 'doj', 'tag_user_alert'
    ];



    public function userListing($space_id) {
      return $this->select('id', 'user_id')->with(['User'=>function($user){
        $user->select('id', 'first_name', 'last_name');
      }])->where('space_id', $space_id)->active()->get();

    }

    public static function isAdmin($space_id, $user_id){
      $user = static::select('user_type_id')
        ->where('space_id', $space_id)
        ->where('user_id', $user_id)
        ->where('user_type_id', 2)
        ->first();
      return $user['user_type_id']??0;
    }
    
    public function getCompanyIdAttribute(){
      return in_array($this->sub_company_id, config('constants.DUMMY_UUID')) ? $this->user_company_id : $this->sub_company_id;
    }

    public static function postUsers($space_id, $post_owner_id){
      return static::space($space_id)
        ->with('User')
        ->where('user_id', '!=', $post_owner_id)
        ->active()->get()->toArray();
    }

    public static function incompleteProfileUsers($selection_method='get'){
      return static::whereRaw("metadata->'user_profile' is not null")
      ->whereRaw("length(metadata->'user_profile'->>'company') = 0")->$selection_method();
    }

    public static function cancelInvitation(){
      return DB::select("
        UPDATE space_users set 
          metadata = jsonb_set(jsonb_set(metadata::jsonb , '{invitation_code}', '-1'), '{invitation_status}', '\"Canceled\"')
        where metadata->>'invitation_code' = '0'
        and now() - created_at > '".static::AUTO_INVITE_CANCEL." days';
      ");
    }

    public static function verifyRegistration($verification_data){
      $user = SpaceUser::where('space_id', $verification_data['shareid'])
        ->where('user_id', User::where('email', $verification_data['email'])->first()['id'])->first();
      return ((string)$user['metadata']['registration_code'] ?? 0) == (trim($verification_data['verify_code'])??0);
    }

    public static function updateCompanyId($updated_data){
      DB::select('
        UPDATE space_users set user_company_id = \''.$updated_data['new_id'].'\', 
           metadata = replace(metadata::text, \'"company":"'.$updated_data['previous_id'].'"\'
          , \'"company":"'.$updated_data['new_id'].'"\')::json 
        where space_id =\''. $updated_data['space_id'].'\'
        and (user_company_id = \''.$updated_data['previous_id'].'\'
        or metadata::text ilike \'%'.$updated_data['previous_id'].'%\')
      ');
    }

    

    public static function checkTagAlertSetting($space_id, $tagged_users, $tag_setting, $tag_all){
      $users = static::select('user_id')
          ->where('space_id', $space_id)
          ->active();

      if(!$tag_all) $users->whereIn('user_id', $tagged_users);
      
      return $users->where('tag_user_alert', $tag_setting)
      ->get()->toArray();
    }

    public static function searchCommunityMember($space_id, $search_keyword){
      return static::with('User', 'Share')
        ->whereHas('User', function($q) use($search_keyword) {
        $q->where('registration_status', 1)
          ->where('first_name', 'ilike', "%$search_keyword%")
          ->orWhere('last_name', 'ilike', "%$search_keyword%")
          ->orWhere('metadata->user_profile->job_title', 'ilike', "%$search_keyword%");
        })
        ->where('space_id', $space_id)
        ->where('metadata->invitation_code', '1')
        ->active()->get()->toArray();
    }

    public static function feedbackOpenIntimation($space_id){
      return static::with('Share')
      ->whereHas('Share', function($q) use($space_id){
        $q->whereRaw("date_part('month',feedback_status_to_date) = date_part('month',now())")
        ->whereRaw("date_part('year',feedback_status_to_date) = date_part('year',now())")
        ->where('feedback_status', true);
        if($space_id) {
          $q->where('space_id', $space_id);
        }
      })->doesntHave('Feedback', 'and', function($q){
          $q->whereRaw('space_users.space_id = feedback.space_id')
          ->whereRaw("date_part('month',created_at) = date_part('month',now())")
          ->whereRaw("date_part('year',created_at) = date_part('year',now())");
        })
      ->active()
      ->get();
    }

    public static function userShares($user_id){
      return static::with('Share')
        ->where('user_id', $user_id)
        ->active()
        ->get()->toArray();
    }

    public static function spaceUserExceptSender($space_id, $sender_email, $role, $selector_method){
      return Static::with('user')
      ->whereHas('user', function($query)use($sender_email){
          $query->where('email', '!=', $sender_email );
      })
      ->space($space_id)
      ->active()
      ->userByType($role)
      ->$selector_method();
    }

    public static function updateByUserSpace($user_id, $space_id, $updated_data){
      return static::mySpace($user_id)->space($space_id)->update($updated_data);
    }

    public static function communityMember($space_id, $company_id=null, $offset = 0, $limit = null, $search = null) {
      $limit = is_null($limit) ? config('constants.COMMUNITY_USERS_AJAX_LIMIT') : $limit;
      $query = static::join('users as usr', 'usr.id','=','space_users.user_id')
        ->orderBy('usr.first_name')
        ->orderBy('usr.last_name')
        ->with('User', 'Share')
      ->active()
      ->with('sub_comp');
      
      if($company_id)
          $query->usersByCompany($space_id, $company_id);
      if($search)
          $query->where( function ($query) use ($search) {
              $query->where('usr.first_name', 'ilike', "%$search%")
                      ->orWhere('usr.last_name', 'ilike', "%$search%")
                      ->orWhereRaw("usr.first_name || ' ' || usr.last_name ilike '%$search%'");
          });
      
      return $query->where('space_id', $space_id)
              ->active()
              ->skip($offset)
              ->take($limit)
              ->get()
              ->toArray();
    }

    public static function communityMemberProfileImage($space_id, $limit = 5 ) {

      $space_members =  static::select("user.profile_image", "user.profile_thumbnail")
          ->join('users as user', 'user.id','=','space_users.user_id')
          ->orderBy('user.profile_image->file')
          ->active()
          ->where('space_id', $space_id)
          ->take($limit)
          ->get()->toArray();

       foreach ($space_members as $key => $member) {
           $space_members[$key]['profile_image_url'] = wrapUrl(composeUrl(json_decode($member['profile_image'], true)));
       }
       return $space_members;
    }

    public function scopeUsersByCompany($query, $space_id, $company_id){
      return $query->where('user_company_id', $company_id)->where('space_id', $space_id);
    }

    public static function spaceBuyers($space_id, $selection_method, $except_user=null){
      $query = static::active();
      if($except_user)
        $query->where('user_id','!=' ,$except_user);
      return $query->buyers($space_id)->$selection_method();
    }

    public static function UserSpaces($user_id, $selection_method){
      return static::where('user_id', $user_id)->active()->$selection_method();
    }

    public static function spaceUsers($space, $role){
      return static::space($space->id)->with('User')->userByType($role)->active()->get();
    }
    
    public function Feedback(){
        return $this->hasMany('App\Feedback', 'user_id', 'user_id');
    }

    
    public function scopeBuyers($query, $space_id){
      return $query->where('user_company_id', Space::find($space_id)->company_buyer_id)->where('space_id', $space_id);
    }

    
    public function scopeUserByType($query, $type_name){
      return $query->where('user_type_id', UserType::userTypeIdByName($type_name));
    }

    
    public function scopeActive($query){
      return $query->where('user_status', '0')
        ->whereNull('deleted_at')
        ->whereRaw("metadata->>'user_profile' !=''");
    }
    
    
    public function scopeMySpace($query, $user_id) {
      return $query->where('user_id', $user_id);
    }

    
    public function scopeSpace($query, $space_id){
      return $query->where('space_id', $space_id);
    }


    public function getCreatedAtAttribute($value) {
        if(!$value) return $value;
        return Carbon::parse($value)->timezone(\Auth::user()->timezone??'Europe/London');
    }
    
    public function getUpdatedAtAttribute($value) {        
        if(!$value) return $value;
        return Carbon::parse($value)->timezone(\Auth::user()->timezone??'Europe/London');
    }


    
    public function User(){
        return $this->belongsTo("App\User", "user_id","id"); 
    }
    
    
    public function Share(){
        return $this->belongsTo("App\Space", "space_id"); 
    }

    public function getIdAttribute($value) {
        return (string) $value;
    }

    public function getUserIdAttribute($value) {
        return (string) $value;
    }

    public function getSpaceIdAttribute($value) {
        return (string) $value;
    }

    public function user_role(){
        return $this->belongsTo("App\UserType", "user_type_id"); 
    }

    public function InvitedBy(){
        return $this->belongsTo("App\User", "created_by"); 
    }

    public function posts(){
     return $this->belongsTo("App\Post", 'user_id', 'user_id');    
    }
    
    public function comment(){
        return $this->belongsTo("App\Comment", "user_id","user_id"); 
    }
    public function sub_comp(){        
        return $this->belongsTo("App\Company", "sub_company_id","id");       
    }

    public static function getSpaceSubCompanyIdList($space_id,$comp){
      return static::where('space_id',$space_id)
        ->whereNotNull('sub_company_id')
        ->with(array('sub_comp'=>function($query) use($comp){
            $query->where('company_name', 'ilike','%'.$comp.'%');
            $query->select('id','company_name');
        }))
        ->groupBy('sub_company_id')->get(['sub_company_id'])->toArray();
    }

    public static function getSpaceUsersInfo($space_id){
      return $space_user_info = static::where('space_id',$space_id)
        ->with(array('User'=>function($query) {
           $query->select('id','email','first_name','last_name','active_space');
        }))->with(array('Share'=>function($query) {
           $query->select('id','share_name','seller_processed_logo','buyer_processed_logo');
        }))->where('metadata->invitation_code','1')
        ->get()->toArray();
    }

    public static function getSpaceAdminInfo($space_id, $trashed=null){
      return $space_admin_info = static::where('space_id',$space_id)->where('user_type_id','2')
        ->with(array('User'=>function($query) {
            $query->select('id','email','first_name','last_name','active_space');
        }))
        ->get()->toArray();
    }

    public static function getShareIfUserHaveAnyShare($user_id){        
      return $space = static::where('user_id',$user_id)       
        ->orderBy('created_at','desc')      
        ->where('user_status', '0')     
        ->whereNull('deleted_at')       
        ->whereRaw("metadata->>'user_profile' !=''")        
        ->first();      
            
    }

    public static function getSpaceActiveUsersInfo($space_id,$admin_id=null){
      return $space_user_info = static::where('space_id',$space_id)
        ->where('user_id','!=' ,$admin_id)
        ->whereRaw("metadata->>'user_profile' !=''")
        ->where('metadata->invitation_code','1')
        ->whereNull('deleted_at')
        ->where('user_status','0')
        ->get()->toArray();
    }
    public static function getSpaceUserInfo($share_id,$user_id, $selection_method='get', $flag=false) {
      $query = static::where('space_id', $share_id)
        ->where('user_id', $user_id);
        if($flag)
        $query->whereRaw("metadata->>'user_profile' !=''");

        return $query->$selection_method();
    }
    public static function getOneSpaceUserInfo($share_id,$user_id) {
      return static::where('space_id', $share_id)->where('user_id',$user_id)->first();
    }
    public static function getFirstSpaceInfo($share_id) {
      return static::where('space_id', $share_id)->first();
    }
    public static function getSpaceInfo($share_id) {
      return static::where('space_id', $share_id)->get();
    }
    public static function getUserInfo($user_id) {
      return static::where('user_id', $user_id)->get();
    }
    public static function getUserProfileInfoInDescOrder($user_id) {
      return static::where('user_id', $user_id)->where('user_status', '0')->orderBy('created_at', 'desc')->orderByRaw("metadata->>'user_profile'")->first();
    }
    public static function getInactiveUserSpaceInfo($user_id,$share_id) {
      return static::where('user_id', Auth::user()->id)->where('space_id', $share_id)->where('user_status', '0')->orderBy('created_at', 'asc')->first();
    }
    public static function getSpaceUserRole($share_id,$user_id) {
      return static::with('user_role')->where('user_id', $user_id)
        ->where('space_id', $share_id)->with('sub_comp')->get();
    }
    public static function getPendingInvitations($space_id) {
      return static::join('users as usr', 'usr.id','=','space_users.user_id')
        ->orderBy('usr.first_name')
        ->orderBy('usr.last_name')
        ->select('space_users.*')
        ->whereRaw("metadata->>'invitation_code' = '0'")
        ->whereNull('deleted_at')
        ->where('space_id', $space_id)->with(['User'=>function($q){
          $q->select('id','email','first_name','last_name','profile_image');
        }])
        ->with(['InvitedBy'=>function($invited_by){
          $invited_by->select('id','first_name','last_name');
        }])->paginate(20);
    }

     public static function getPendingInvitationsCount($space_id) {
      return static::whereRaw("metadata->>'invitation_code' = '0'")
        ->where('space_id', $space_id)
        ->whereNull('deleted_at')
        ->count();
    }

    public static function getSpaceMembers($space_id) {
      return static::join('users as usr', 'usr.id','=','space_users.user_id')
        ->orderBy('usr.first_name')
        ->orderBy('usr.last_name')
        ->orderBy('usr.email')
        ->select('*', 'space_users.user_type_id as user_role_id')
        ->with('User','Share')
        ->whereHas('User',function($q){
          $q->where('registration_status',1);
        })->where('user_status','0')
        ->where('space_id', $space_id)
        ->where('metadata->invitation_code','1')
        ->with(array('sub_comp'=>function($query){
          $query->select('id','company_name');
        }))
        ->paginate(20);
    }
    public static function pendingInvites() {
      return static::whereRaw("metadata->>'invitation_code' = '0'")
        ->whereHas('Share',function($query) {
          $query->whereNull('deleted_at')->withTrashed();
        })
        ->with('Share')
        ->whereHas('User')
        ->with(['User' => function($query) {
          $query->select('id', 'email', 'first_name', 'last_name', 'profile_image');
        }])
        ->with(['InvitedBy' => function($query) {
          $query->select('id', 'first_name', 'last_name', 'email');
        }])
        ->get(['id', 'space_id', 'user_id', 'metadata', 'created_at', 'created_by']);
      }
    public static function getActiveSpaceUser($space_id,$user_id, $selection_method='get')  {
      return static::with('user_role')->space($space_id)->mySpace($user_id)->active()->$selection_method();
    }

    public function getActiveOrPendingSpaceUser($space_id, $user_id, $selection_method='count')  {
      return static::with('user_role')
        ->space($space_id)
        ->mySpace($user_id)
        ->whereRaw("metadata->>'invitation_code' in ('0', '1')")
        ->whereNull('deleted_at')
        ->$selection_method();
    }

    public static function getSpaceUserActiveCount($space_id, $selection_method='count'){
      return static::space($space_id)->with('User')->active()->$selection_method();
    }

    public static function getSpaceUserActive($space_id, $selection_method='count'){
      return static::space($space_id)
        ->with('User')
        ->join('users', 'space_users.user_id', '=', 'users.id')
        ->orderBy('users.first_name')
        ->active()->$selection_method();
    }

    public static function getSpaceUserMetaDate($space_id){
      return static::where('id', $space_id)->first()->metadata;
    }
    public static function updateUserDataInSpaceUser($space_id,$user_id,$userdata){
      return static::where('space_id', $space_id)->where('user_id',$user_id) ->update($userdata);
    }
    public static function selectUserIdFromSpaceUser($space_id){
      return static::select('user_id')->where('space_id', $space_id)->where('user_status', '0')->where('metadata->invitation_code', '1')->get()->toArray();
    }
    public static function getSpaceUsers($space_id){
      return static::with('User', 'Share')->where('space_id', $space_id)->get();
    }
    public static function getSpaceUsersInvited($space_id){
      return static::with('User', 'Share')->where('user_status', '0')->where('space_id', $space_id)
      ->where('metadata->invitation_code', '1')->get()->toArray();
    }
    public static function spaceUsersWithSubCompany($space_id){
      return static::with('User', 'Share', 'sub_comp')
        ->where('space_id', $space_id)
        ->active()
        ->get()->toArray();
    }
    public static function userProfileNotNull($user_id){
      return static::where('user_id', $user_id)
        ->active()->orderBy('updated_at', 'desc')->first();
    }
    public static function spaceUserLog($space_id,$start_date,$end_date){
      return DB::select("SELECT * from (
      SELECT distinct on (\"Email Address\") * from (
        SELECT member.first_name||' '||member.last_name as \"Sent To\", log.created_at as \"Date Invited\", 
        member.email as \"Email Address\", 
        invitee.first_name||' '||invitee.last_name as \"Sent By\", comp.company_name as \"Company\",
        sender.metadata::text as \"Senders Job Title\"
        from space_users su
        inner join activity_logs log on log.metadata->>'invited_to' = su.user_id::text and log.space_id = '".$space_id."'
        inner join users member on member.id::text = log.metadata->>'invited_to' or su.user_id = member.id
        inner join users invitee on invitee.id::text = log.metadata->>'invited_by'
        inner join space_users sender on sender.user_id = log.user_id and sender.space_id = '".$space_id."'
        left join companies comp on CASE WHEN sender.sub_company_id::text ilike '00000000%' THEN sender.user_company_id else sender.sub_company_id END = comp.id 
        where su.space_id = '".$space_id."'
        and su.metadata->'user_profile' is null
        and su.metadata->>'invitation_code' = '0'
        and log.created_at between '".$start_date."' and  '".$end_date."'
        order by log.created_at desc
      ) as distinct_result) as final_result order by \"Date Invited\";"
    );
    }
    public static function shareMembersLog($space_id,$start_date,$end_date){
      return DB::select(
      "SELECT member.first_name as \"First Name\", member.last_name as \"Last Name\",
        su.metadata::text as \"Job Title\", comp.company_name as \"Company\", member.email as \"Email Address\", 
        case when max(su.doj) is null then to_date(max(to_char(su.created_at, 'dd/mm/yyyy')),'dd/mm/yyyy') else to_date(max(to_char(su.doj, 'dd/mm/yyyy')),'dd/mm/yyyy') end as \"Date Joined\", 
        to_char(min(su.created_at),'dd/mm/yyyy') as \"Date Invited\", 
        max(log.created_at||'|'||invitee.email) as \"Invited By\"
      from space_users  su 
      inner join companies comp on CASE WHEN su.sub_company_id::text ilike '00000000%' THEN su.user_company_id else su.sub_company_id END = comp.id
      left join activity_logs log on log.metadata->>'invited_to' = su.user_id::text and log.space_id = '".$space_id."'
      inner join users member on member.id::text = log.metadata->>'invited_to' or su.user_id = member.id
      left join users invitee on invitee.id::text = log.metadata->>'invited_by'
      where su.space_id = '".$space_id."'
      and su.metadata->'user_profile' is not null
      and su.deleted_at is null
      and su.metadata->>'invitation_code' = '1'
      and su.created_at between  '".$start_date."' and  '".$end_date."'
      group by member.first_name, member.last_name, member.email,su.metadata::text,comp.company_name
      order by \"Date Joined\";"
    );
    }
    public static function newMembers($space_id,$start_date,$end_date){
      return DB::select(
      "SELECT to_char(created_at, 'YYYY-MM'), 'new_users'::text as tag, count(*) from space_users 
      where metadata->>'user_profile' is not null
      and space_id = '".$space_id."'
      and created_at between  '".$start_date."' and  '".$end_date."'
      and metadata->>'invitation_code' = '1'
      group by to_char(created_at, 'YYYY-MM');"
    );    
    }
    public static function sellerAndBuyerMembers($space_id,$start_date,$end_date){
      return DB::select(
      "SELECT
        to_char(doj, 'YYYY-MM'),
        case when user_company_id = ( select company_buyer_id from spaces where id = '".$space_id."' )
          then 'Buyer'
        when user_company_id = ( select company_seller_id from spaces where id = '".$space_id."')
          then 'Seller'
        end as tag, count(*)
      from space_users
      where space_id = '".$space_id."'
      and user_company_id != '00000000-0000-0000-0000-000000000000'
      and metadata->>'invitation_code' = '1'
      and doj between  '".$start_date."' and  '".$end_date."'
      group by tag,to_char(doj, 'YYYY-MM')
      order by to_char(doj, 'YYYY-MM');"
    );
    }
    public static function membersDeleted($space_id,$start_date,$end_date){
      return DB::select(
      "SELECT
        to_char(deleted_at, 'YYYY-MM'),
        case when user_company_id = ( select company_buyer_id from spaces where id = '".$space_id."' )
          then 'Buyer'
        when user_company_id = ( select company_seller_id from spaces where id = '".$space_id."')
          then 'Seller'
        end as tag, count(*)
      from space_users
      where space_id = '".$space_id."'
      and user_company_id != '00000000-0000-0000-0000-000000000000'
      and deleted_at is not null
      and metadata->>'invitation_code' = '1'
      and deleted_at between  '".$start_date."' and  '".$end_date."'
      group by tag,to_char(deleted_at, 'YYYY-MM')
      order by to_char(deleted_at, 'YYYY-MM');"
    );
    }
    public static function spaceUserCreated($space_id,$user_id,$user_type_id,$mail_data){
      return static::create(['space_id' => $space_id, 'user_id' => $user_id, 'user_type_id' => $user_type_id, 'metadata' => ["invitation_status" => "Pending from User", "invitation_code" => 0, "mail_data" => $mail_data]]);
    }

    public static function userSaveInShare($data,$space_id){     
      $user = SpaceUser::getSpaceUserInfo($space_id,$data['user_id'], 'first');
      if(empty($user)){        
        $data['space_id'] = $space_id; 
         return static::create($data); 
      }
    }

    public static function getAllSharePendingInvitations($supplier_id, $buyer_id, $filters) {
      $order_by = $offset_value ='';
      $where = 'WHERE spaces.deleted_at is null';

      $rag_filter = ManagementInformation::getRagFilter($filters);

      if(is_array($rag_filter) && sizeOfCustom($rag_filter)){
        $rag_filter = " and spaces.id in('" . implode("', '", $rag_filter) . "')";
      } else $rag_filter = '';

      if(!empty(array_filter($filters['status_filter']))){
         $where = $where." and spaces.status in ('".implode("','", $filters['status_filter'])."')";
      }

      if(empty($filters['spaces']) || $filters['sort'] == 'pending'){
          $space_where = (!empty($supplier_id))?"spaces.company_seller_id IN ('".$supplier_id."')":"";
          $buyer_where = (!empty($buyer_id))?"spaces.company_buyer_id IN ('".$buyer_id."')":"";
          $filter = $where;
          if(!empty($supplier_id) && !empty($buyer_id)){
            $filter = $where.' and '.$space_where.' or spaces.deleted_at is null and '.$buyer_where;
          }else if(!empty($supplier_id) && empty($buyer_id)){
            $filter = $where.' and '.$space_where;
          }else if(empty($supplier_id) && !empty($buyer_id)){
            $filter = $where.' and '.$buyer_where;
          }
          $order_by = (trim($filters['sort']) != 'pending')?"":"order by total ".$filters['sort_order'];
          $offset_value = "OFFSET {$filters['offset']}";
      }else{
          $filter = (!empty($filters['spaces']))?$where." and spaces.id IN ('".implode("','",$filters['spaces'])."')":"";
      }
      
          $limit_offset = !$filters['disable_offset'] ? "LIMIT {$filters['limit']} {$offset_value}":'';
          $where_date = (!empty($filters['date_value']))?" and space_users.created_at <= '".$filters['date_value']." 23:59:59'":'';
          $pending_invites = DB::select("SELECT spaces.id,
                          (SELECT 
                          case when COUNT(*) > 0 THEN COUNT(*) ELSE 0 end as sum
                          FROM space_users 
                          WHERE spaces.id = space_users.space_id and space_users.metadata->>'invitation_code' = '0' ".$where_date." 
                          ) As total
                          FROM spaces
                          left join space_users on space_users.space_id=spaces.id
                          ".$filter." ".$rag_filter."
                          group by spaces.id
                          ".$order_by." ".$limit_offset);
          return arrayValueToKey(objectToArray($pending_invites), 'id');
    }

    public static function createSpaceUser($space_id,$user_id,$user_type_id){
      return static::create(['space_id' => $space_id, 'user_id' => $user_id, 'user_type_id' => $user_type_id, 'metadata' => ["invitation_status" => "Member", "invitation_code" => 1]]);
    }

    public static function getUserBySpaceId($share_id,$user_type){
      return static::with('User')->where('space_id', $share_id)->where('user_type_id', $user_type)->orderBy('updated_at', 'DESC')->get()->toArray();
    }

    public function getNonFeedbackUser($space_id, $year, $month){
       return $this->with('User')
                    ->where('space_id','=',$space_id)
                    ->where('user_status','=',0)
                    ->where('metadata->invitation_code','=',1)
                    ->whereNotIn('user_id',function($query)use($year,$month,$space_id){
                        $query->select('user_id')->from('feedback')
                        ->where('space_id','=',$space_id)
                        ->whereYear('created_at', '=', $year)
                        ->whereMonth('created_at', '=', $month);
                    })->get()->toArray(); 
    }

     public function getInactiveSpaceUser($space_id,$user_id, $selection_method='get')  {
      return $this->space($space_id)
        ->mySpace($user_id)
        ->whereNotNull('deleted_at')
        ->$selection_method();
    }

    public function getActiveSpaceUserOnOtherSpace($user_id, $selection_method='get')  {
      return $this->with('user_role')->mySpace($user_id)->active()->$selection_method();
    }

    public function getSpaceAdmin($space_id){
      return $this->where('space_id',$space_id)->where('user_type_id',config('constants.USER_ROLE_ID'))
                  ->where('user_id','!=',Auth::user()->id)
                  ->whereNull('deleted_at')
                  ->with(array('User'=>function($query) {
                      $query->select('id','email');
                  }))->get()->toArray();
    }
}