<?php

namespace App;

use App\Traits\ModelEventLogger;
use Illuminate\Database\Eloquent\{Model,SoftDeletes};
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;

class Post extends Model {
    
    protected $keyType = 'string';
    use SoftDeletes;
    use ModelEventLogger;
    protected $dates = ['deleted_at'];
    protected $appends = ['meta_array'];
    const RULE = ['pin_post'=>2];

    public function getPost($post_id, $user_id)
    {
        return $this->select('id', 'user_id')->where('id', $post_id)
            ->with(['endorseByMe', 'endorse' => function($endorse) use ($user_id){
                return $endorse
                ->where('user_id', '!=', $user_id)
                ->orderBy('created_at', 'desc');
            }])
            ->withCount('endorse')
            ->first();
    }

    public function getDateDiffrence($date) {
        return DB::select("SELECT EXTRACT(EPOCH FROM ((current_timestamp at time zone 'Europe/London') - '".$date."'))::int");
    }
    
    public function getMetaArrayAttribute(){
        return json_decode($this->metadata, true);
    }

    public static function postOwner($comment, $current_user, $post, $filter=false){
        return static::selectRaw('distinct user_id')
            ->where('id', $comment->post_id)
            ->where('user_id', '!=', $current_user->id)
            ->with('user')
            ->whereHas('spaceuser', function($q)use($filter, $post){
                $q->where('space_id', $post->space_id);
                if($filter)
                    $q->where($filter, true);
            })
        ->get()->toArray();
    }

    public function spaceuser(){
        return $this->hasMany('App\SpaceUser','user_id','user_id');
    }

    public static function postUsers($space_id, $search, $users){
        $search = str_replace("\xc2\xa0", '', $search);
        $search = explode(' ', trim($search));
        $search[1] = $search[1]??$search[0];
        $search_condition = $search[0]==$search[1]?'or':'and';
        $visibility = $users?'limited':'all';
        $users = $users??'null';
        return DB::select("
            SELECT comp.company_name, initcap(first_name) ||' '|| initcap(last_name) as value, 'user:'||SPLIT_PART(email,'@',1) as uid, usr.id as user_id,
            case when usr.id in ($users) or ('$visibility') ilike 'all' then '' else '(Not added to this post)'end as \"user_status\",
            initcap(first_name) ||' '|| initcap(last_name) as display_name 
            from space_users sender
            inner join users usr on sender.user_id = usr.id
            left join companies comp on CASE WHEN sender.sub_company_id::text ilike '00000000%' THEN sender.user_company_id else sender.sub_company_id END = comp.id 
            and sender.space_id = '".$space_id."'
            where sender.space_id = '".$space_id."'
            and sender.metadata->>'invitation_code' = '1'
            and sender.deleted_at is null
            and (usr.first_name ilike '".$search[0]."%' ".$search_condition." usr.last_name ilike '".$search[1]."%')
            order by 2
        ");
    }

    public static function getTopPosts($post_data){
        return DB::select(
            "SELECT pst.id, count(distinct epst.id)+count(distinct pstc.id)+count(distinct pstv.id) as score from posts pst
            left join endorse_posts epst on epst.post_id = pst.id
            left join comments pstc on pstc.post_id = pst.id
            left join post_views pstv on pstv.post_id = pst.id
            where EXTRACT(MONTH FROM pst.created_at) = '".$post_data['month']."' and EXTRACT(YEAR FROM pst.created_at) ='".$post_data['year']."' and (pst.visibility ilike '%all%')
            and pst.space_id ='".$post_data['space_id']."'
            and pst.deleted_at is null
            group by pst.id
            order by score desc
            limit ".$post_data['limit'].";"
        );
    }

    public static function getTopPostsByCompany($post_data){
        DB::select("SELECT user_id into \"".$post_data['user_id']."_temp_posts\" from space_users where metadata->'user_profile'->>'company' ilike '".$post_data['company_id']."';");

        $posts = DB::select(
            "SELECT pst.id, count(distinct epst.id)+count(distinct pstc.id)+count(distinct pstv.id) as score from posts pst
            left join endorse_posts epst on epst.post_id = pst.id and epst.user_id in (select * from \"".$post_data['user_id']."_temp_posts\")
            left join comments pstc on pstc.post_id = pst.id and pstc.user_id in (select * from \"".$post_data['user_id']."_temp_posts\")
            left join post_views pstv on pstv.post_id = pst.id and pstv.user_id in (select * from \"".$post_data['user_id']."_temp_posts\")
            where EXTRACT(MONTH FROM pst.created_at) = '".$post_data['month']."' and EXTRACT(YEAR FROM pst.created_at) ='".$post_data['year']."' and (pst.visibility ilike '%all%')
            and pst.space_id ='".$post_data['space_id']."'
            and pst.deleted_at is null
            group by pst.id
            order by score desc
            limit ".$post_data['limit'].";"
        );

        DB::select("DROP table if exists \"".$post_data['user_id']."_temp_posts\";");
        return $posts;
    }

    public static function postById($post_id){
        return Post::where('id', $post_id)->first();
    }

    public function getCreatedAtAttribute($value) {
        if(!$value) return $value;
        return Carbon::parse($value)->timezone(\Auth::user()->timezone??'Europe/London');
    }
    
    public function getUpdatedAtAttribute($value) {        
        if(!$value) return $value;
        return Carbon::parse($value)->timezone(\Auth::user()->timezone??'Europe/London');
    }

    public function getRepostedAtAttribute($value) {
        if(!$value) return $value;
        return Carbon::parse($value)->timezone(\Auth::user()->timezone??'Europe/London');
    }

    public function getPinnedAtAttribute($value) {
        if(!$value) return $value;
        return Carbon::parse($value)->timezone(\Auth::user()->timezone??'Europe/London');
    }

    public static function getPostComments($space_id, $post_id) {
        return static::where('id',$post_id)
            ->with(['comments.user', 'comments.attachments', 'comments.spaceuser' => function($query) use($space_id) {
                $query->where('space_id', $space_id);
            }]) 
            ->with(['comments'=>function($comments){
                $comments->orderBy('created_at');
            }])->get()->toArray();
    }

    public function postSettings() {
        return $this->hasOne('App\PostUser');
    }

    public function images() {
        return $this->hasMany('App\PostMedia')->whereRaw("metadata->>'mimeType' ilike '%image%'");
    }

    public function documents() {
        return $this->hasMany('App\PostMedia');
    }

    public function videos() 
    {
        return $this->hasMany('App\PostMedia')->whereRaw("metadata->>'mimeType' ilike '%video%'");
    }

    public function category(){
        return $this->hasOne('App\Space', 'id', 'space_id');
    }

    public function getIdAttribute($value) {
        return (string) $value;
    }
    public function getPostDescriptionAttribute($value) {
        return nl2br(str_replace('<br />', '', $value));
    }
    public function comments() {
        return $this->hasMany('App\Comment');
    }
    public function user()
    {
        return $this->belongsTo('App\User');
    }
    public function endorseByMe()
    {
        return $this->hasMany('App\EndorsePost')->where('user_id', \Auth::user()->id);
    }
    public function endorseByOthers()
    {
        return $this->hasMany('App\EndorsePost')->where('user_id', '!=',\Auth::user()->id);
    }
    public function endorse()
    {
        return $this->hasMany('App\EndorsePost');
    }
        public function postmedia()
    {
        return $this->hasMany('App\PostMedia');
    }
    public function notification()
    {
        return $this->hasMany('App\Notification'); 
    }
    public function postuser()
    {
        return $this->hasMany('App\PostUser');
    }

    public function postMediaView(){
        return $this->hasMany('App\PostViews','post_id');
    }

    public function postMediaLog(){
        return $this->hasMany('App\PostViews','post_id');
    }

    public function viewUrlPostCount() {
        return $this->hasMany('App\ActivityLog','content_id')
        ->whereIn('action', ['view embedded url', 'click link'])
        ->selectRaw('content_id, count(*) as count')
        ->groupBy('content_id');
    }

    public static function postDetails($space_id,$post_id) {
        return static::select('post_subject', 'post_description','user_id as suid','users.first_name','users.last_name')
               ->join('users', 'users.id', '=', 'posts.user_id')
               ->where('space_id', $space_id)
               ->where('posts.id', $post_id)
               ->get();
    }

    public static function postData($post_id,$space_id) {
        return static::where('id', $post_id)->where('space_id', $space_id)
               ->with(['User.SpaceUser' => function($q) use($space_id) {
               $q->where('user_status', '0')
               ->where('metadata->invitation_code', '1');
               }])
               ->where('space_id', $space_id)->get()->toArray();
    }
   
    public static function updateCommentCount($post_id,$count) {
        return static::where('id', $post_id)->update(['comment_count' => $count]);
    }

    public static function updatePost($updated_data, $timestamp=false) {
        if(!$timestamp)
            $updated_data['updated_at'] = DB::raw('updated_at');
        return static::where('id', $updated_data['id'])->update($updated_data);
    }

    public static function getCommentCount($post_id) {
        return static::where('id', $post_id)->select('comment_count', 'user_id')->get()->toArray();
    }

    public static function getPostWithUser($post_id) {
        return static::where('id', $post_id)->with('user')->get()->toArray();
    }

    public static function getPostData($space_id,$category,$skip_limit,$limit,$auth_user_id,$user_id) {
        return static::where('space_id', $space_id)
            ->where('metadata->category', $category)
            ->whereRaw("'$user_id' = ANY (string_to_array(visibility,','))")
            ->with('PostMedia')
            ->with('viewUrlPostCount')
            ->with('postmediaview')
            ->with(['User.SpaceUser' => function($query) use($space_id) {
                $query->where('space_id', $space_id);
            }])
            ->with(['endorse.space_user' => function($query) use($space_id) {
                $query->where('space_id', $space_id);
            }])
            ->with(['comments.user', 'comments.spaceuser' => function($query) use($space_id) {
                $query->where('space_id', $space_id);
            }])
            ->with('endorse.user')
            ->with(['postuser' => function($query) use($auth_user_id) {
                $query->where('user_id', $auth_user_id);
            }])
            ->orderByRaw('pin_status desc, case when pin_status = false then created_at else (case when reposted_at is null then created_at else reposted_at end) end desc')
            ->skip($skip_limit)
            ->take($limit)
            ->get()
            ->toArray();
    }
    public static function getPostDataWithUserOrAll($space_id,$skip_limit,$limit,$auth_user_id, $category, $post_id, $comment_limit=2) {
            $posts = static:: where('space_id', $space_id);
            if($category)
                $posts->where('metadata->category', $category);
            if($post_id)
                $posts->where('id', $post_id);
            return $posts->whereRaw(" ( '$auth_user_id' = ANY (string_to_array(visibility,',')) or 'All' = ANY (string_to_array(visibility,','))  ) ")
            ->with('PostMedia')
            ->with(['images' => function($images){
                $images->latest();
            }])
            ->with('documents')
            ->with(['postMediaLog' => function($q){
                $q->select('user_id', 'post_id')->groupBy('user_id', 'post_id');
            }])
            ->with('viewUrlPostCount')
            ->with(['User.SpaceUser' => function($query) use($space_id) {
                $query->where('space_id', $space_id);
            }])
            ->with('endorse','endorseByMe')
            ->with(['endorseByOthers.user','endorseByOthers.space_user' => function($query) use($space_id) {
                $query->where('space_id', $space_id);
            }])
            ->withCount('comments', 'endorse')
            ->with('comments.attachments')
            ->with(['comments' => function($comment)use($space_id, $comment_limit){
                $comment->with(['user', 'spaceuser' => function($spaceuser)use($space_id){
                    $spaceuser->where('space_id', $space_id);
                }]);
                $comment->orderBy('created_at');
            }])
            ->with(['postsettings' => function($post_user) use($auth_user_id){
                $post_user->where('user_id', $auth_user_id);
            }])
            ->orderByRaw('pin_status desc, case when pin_status = false then (case when reposted_at is null then created_at else reposted_at end) else pinned_at end desc')
            ->skip($skip_limit)
            ->take($limit)
            ->get()
            ->toArray();
    }

    public static function getSinglePostDataWithUserOrAll($post_id, $space_id, $user_id) {
            $posts = static:: where('id', $post_id);
            return $posts->whereRaw(" ( '$user_id' = ANY (string_to_array(visibility,',')) or 'All' = ANY (string_to_array(visibility,','))  ) ")
            ->with('PostMedia')
            ->with('postmediaview')
            ->with('viewUrlPostCount')
            ->with(['User.SpaceUser' => function($query) use($space_id) {
                $query->where('space_id', $space_id);
            }])
            ->with(['endorse.space_user' => function($query) use($space_id) {
                $query->where('space_id', $space_id);
            }])
            ->with(['comments.user', 'comments.spaceuser' => function($query) use($space_id) {
                $query->where('space_id', $space_id);
            }])
            ->with('endorse.user')
            ->with(['postuser' => function($query) use($user_id) {
                $query->where('user_id', $user_id);
            }])
            ->orderByRaw('pin_status desc, case when pin_status = false then (case when reposted_at is null then created_at else reposted_at end) else pinned_at end desc')
            ->get()
            ->toArray();
    }
    public static function pinPostCount($space_id) {
        return static::where([ 'space_id' => $space_id, 'pin_status' => true])->get()->count();
    }
    
    public static function checkPostVisibility($post_id, $user_id, $selection_method='count') {
        return static::where('id', $post_id)
        ->whereRaw("(visibility ilike '%".$user_id."%' or visibility ilike '%all%')")
        ->$selection_method();
    }

    public static function postAllVisibility($post_id,$user_id) {
        return static::where('id', $post_id)
            ->where(function($query) use($user_id) {
                $query->orWhere('visibility', 'ilike', '%' . $user_id . '%')
                ->orWhere('visibility', 'ilike', '%all%');
            })
            ->with('User.SpaceUser')->get();
    }
    public static function postWithEndorseUser($post_id,$space_data,$limit) {
        return static::where('id', $post_id)->with('PostMedia')->with('postmediaview')->with('viewUrlPostCount')
            ->with(['User.SpaceUser' => function($qa) use($space_data) {
                $qa->where('space_id', $space_data[0]['id']);
            }])->with(['endorse.space_user' => function($q) use($space_data) {
                $q->where('space_id', $space_data[0]['id']);
            }])->with(['comments.user', 'comments.spaceuser' => function($q4) use($space_data) {
                $q4->where('space_id', $space_data[0]['id']);
            }])->with('endorse.user')->take($limit)->get()->toArray();
    }
    public static function postWithMedia($space_id,$category,$user_id,$auth_user_id,$limit) {
        return static::where('space_id', $space_id)
                ->where('metadata->category', $category)
                ->whereRaw("'$user_id' = ANY (string_to_array(visibility,','))")
                ->with('PostMedia')->with('postmediaview')->with('viewUrlPostCount')->with(['User.SpaceUser' => function($query) use($space_id) {
                    $query->where('space_id', $space_id);
                }])->with(['endorse.space_user' => function($query) use($space_id) {
                    $query->where('space_id', $space_id);
                }])->with(['comments.user', 'comments.spaceuser' => function($query) use($space_id) {
                    $query->where('space_id', $space_id);
                }])->with('endorse.user')->with(['postuser' => function($query) use($space_id,$auth_user_id) {
                    $query->where('user_id', $auth_user_id);
                }])->orderByRaw('pin_status desc, case when pin_status = false then (case when reposted_at is null then created_at else reposted_at end) else pinned_at end desc')->take($limit)->get()->toArray();
    }
    public static function postWithUserOrAll($space_id,$user_id,$auth_user_id,$limit) {
        return static:: where('space_id', $space_id)
                ->whereRaw(" ( '$user_id' = ANY (string_to_array(visibility,',')) or 'All' = ANY (string_to_array(visibility,','))  ) ")
                ->with('PostMedia')->with('postmediaview')->with('viewUrlPostCount')
                ->with(['User.SpaceUser' => function($query) use($space_id) {
                    $query->where('space_id', $space_id);
                }])
                ->with(['endorse.space_user' => function($query) use($space_id) {
                    $query->where('space_id', $space_id);
                }])
                ->with(['comments.user', 'comments.spaceuser' => function($query) use($space_id) {
                    $query->where('space_id', $space_id);
                }])
                ->with('endorse.user')
                ->with(['postuser' => function($query) use($space_id,$auth_user_id) {
                    $query->where('user_id', $auth_user_id);
                }])
                ->orderByRaw('pin_status desc, case when pin_status = false then (case when reposted_at is null then created_at else reposted_at end) else pinned_at end desc')
                ->take($limit)
                ->get()->toArray();
    }

    public static function postInteractionLog($space_id,$start_date,$end_date) {
        return DB::select(
            "SELECT to_char(created_at,'dd/mm/yyyy') as \"Date\",pst_id,pm_id,\"Subject\",\"Category\",\"Attachment\",\"Added by\", company_name as \"Company\"
                , max(\"Views (Buyer)\") as \"Views (Buyer)\"
                , max(\"Downloads (Buyer)\") as \"Downloads (Buyer)\"
                , max(\"Views (Seller)\") as \"Views (Seller)\"
                , max(\"Downloads (Seller)\") as \"Downloads (Seller)\"
                from (
                SELECT distinct company_name,pst_id,pm_id,created_at,user_company_id, post_subject as \"Subject\",category as \"Category\",attachment as \"Attachment\",added_by as \"Added by\",
                    case when pm_id is null and attachment is null then 'NA' when user_company_id = (select company_buyer_id from spaces where id = '".$space_id."') then sum(view)::text else 0::text end as \"Views (Buyer)\",
                    case when pm_id is null then 'NA' when user_company_id = (select company_buyer_id from spaces where id = '".$space_id."') then sum(download)::text else 0::text end as \"Downloads (Buyer)\",
                    case when pm_id is null and attachment is null then 'NA' when user_company_id = (select company_seller_id from spaces where id = '".$space_id."') then sum(view)::text else 0::text end as \"Views (Seller)\",
                    case when pm_id is null then 'NA' when user_company_id = (select company_seller_id from spaces where id = '".$space_id."') then sum(download)::text else 0::text end as \"Downloads (Seller)\"

                from (
                    SELECT
                        case when comp.company_name is null then pst_comp.company_name else comp.company_name end,pst.created_at,case when su.user_company_id is null then psu.user_company_id else su.user_company_id end,pst.id as pst_id, pm.id as pm_id,pst.post_subject, pst.metadata->>'category' as category,
                        case when pm.metadata->>'originalName' is not null then pm.metadata->>'originalName' else pst.metadata->'get_url_data'->>'url_list' end as attachment,
                        usr.first_name||' '||usr.last_name as added_by, case when log.action ilike '%download%' then 1 else 0 end as download, 
                        case when log.action ilike '%view%' or log.action ilike '%play%' or log.action = 'click link' or log.action = 'view embedded url' then 1 else 0 end as view
                    from posts pst
                    left join post_media pm on pm.post_id = pst.id
                    inner join users usr on usr.id  = pst.user_id
                    left join activity_logs log on (log.content_id = pm.id::text and log.content_type in ('App\PostMedia', 'AppPostMedia')) or log.content_id = pst.id::text
                    left join space_users su on su.space_id = pst.space_id and log.user_id = su.user_id
                    inner join space_users psu on psu.space_id = pst.space_id and psu.user_id = pst.user_id
                    left join companies comp on CASE WHEN su.sub_company_id::text ilike '00000000%' THEN su.user_company_id else su.sub_company_id END = comp.id
                    left join companies pst_comp on CASE WHEN psu.sub_company_id::text ilike '00000000%' THEN psu.user_company_id else psu.sub_company_id END = pst_comp.id

                    where pst.space_id = '".$space_id."'
                    and pst.created_at between  '".$start_date."' and  '".$end_date."'
                    and pst.deleted_at is null
                    order by pst.created_at
                ) as tbl
                group by company_name,created_at,user_company_id,pst_id,pm_id,post_subject,category,attachment,added_by,user_company_id
                order by created_at
                ) as tbl2 
                group by company_name,created_at,user_company_id,pst_id,pm_id,\"Subject\",\"Category\",\"Attachment\",\"Added by\";"         
        );
    }
        public static function sellerPosts($space_id,$start_date,$end_date) {
        return DB::select(
            "SELECT
                to_char(post.created_at, 'YYYY-MM'),
                case when user_company_id = ( select company_buyer_id from spaces where id = '".$space_id."' )
                    then 'Buyer'
                when user_company_id = ( select company_seller_id from spaces where id = '".$space_id."')
                    then 'Seller'
                end as tag, count(*)
            from posts post
            inner join space_users su on su.user_id = post.user_id and post.space_id = su.space_id
            where su.space_id = '".$space_id."'
            and user_company_id != '00000000-0000-0000-0000-000000000000'
            and post.created_at between  '".$start_date."' and  '".$end_date."'
            and post.deleted_at is null
            group by tag,to_char(post.created_at, 'YYYY-MM')
            order by to_char(post.created_at, 'YYYY-MM');"
        );
    }
     public static function searchPosts($spaceId,$userId,$keywords,$coun) {
        return DB::table('posts as p')
        ->select(DB::raw("p.id, p.post_subject, p.space_id, p.post_description, u.id as user_id, u.profile_image as userprofileImage, max(pm.metadata::text) as data"))
        ->leftJoin('post_media as pm','p.id','pm.post_id')
        ->Join('users as u','p.user_id','u.id')
        ->where('p.space_id', '=',$spaceId)
        ->where(function($q)use($userId){
          $q->orWhere('p.visibility', 'ilike','%'.$userId.'%')
          ->orWhere('p.visibility', 'ilike','%all%');
        })
        ->where('p.deleted_at', '=', null)            
        ->where(function($q)use($keywords){
          $q->orWhere('p.post_subject', 'ilike','%'.$keywords.'%')
          ->orWhere('p.post_description', 'ilike','%'.$keywords.'%')              
          ->orWhere('pm.metadata->originalName', 'ilike','%'.$keywords.'%');
        })         
        ->groupby('p.id','u.id')
        ->limit($coun)
        ->get()->toArray();
    }

    public static function updatePostCategory($space_id, $old_category, $new_category = 'category_1'){
        return DB::select('UPDATE "posts" SET "metadata" = metadata::jsonb || \'{"category":"'.$new_category.'"}\' WHERE "space_id" = \''.$space_id.'\' AND "metadata"->>\'category\' = \''.$old_category.'\'');
    }

    public function createPost($post_input) {
        return DB::table('posts')->insertGetId(
                    array('user_id' => $post_input->user_id, 
                          'space_id' => $post_input->space_id, 
                          'post_description' => $post_input->post_description, 
                          'post_subject' => $post_input->post_subject,
                          'comment_count' => $post_input->comment_count, 
                          'metadata' => $post_input->metadata, 
                          'visibility' => $post_input->visibility,
                          'space_category_id' => $post_input->space_category_id,
                          'group_id' => $post_input->group_id,
                          'url_preview' => $post_input->url_preview,
                          'created_at' => Carbon::now(),
                          'updated_at' => Carbon::now())
                );
    }

    public function getPostByDescription($data, $user_id) {
        return $this->where('post_description','ilike', $data['space']['post'])
                    ->where('user_id', $user_id)
                    ->where('post_subject','ilike', $data['post']['subject'])
                    ->orderBy('created_at', 'desc')->first();
    }

    
    public function getAllReactedUsers($post_id, $except_ids){
        $users_reaction = $this->where('posts.id',$post_id)
                                    ->with('comments')->with('endorse')
                                    ->first()->toArray();
                              
        array_push($except_ids,$users_reaction['user_id']);
        $comments = $users_reaction['comments'];  
        $endorse  =  $users_reaction['endorse'];  
        $reactions= array_merge($endorse,$comments);
        $response = [];
        foreach ($reactions as $key => $value) { 
                 $response[strtotime($value['created_at'])] = $value['user_id'];
              }
        $result= array_unique($response); 
        krsort($result);
        return  array_diff($result, $except_ids);
    }

}
