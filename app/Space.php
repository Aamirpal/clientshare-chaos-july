<?php

namespace App;
use Image;
use Carbon\Carbon;
use App\Helpers\Aws;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Jobs\MoveClearBitLogo;
use App\Models\SpaceCategory;

class Space extends Model {
    use SoftDeletes;
    protected $keyType = 'string';
    const ADD_COMMENT_ATTACHMENT = 'edb8a67b-517a-4cb5-8ea8-a579ab08d5ab';
    const MAX_COLLEAGUE = 7;
    const MIN_COLLEAGUE = 4;

    const VERSION = [
        0 => 'Old',
        1 => 'New'
    ];
    const DEFAULT_V2_CATEGORIES=[
        'General Updates',
        'Business Reviews',
        'Management Information',
        'Innovation & Added Value',
        'Company & Employee News',
        'Management Messages'
    ];
    
    const DEFAULT_CATOGERY = [
        'category_1' => 'General',
        'category_2' => 'Business Reviews',
        'category_3' => 'MI & Reports',
        'category_4' => 'Proposals & Presentations',
        'category_5' => 'Innovation & Success Stories',
        'category_6' => 'Contracts'
    ];
    
    const SPACE_SORT = [
        'buyer' => 'buyer_name',
        'supplier' => 'seller_name',
        'contract_value' => 'contract_value',
        'contract_end_date' => 'contract_end_date',
        'status' => 'status'
    ];

    protected $appends = [
        'seller_logo_unwraped_url',
        'buyer_logo_unwraped_url',
        'version_name'
    ];
    
    protected $casts = [
        'category_tags' => 'json',
        'metadata' => 'json',
        'seller_logo' => 'json',
        'buyer_logo' => 'json',
        'processed_seller_logo' => 'json',
        'processed_buyer_logo' => 'json',
        'email_header' => 'json',
        'background_image' => 'json',
        'allowed_ip' => 'json'
    ];

    protected $fillable = [
        'user_id', 'company_id', 'share_name', 'category_tags', 'allow_feedback', 'metadata',
        'company_seller_id', 'company_buyer_id', 'company_seller_logo', 'company_buyer_logo',
        'seller_processed_logo', 'buyer_processed_logo', 'background_logo', 'domain_restriction', 'doj', 'sub_companies', 'seller_logo', 'buyer_logo', 'processed_seller_logo', 'processed_buyer_logo', 'email_header_image', 'email_header', 'background_image', 'allowed_ip', 'ip_restriction', 'seller_circular_logo', 'buyer_circular_logo', 'version', 'share_profile_progress'
    ];
    protected $dates = ['deleted_at'];

    public function generateEmailBannerLogosforShares($space_id = null){
        $spaces = ($space_id)?$this->whereId($space_id)->get(): $this->whereNull('seller_circular_logo')->orWhere('seller_circular_logo', null)->get();

        foreach ($spaces as $space) {
            try{
                $this->generateJointShareLogos($space);
            } catch(\Exception $exception){
                $exception->getMessage();
            }
        }
    }

    public function generateJointShareLogos($space){

        $seller_url = wrapCircularBorder(makeAWSFilePublic(urlCleaner($space['seller_logo'])), $space['id'], 'seller', Config('constants.EMAIL_BANNER_DIMENSION'));
        $buyer_url = wrapCircularBorder(makeAWSFilePublic(urlCleaner($space['buyer_logo'])), $space['id'], 'buyer', Config('constants.EMAIL_BANNER_DIMENSION'));
        $this->where('id', $space['id'])->update(['seller_circular_logo' => filePathUrlToJson($seller_url)]);
        $this->where('id', $space['id'])->update(['buyer_circular_logo' => filePathUrlToJson($buyer_url)]);
    }

    public function getSpacesListById($space_ids){
        return $this->whereIn('id', $space_ids)->get();
    }

    public function editSinglePostTemplate( $space_id) {
        return $this->with(['spaceUsers'=>function($space_user){
            $space_user->select('user_id', 'space_id', 'user_company_id', 'sub_company_id')->with(['User'=>function($user){
                $user->select('id', 'first_name', 'last_name');
            }])->active();
        }])->where('id', $space_id)->first();
    }

    public function getSellerLogoUnwrapedUrlAttribute(){
      return $this->getOriginal('company_seller_logo');
    }

    public function getBuyerLogoUnwrapedUrlAttribute(){
      return $this->getOriginal('company_buyer_logo');
    }

    public static function moveLogo($columns){
        $shares = static::where($columns['old_column'], 'ilike', '%clearbit%')
            ->where($columns['old_column'], '!=', '')
            ->where($columns['new_column'], null)
            ->withTrashed()
            ->get()->toArray();
        dispatch(new MoveClearBitLogo($shares, $columns));
        return;
    }

    public function spaceInfo($selection, $space_id){
        return $this->select($selection)->findOrFail($space_id);
    }

    public function spacesList($selection, $selection_method){
        return $this->select($selection)
            ->orderBy('share_name')
            ->$selection_method();
    }

    public function getActiveSpacesWithSellerBuyer() {
        $companies['sellers'] = $this->selectRaw('distinct company_seller_id')
            ->get()->toArray();
        $sellers = $buyers = [];
        foreach($companies['sellers'] as $seller)
            $sellers[]['company_seller_id'] = $seller['company_seller_id'];

        $companies['sellers'] = Company::whereIn('id', ($sellers))->orderBy('company_name')->get();

        $companies['buyers'] = $this->selectRaw('distinct company_buyer_id')
            ->get()->toArray();
        foreach($companies['buyers'] as $seller)
            $buyers[]['company_buyer_id'] = $seller['company_buyer_id'];

        $companies['buyers'] = Company::whereIn('id', ($buyers))->orderBy('company_name')->get();
        return $companies;
    }
    
    public static function getSpacesBuyerSeller($spaces=null) {
        $query = static::whereIn('id', $spaces)
            ->with('BuyerName', 'SellerName')
            ->get()->toArray();
        return arrayValueToKey(objectToArray($query), 'id');
    }
    public static function spaceCompany($space_id, $company_id){
        $space = static::findOrFail($space_id);
        if($space['company_buyer_id'] == $company_id) return Company::COMPANY_TYPE['buyer'];
        elseif($space['company_seller_id'] == $company_id) return Company::COMPANY_TYPE['seller'];
        return null;
    }

    public static function sharesWithActiveFeedback(){
        return static::select('id')->whereRaw('feedback_status is true')
        ->whereRaw("to_char(feedback_status_to_date, 'mm') = to_char(now(), 'mm')")
        ->whereRaw("to_char(feedback_status_to_date, 'yy') = to_char(now(), 'yy')")->get();
    }

    public static function superAdminSpaces(){
        return static::with('AdminUser', 'Company', 'SellerName', 'BuyerName')
            ->with(['spaceuser' => function($query) {
                $query->active();
            }])->orderBy('created_at', 'desc')->get()->toArray();
    }

    public function ManagementInformationEmailLog(){
        return $this->hasMany("App\ManagementInformationEmailLog");
    }

    public static function spaceById($space_id, $selection_method){
      return static::space($space_id)->$selection_method();
    }

    public static function spaceByName($share_name, $selection_method){
      return static::where('share_name',$share_name)->$selection_method();
    }

    public static function spaceByIdGetCreatedAt($space_id,$selector){
      return static::select($selector)->where('id', $space_id)->first();
    }

    public function scopeSpace($query, $space_id){
        return $query->where('id', $space_id);
    }

    public static function updateSpaceById($space_id, $space_data){
        return static::space($space_id)->update($space_data);
    }

    public function getCreatedAtAttribute($value) {
        if(!$value) return $value;
        return Carbon::parse($value)->timezone(\Auth::user()->timezone??'Europe/London');
    }
    
    public function getUpdatedAtAttribute($value) {        
        if(!$value) return $value;
        return Carbon::parse($value)->timezone(\Auth::user()->timezone??'Europe/London');
    }

    public function getCompanySellerLogoAttribute() {
        if(!$this->seller_logo) return env('APP_URL').Config('constants.CS_PROFILE_IMAGE');
        return wrapUrl($this->seller_logo);
    }

    public function getCompanyBuyerLogoAttribute() {
        if(!$this->buyer_logo) return env('APP_URL').Config('constants.CS_PROFILE_IMAGE');
        return wrapUrl($this->buyer_logo);
    }

    public function getSellerProcessedLogoAttribute() {
        if(!$this->processed_seller_logo) return env('APP_URL').Config('constants.CS_PROFILE_IMAGE');
        return $this->processed_seller_logo;
    }

    public function getBuyerProcessedLogoAttribute() {
        if(!$this->processed_buyer_logo) return env('APP_URL').Config('constants.CS_PROFILE_IMAGE');
        return $this->processed_buyer_logo;
    }

    public function getSellerLogoAttribute($seller_logo){
        if(!$seller_logo) return env('APP_URL').Config('constants.CS_PROFILE_IMAGE');
        return composeUrl(json_decode($seller_logo, true));
    }

    public function getBuyerLogoAttribute($buyer_logo) {
        if(!$buyer_logo) return env('APP_URL').Config('constants.CS_PROFILE_IMAGE');
        return composeUrl(json_decode($buyer_logo, true));
    }

    public function getSellerCircularLogoAttribute($seller_circular_logo) {
        if(!$seller_circular_logo) return Config('constants.EMAIL_DEFAULT_SHARE_LOGO');
        return composeUrl($seller_circular_logo);
    }

    public function getBuyerCircularLogoAttribute($buyer_circular_logo) {
        if(!$buyer_circular_logo) return Config('constants.EMAIL_DEFAULT_SHARE_LOGO');
        return composeUrl($buyer_circular_logo);
    }

    public function getProcessedSellerLogoAttribute($processed_seller_logo) {
        if(!$processed_seller_logo) return env('APP_URL').Config('constants.CS_PROFILE_IMAGE');
        return composeUrl(json_decode($processed_seller_logo, true));
    }

    public function getProcessedBuyerLogoAttribute($processed_buyer_logo) {
        if(!$processed_buyer_logo) return env('APP_URL').Config('constants.CS_PROFILE_IMAGE');
        return composeUrl(json_decode($processed_buyer_logo, true));
    }

    public function getEmailHeaderImageAttribute() {
        if(!$this->email_header) return env('APP_URL').Config('constants.CS_PROFILE_IMAGE');
        return wrapUrl($this->email_header);
    }

    public function getEmailHeaderAttribute($email_header) {
        if(!$email_header) return false;
        return composeUrl(json_decode($email_header, true));
    }

    public function getAllowedIpAttribute($value) {
        return json_decode($value, true)??[];
    }

    public function getIdAttribute($value) {
        return (string) $value;
    }

    public function reports(){
       return $this->hasMany("App\PowerBiReport","space_id","id");
    }

    /**/
    public function AdminUser(){
        return $this->belongsTo("App\User", "user_id"); 
    }

    /**/
    public function Company(){
        return $this->belongsTo("App\Company"); 
    }

    /**/
    public function Posts(){
       return $this->hasMany("App\Post","space_id","id");
   }

   public function QuickLinks(){
       return $this->hasMany("App\QuickLinks","share_id","id");
   }

    public function SellerName(){
        return $this->hasOne("App\Company", "id","company_seller_id"); 
    }

    public function BuyerName(){
        return $this->hasOne("App\Company", "id","company_buyer_id"); 
    }

    public function spaceuser(){
        return $this->hasOne("App\SpaceUser", "space_id");
    }

    public function spaceMember(){
        return $this->hasOne("App\SpaceUser", "space_id")->where('user_type_id',config('constants.USER_TYPE_ID'));
    }

    public function spaceAdmin(){
        return $this->hasOne("App\SpaceUser", "space_id")->where('user_type_id',config('constants.USER_ROLE_ID'));
    }

    public function spaceUsers(){
        return $this->hasMany("App\SpaceUser", "space_id","id");
    }

    public function user(){
        return $this->hasOne("App\User","id","user_id");
    }

    public static function spaceWithUser($space_id) {
        if($space_id){
            $query = static::where('id', $space_id)
                ->with('user')
                ->get()->toArray();
            return arrayValueToKey(objectToArray($query), 'id');
        }
        return false;
    }

    public function setBuyerLogo($logo,$name){
        $url = $this->getCircleImage($logo,$name);
        if($url != ''){
            $this->processed_buyer_logo = $url;
           return $this->save();
        }
        return false;
    }
    public function setSellerLogo($logo,$name){
        $url = $this->getCircleImage($logo,$name);
        if($url != ''){
            $this->processed_seller_logo = $url;
            return $this->save();
        }
        return false;
    }


    public function getCircleImage($logo, $name, $crop_fit=false){
        return getCircleImage($logo, $name, $crop_fit);
    }
    public static function checkSpaceDeleted($space_id) {       
        return $space = static::where('id',$space_id)->whereNotNull('deleted_at')->withTrashed()->first();      
    }

    public static function uploadSpacesAnalyticsReport($file,$name){
        if(!empty($file) && !empty($name)){
            /*$name = rand()."_".time().".".$name.".zip";*/
            $name = $name.".zip";
            $s3 = \Storage::disk('s3');
            $s3_bucket = env("S3_BUCKET_NAME");
            $filePath = '/analytics_report/' . $name;
            $fulleurl1 = "https://s3-eu-west-1.amazonaws.com/".$s3_bucket."".$filePath;
            $s3->put($filePath, file_get_contents($file), 'public');
            return $fulleurl1;
        }
    }

    public static function getSpaceCreatedAt($space_id) {       
        return $space = static::where('id',$space_id)->first(['created_at'])->toArray();      
    }
    public static function getFeedbackStatus($share_id) {
        return static::select('feedback_status','feedback_status_to_date')->where('id', $share_id)->first();
    }
    public static function getSpaceBuyerSeller($share_id) {
        return static::where('id',$share_id)->with('buyerName','sellerName')->withCount('reports')->first();
    }
    public static function getAllSpaceBuyerSeller($share_id) {
        return static::where('id',$share_id)->with('buyerName','sellerName')->get();
    }
    public static function getUserSpace($user_id,$share_id) {
        return static::space($share_id)->where('user_id', $user_id)->get()->toArray();
    }
    public static function getActiveShares($user) {
        return static::whereHas('spaceuser', function($q)use($user){
            if($user->user_type_id != config('constants.ADMIN_ROLE_ID') ){
                $q->where('user_id', $user->id);
            }
            $q->active();
        })->get();
    }
    public static function getSpaceUserWithMetaData() {
        return static::with('BuyerName', 'SellerName')
      ->whereHas('spaceuser', function($q){
        $q->whereRaw("metadata #>> '{user_profile}' != ''");
      })->orderBy('share_name', 'ASC')->get();
    }
    public static function getSpaceUserByUserId($user_id) {
        return static::with('BuyerName', 'SellerName')
            ->whereHas('spaceuser', function($q)use($user_id){
                $q->whereRaw("metadata #>> '{user_profile}' != ''")
                ->where('user_id', $user_id)->active();
            })
        ->orderBy('share_name', 'ASC')->get();
    }
    public static function getShare($rank) {
        return static::select('id')->where('rank', $rank)->first();
    }

    public static function saveTwitterHandles($form_data) {
        if(empty(array_filter($form_data['twitter_handles'])))
            return static::where('id', $form_data['space_id'])->update(['twitter_handles' => config('constants.EMPTY_JSON') ]);

        $form_data['twitter_handles'] = array_filter($form_data['twitter_handles']);
        if(!empty($form_data['space_id']) && !empty($form_data['twitter_handles'])){
            return static::where('id', $form_data['space_id'])->update(['twitter_handles' => json_encode($form_data['twitter_handles']) ]);
        }
    }

    public static function getTwitterHandles($space_id) {
         return static::select('twitter_handles')->where('id', $space_id)->get()->toArray();
    }

    public static function getSpacesData($filters){
        
        $base_query = static::selectRaw('spaces.id as space_id, spaces.share_name, spaces.contract_value as contract_value, spaces.contract_end_date as contract_end_date, spaces.status as status, seller.id as seller_id, buyer.id as buyer_id, seller.company_name as seller_name, buyer.company_name as buyer_name, u.first_name as first_name, u.last_name as last_name')
                    ->Join('users as u', 'u.id', '=', 'spaces.user_id')
                    ->join('companies as seller', 'seller.id', '=', 'spaces.company_seller_id')
                    ->join('companies as buyer', 'buyer.id', '=', 'spaces.company_buyer_id')
                    ->whereHas('spaceuser', function($q) {
                        $q->whereRaw('space_users.deleted_at is null');
                    });
        $RAG_filter = ManagementInformation::getRagFilter($filters, false);

        if(is_array($RAG_filter) && sizeOfCustom($RAG_filter)){
          $base_query->whereIn('spaces.id', $RAG_filter);
        } else {
            $RAG_filter='';
        }

        if(!empty(array_filter($filters['status_filter']))){
            $base_query->whereIn('spaces.status', $filters['status_filter']);
        }
        
        if(!empty($filters['data']) && !empty($filters['spaces'])) { 
            $base_query->whereIn('spaces.id', $filters['spaces']);
        } else {
            
            if(!empty($filters['suppliers']) && !empty($filters['buyers'])) {
                
                $base_query->where(function($query) use ($filters) {
                    $query->whereIn('spaces.company_seller_id', $filters['suppliers'])
                        ->orWhereIn('spaces.company_buyer_id', $filters['buyers']);
                });

                if(!empty(array_filter($filters['status_filter']))) {
                    $base_query->whereIn('spaces.status', $filters['status_filter']);
                }

            }else if(!empty($filters['suppliers']) && empty($filters['buyers'])) { 
                $base_query->whereIn('spaces.company_seller_id', $filters['suppliers']);
            }else if(empty($filters['suppliers']) && !empty($filters['buyers'])) { 
                $base_query->whereIn('spaces.company_buyer_id', $filters['buyers']);
            }


            if(!$filters['disable_offset'])
                $base_query->offset($filters['offset'])
                        ->limit($filters['limit']);
        }        
        
        if(!empty(array_filter($filters['spaces_filter'])))
            $base_query->whereIn('spaces.id', array_filter($filters['spaces_filter']));

        if(isset(static::SPACE_SORT[$filters['sort']])){
            $base_query->orderByRaw(static::SPACE_SORT[$filters['sort']].' '.$filters['sort_order'].' NULLS LAST');
            
            if(!$filters['disable_offset'])
                $base_query->offset($filters['offset'])
                    ->limit($filters['limit']);
        }

        $shares = $base_query->get()->toArray();
        return arrayValueToKey(objectToArray($shares), 'space_id');
    }

    public function getBackgroundImageAttribute($background_image) {
        if(!$background_image) 
            return false;
        return composeUrl(json_decode($background_image, true));
    }

    public function UpdateClearBitLogo($id, $data) {
        return $this->where('id', $id)->withTrashed()->update($data);
    }

    public function getCompanySeller($space_id, $user_company_id) {
        return $this->select('id')->where('id', $space_id)
                    ->where('company_seller_id', $user_company_id)->get();
    }

    public function getCompanyBuyer($space_id, $user_company_id) {
        return $this->select('id')->where('id', $space_id)
                    ->where('company_buyer_id', $user_company_id)->get();
    }

    public function getShareProfileData($space_id) {
         return $this->withCount('QuickLinks', 'Posts', 'spaceMember','spaceAdmin')
                        ->where('id', $space_id)
                        ->first()->toArray();
    }

    public function getColleagueForInvitaionMail($user_id){
        $space_users = $this->hasMany("App\SpaceUser", "space_id","id")->where('space_users.user_id','!=', $user_id)->active();
        $total_users = $space_users->count();
        $output=[];
        if($total_users < static::MIN_COLLEAGUE)
            return $output;

        if($total_users > static::MAX_COLLEAGUE)
            $space_users->latest()->limit(static::MAX_COLLEAGUE - 1);

        foreach($space_users->get() as $space_user){
            $output[$space_user->user->id]= $space_user->user->getImageOrInitialsEmail();
        }
        
        if($total_users > static::MAX_COLLEAGUE)
            $output['rest_count'] = $total_users - static::MAX_COLLEAGUE + 1;

        return $output;
    }

    public function JointShareLogoForEmail(){
        return $this->joint_share_email_logos ? composeEmailUrl(composeUrl($this->joint_share_email_logos)) : config('constants.s3.url') . 'spaces_email_banner/default.png';
    } 

    public function getVersionNameAttribute(){
        return self::VERSION[+$this->version];
    }

    // V2 code
    public function setNewDefaultCategories(){
        $data['space_id'] = $this->id;
        foreach(self::DEFAULT_V2_CATEGORIES as $category){
            $data['name'] = $category;
            $data['logo'] = config('constants.category_logos.'.strtolower(getCategorySlug($category)));
            if(SpaceCategory::where($data)->count() <= 0)
                SpaceCategory::create($data);
        }
    }
}