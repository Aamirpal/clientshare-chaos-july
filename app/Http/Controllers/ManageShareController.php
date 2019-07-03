<?php

namespace App\Http\Controllers;

use Auth;
use DB;
use Hash;
use Session;
use Validator;
use App\{
    ActivityLog,Company,Comment,EndorsePost,
    Media,Notification,PostMedia, Post,PostViews,
    Space,SpaceUser,User,EmailLog,QuickLinks
};
use App\Helpers\{
    Aws,
    Logger,
    Post as PostHelper,
    Postmark
};
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Input,
    Config
};
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Controllers\{
    MailerController,
    FeedbackController
};
use Cookie;
use Carbon\Carbon;
use Image;
use File;
use App\{
    PostUser,
    Feedback
};
use App\Jobs\{
    ConvertRoundImage,
    PostViewCallback,
    SendCampaignNotification,
    EmailHeaderImage,
    CreateJointShareLogos
};
use App\{
    SpaceGroups,
    SpaceUserGroups,
    SubCompany,
    Invitation,
    UserType
};
use App\Traits\{
    Generic,
    OneTimePassport
};
use \Symfony\Component\HttpFoundation\Response as HttpResponse;
use Abraham\TwitterOAuth\TwitterOAuth;
use App\Http\Controllers\PostController;
use App\Repositories\GroupUser\GroupUserRepository;
use App\Repositories\Group\GroupRepository;

class ManageShareController extends Controller {

    use Generic, OneTimePassport;

    public function spaceUserAndCategories($space_id){
        $space_data['space_users'] = SpaceUser::getSpaceUserActive($space_id, 'get')->pluck(['user']);
        $space_data['categories'] = Space::spaceById($space_id, 'firstorfail')['category_tags'];
        return $space_data;
    }

    public function view(Request $request) {
        $request_data = $request->all();
        $this->setClientShareList();
        $active_space = Auth::User()->toArray()['active_space'];
        $active_space = json_decode($active_space, true);
        if (isset($request_data['shareid'])) {
            $space = SpaceUser::getFirstSpaceInfo($request_data['shareid']);
        } elseif (isset($request_data['_shareToken'])) {
            $space = SpaceUser::getInactiveUserSpaceInfo(Auth::user()->id,$request_data['_shareToken']);
        } elseif (isset($active_space['last_space'])) {
            $space = (new SpaceUser)->getActiveOrPendingSpaceUser($active_space['last_space'], Auth::user()->id, 'get');
            if (!sizeOfCustom($space)){
                $space = (new SpaceUser)->getActiveSpaceUserOnOtherSpace(Auth::user()->id,'count');
                if ($space == 0)
                    abort(404);
                else
                    $space = (new SpaceUser)->getActiveSpaceUserOnOtherSpace(Auth::user()->id,'first');
            } else {
                $space = $space[0];
            }
            
        } elseif (Auth::user()->registration_status) {
            $space = SpaceUser::getUserProfileInfoInDescOrder(Auth::user()->id);
        }
        if (isset($space) && sizeOfCustom($space)) {
            $space_info = Space::spaceById($space->space_id,'first');
            if(empty($space_info)){
                $space_users = SpaceUser::getUserInfo(Auth::user()->id);
                foreach($space_users as $space_user){
                     $space_info = Space::spaceById($space_user->space_id,'first');
                     if(!empty($space_info)) break;
                }
                if(empty($space_info)){
                    Auth::logout();
                    return redirect('/login');
                }
                return $space_info->id;
            }else{
                return $space->space_id;
            }
        } else {
            Auth::logout();
            return redirect('/login');
        }
        abort(404);
    }

    public function initial_setup(Request $request) {
        $data = $request->all();
        $data['space']['allow_feedback'] = isset($data['space']['allow_feedback']) ? "true" : "false";
        if (!empty($_FILES['file']['tmp_name'])) {
            $file = Input::file('file');
            $file_tmp = getimagesize($file);
            $aws = new Aws;
            $file_temp['temp_name'] = $_FILES['file']['tmp_name'];
            $file_temp['name'] = 'file';
            $data['user']['profile_image'] = filePathUrlToJson($aws->image($file_temp, $request)['path']);
        }
        if (!empty($data['space_user']['description'])) {
            $data['user']['biography'] = $data['space_user']['description'];
        }
        User::where('id', $data['user']['id'])->update($data['user']);
        if (isset($data['categories']))
            $data['space']['category_tags'] = json_encode(array_filter($data['categories'], function($value) {
                return $value != '';
            }));
        Space::updateSpaceById($data['space']['id'], $data['space']);
        return redirect("admin_dashboard");
    }

    public function searchCommunityMember(Request $request) {
        $search_input = $request->all();
        if (!isset($search_input['search']) || (isset($search_input['search']) && empty($search_input['search']))) {
           return back()->withErrors(['search', trans('messages.validation.search_community')]);
        }
        (new UserController)->updateSpaceSessionData($search_input['share_id']);
        $search = trim($search_input['search']);
        $space_data = Space::spaceById($search_input['share_id'], 'first');
        $space_members = SpaceUser::searchCommunityMember($search_input['share_id'], $search);
        $companies_dictonary = Company::getAllCompaniesById(array_unique(array_column($space_members, 'company_id')), ['id', 'company_name']);
        usort($space_members, function($current_user, $next_user) {
            $string_case_comparison = strcasecmp($current_user['user']['first_name'], $next_user['user']['first_name']);
            return $string_case_comparison <=> 0;
        });
        foreach ($space_members as $index => $space_member) {
            $space_members[$index]['company'] = '';
            if (isset($space_member['metadata']['user_profile'])) {
                $company_id = $space_member['metadata']['user_profile']['company'];
                $company = Company::getCompanyById($company_id);
                $space_members[$index]['company'] = $company['company_name'];
            }
        }
        $space_user = SpaceUser::getSpaceUserRole($search_input['share_id'], Auth::user()->id);
        $space_id = $search_input['share_id'];
        $search_input = Space::getSpaceBuyerSeller($search_input['share_id']);
        (new Logger)->mixPannelInitial(Auth::user()->id, $search_input['share_id'], Logger::MIXPANEL_TAG['community_search']);
        return view('pages/community_members', [
            'space_data' => $space_data,
            'space_members' => $space_members,
            'space_id' => $space_id,
            'space_user' => $space_user,
            'data' => $search_input,
            'companies_dictonary' => $companies_dictonary,
            'search' => $search
                ]);
    }


    public function executive_summary_save(Request $request) {
        $executive_summary_form_data = $request->all();
        if(!isset($executive_summary_form_data['onboarding_data']))
            $space_post = \Validator::make($request->all(), ['space.executive_summary' => 'required|max:310']
            );
        if (isset($executive_summary_form_data['delete_summary_files_inp']) && strlen($executive_summary_form_data['delete_summary_files_inp'])) {
            $delete_summary_file_id = explode(",", $executive_summary_form_data['delete_summary_files_inp']);
            $post = new Media;
            $post = Media::whereIn('id', $delete_summary_file_id);
            $post->delete();
        }
        if (!isset($executive_summary_form_data['onboarding_data']) && $space_post->fails())
            return ['result'=>false,'error'=>'Executive Summary can not be empty and not even more than 300 characters.'];

        if (isset($executive_summary_form_data['deleted_aws_files_data']) && strlen($executive_summary_form_data['deleted_aws_files_data'])) {
            $executive_summary_form_data['deleted_aws_files_data'] = json_decode($executive_summary_form_data['deleted_aws_files_data'], true);
            foreach ($executive_summary_form_data['deleted_aws_files_data'] as $key => $value) {
                if(!empty($value))
                    (new Media)->deleteExecutiveFiles(Auth::user()->id, $executive_summary_form_data['space']['id'], $value);
            }
        }
        
        if (isset($executive_summary_form_data['aws_files_data']) && strlen($executive_summary_form_data['aws_files_data'])) {
            $executive_summary_form_data['aws_files_data'] = json_decode($executive_summary_form_data['aws_files_data'], true);
            if(sizeOfCustom($executive_summary_form_data['aws_files_data']) == 2 || sizeOfCustom($executive_summary_form_data['aws_files_data']) == 0)
                (new Media)->deleteExecutiveFiles(Auth::user()->id, $executive_summary_form_data['space']['id']);
            foreach ($executive_summary_form_data['aws_files_data'] as $key => $value) {
                if(!empty($value)){
                    (new Media)->deleteExecutiveFiles(Auth::user()->id, $executive_summary_form_data['space']['id'], $value);
                    if(Media::bySpaceId($executive_summary_form_data['space']['id'], 'count') == config('constants.EXECUTIVE_FILES_LIMIT')) continue;
                    $extension = explode(".", $value['originalName']);
                    (new Media)->createExecutiveFiles(Auth::user()->id, $executive_summary_form_data['space']['id'], $value, $extension);
                }
            }
        }
        if (!empty($executive_summary_form_data['space']['id'])) {
            $executive_summary_form_data['space']['executive_summary'] = $executive_summary_form_data['space']['executive_summary']??NULL;
            Space::where('id', $executive_summary_form_data['space']['id'])->update($executive_summary_form_data['space']);
        }

        (new Logger)->log([
            'description' => Config::get('constants.SAVE_EXECUTIVE_SUMMARY'),
            'action' => Config::get('constants.SAVE_EXECUTIVE_SUMMARY')
        ]);
        (new Logger)->mixPannelInitial(Auth::user()->id, $executive_summary_form_data['space']['id'], Logger::MIXPANEL_TAG['edit_executive_summary']);
        return ['result'=>true, 'executive_summary' => $executive_summary_form_data['space']['executive_summary']];
    }

    function save_ex_summary_files($files2) {
        if (!empty($files2)) {
            foreach ($files2 as $file) {
                if (!empty($file)) {
                    $ext = $file->getClientOriginalExtension();
                    $originalfile = $file->getClientOriginalName();
                    $size = $file->getClientSize();
                    if ($ext != "mp4" && $ext != "pdf" && $ext != "flv" && $ext != "3gp" && $ext != "mkv" && $ext != "ppt" && $ext != "pptx" && $ext != "docx" && $ext != "doc" && $ext != "xls" && $ext != "xlsx" && $ext != "csv") {
                        return back()->withErrors(['summary_file_error' => 'Invalid file type, please choose pdf file or video file '])->withInput();
                    }
                    if ($size == '0') {
                        return back()->withErrors(['summary_file_error' => 'File is empty, please choose pdf file or video file '])->withInput();
                    }
                    $name = rand() . "_" . time() . "." . $ext;
                    $s3 = \Storage::disk('s3');
                    $s3_bucket = env("S3_BUCKET_NAME");
                    $filePath = '/pdf_files/' . $name;
                    $fullurl = "https://s3-eu-west-1.amazonaws.com/" . $s3_bucket . "" . $filePath;
                    $s3->put($filePath, file_get_contents($file), 'public');
                    $media = new Media;
                    $media->user_id = Auth::user()->id;
                    $media->space_id = Session::get('space_info')['id'];
                    $media->s3_file_path = filePathUrlToJson($fullurl);
                    $media->media_type = $ext;
                    $media->metadata = ['mimeType' => $file->getClientMimeType(), 'originalName' => $file->getClientOriginalName(), 'size' => $file->getClientSize()];
                    $media->save();
                }
            }
        }
    }

    /**/

    public function super_admin() {

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $user_type = (session()->get('usertype'));
        if (!trim($user_type)) return redirect('/');

        $this->setClientShareList();
        if ($user_type == Config::get('constants.SUPER_ADMIN')) {
            $spaces = Space::superAdminSpaces();
            foreach ($spaces as $space) {
                $space_buyer = SpaceUser::spaceBuyers($space['id'], 'count', $space['admin_user']['id']);
                $result['data'] = $space;
                $result['spaceBuyer'] = $space_buyer;
                $space_buyer_info[] = $result;
            }
            return view('share/index', ['data' => $space_buyer_info]);
        } elseif ($user_type == 'admin' || $user_type == 'user') {
            return $this->view($request);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, GroupRepository $group) {
        $this->validate($request, [
            'seller.seller_name' => 'required|max:100',
            'buyer.buyer_name' => 'required|max:100',
            'share.share_name' => 'unique:spaces,share_name|required|max:100',
            'user.email' => 'required|email|not_in:' . Config::get('constants.email.restricted_emails'),
            ], [
            'required' => 'This field is required.',
            'email' => 'The email must be a valid email address.',
            'unique' => 'The share name has already been taken.',
            'user.email.not_in' => 'Email cannot be used for share.'
            ]);

        $data = $request->all();
        $sellername = $data['seller']['seller_name'];
        ;
        $buyername = $data['buyer']['buyer_name'];
        $seller_logo_path = null;
        $buyer_logo_path = null;
        if ($data['sellertype'] == Config::get('constants.API'))
            $seller_logo_path = $this->uploadLogo($data['sellerlogo']);

        if ($data['buyertype'] == Config::get('constants.API'))
            $buyer_logo_path = $this->uploadLogo($data['buyerlogo']);
        
        if ($data['sellertype'] == 'browse' && isset($data['seller']['seller_logo']))
            $seller_logo_path = $this->save_logo($data['seller']['seller_logo']);

        if ($data['buyertype'] == 'browse' && isset($data['buyer']['buyer_logo'])) 
            $buyer_logo_path = $this->save_logo($data['buyer']['buyer_logo']);

        $seller_company = (new Company)->getCompanyBySellerOrbuyerName($data['seller']['seller_name']);
        if (!sizeOfCustom($seller_company)) 
            $seller_company[0] = (new Company)->createCompanyName($data['seller']['seller_name']);

        $buyer_company = (new Company)->getCompanyBySellerOrbuyerName($data['buyer']['buyer_name']);

        if (!sizeOfCustom($buyer_company))
            $buyer_company[0] = (new Company)->createCompanyName($data['buyer']['buyer_name']);

        $user = User::getUserByEmail($data['user']['email']); 
        if (!sizeOfCustom($user)) 
        {
            $this->validate($request, [
                'user.email' => 'unique:users,email|required|email',
                ]);
            $data['user']['user_type_id'] = Config::get('constants.USER_ROLE_ID');
            $data['user']['password'] = Hash::make('password');
            $user = User::create($data['user']);
        }
        $banner_image = NULL;
        $banner = $data['bannerbackground'];
        if ($banner != '') {
            $banner_image = $this->bannerUpload($banner);
            $banner_image = $this->save_logo($banner_image, config('constants.URL_EXIST'));
        }

        $categories = Space::DEFAULT_CATOGERY;
        $data['share']['company_seller_id'] = $seller_company[0]['id'];
        $data['share']['company_buyer_id'] = $buyer_company[0]['id'];
        $data['share']['seller_logo'] = $seller_logo_path;
        $data['share']['buyer_logo'] = $buyer_logo_path;
        $data['share']['company_id'] = $seller_company[0]['id'];
        $data['share']['user_id'] = $user->id;
        $data['share']['category_tags'] = $categories;
        $data['share']['background_image'] = $banner_image;
        $data['share']['feedback_status_to_date'] = Carbon::parse(date('Y-m-01'))->addMonth(1)->toDateTimeString();

        $share = Space::create($data['share']);
        $share->setNewDefaultCategories();
        $group->createDefaultGroup($share->id);
        $update_data['space']['status'] = config::get('constants.SHARE_STATUS');
        Space::updateSpaceById($share['id'], $update_data['space']);
        $job_data = [
            'seller_logo' => $data['share']['seller_logo'],
            'seller_name' => $sellername,
            'buyer_logo' => $data['share']['buyer_logo'],
            'buyer_name' => $buyername,
            'share_id' => $share['id'],
            'user_id' => $user->id,
            'form_edit' => 'false',
            'share_data' => $data['share']
        ];
        SpaceUser::createSpaceUser($share->id, $user->id, Config::get('constants.USER_ROLE_ID'));
        $seller_logo = dispatch(new ConvertRoundImage($job_data));
        dispatch(new EmailHeaderImage($share['id']));
        return back();
    }

    /* Resend Invite From James */

    public function resend_invite_from_james(Request $request) {
        $data = $request->all();
        $spaceid = $data['spaceid'];
        $userid = $data['user_id'];
        $space_data = Space::where('id', $spaceid)->get()->toArray();
        $share['seller_processed_logo'] = $space_data['0']['seller_processed_logo'];
        $share['buyer_processed_logo'] = $space_data['0']['buyer_processed_logo'];
        $share['share_name'] = $space_data['0']['share_name'];
        (new MailerController)->spaceInvitation($userid, $spaceid, $share);
    }

    /* Campaign trigger */

    public function campaignTrigger() {
        ini_set('max_execution_time', -1);
        $users = $fields = array();
        $i = 0;
        $handle = @fopen(env('xls_file_url'), "r");
        if ($handle) {
            while (($row = fgetcsv($handle, 4096)) !== false) {
                if (empty($fields)) {
                    $fields = $row;
                    continue;
                }
                foreach ($row as $k => $value) {
                    $users[$i][$fields[$k]] = $value;
                }
                $i++;
            }
            if (!feof($handle)) {
                echo "Error: unexpected fgets() fail\n";
            }
            fclose($handle);
        }
        foreach ($users as $user_key => $user) {
            echo($this->inviteUserCampaign($user));
        }
        return $users;
    }

    public function inviteUserCampaign($user_data = null) {
        $requested_data['share_id'] = env('share_id');
        $requested_data['user_id'] = env('user_id');
        $requested_data['sender_email'] = env('sender_email');
        if (!sizeOfCustom($requested_data))
            abort(404);
        if (!strlen(trim($user_data['to'])))
            return 0;
        $share = Space::with('BuyerName', 'SellerName')->find($requested_data['share_id']);
        $user = User::where("email", 'ilike', $user_data['to'])->get();
        $sender_user = User::getUserIdFromEmail($requested_data['sender_email']);
        if (!sizeOfCustom($user)) {
            $data['user']['user_type_id'] = 3;
            $data['user']['password'] = Hash::make('password');
            $data['user']['email'] = $user_data['to'];
            $data['user']['first_name'] = $user_data['first_name'];
            $data['user']['last_name'] = $user_data['last_name'];
            $user[0] = User::create($data['user']);
        } else {
            $data['user']['first_name'] = $user_data['first_name'];
            $data['user']['last_name'] = $user_data['last_name'];
            User::where('id', $user[0]->id)->update($data['user']);
        }
        $space_user = SpaceUser::getSpaceUserInfo($requested_data['share_id'], $user[0]->id);
        if (sizeOfCustom($space_user)) {
            return 0;
        }
        if ((!empty($share['seller_processed_logo'])) && (!empty($share['company_seller_logo']))) {
            $company_seller_logo = $share['seller_processed_logo'];
        } elseif ((empty($share['seller_processed_logo'])) && (!empty($share['company_seller_logo']))) {
            $company_seller_logo = $share['company_seller_logo'];
        } else {
            $company_seller_logo = env('APP_URL').'/images/login_user_icon.png';
        }
        if ((!empty($share['buyer_processed_logo'])) && (!empty($share['company_buyer_logo']))) {
            $company_buyer_logo = $share['buyer_processed_logo'];
        } elseif ((empty($share['buyer_processed_logo'])) && (!empty($share['company_buyer_logo']))) {
            $company_buyer_logo = $share['company_buyer_logo'];
        } else {
            $company_buyer_logo = env('APP_URL').'/images/login_user_icon.png';
        }
        $data['mail']['to'] = $user[0]->email;
        $data['mail']['link'] = "/registeruser/" . $user[0]->id . "/" . $requested_data['share_id'];
        $data['mail']['companyx'] = $share->toArray()['seller_name']['company_name'];
        $data['mail']['companyy'] = $share->toArray()['buyer_name']['company_name'];
        $data['mail']['sender_first_name'] = ucfirst($sender_user->first_name);
        $data['mail']['sender_last_name'] = ucfirst($sender_user->last_name);
        $data['mail']['receiver_last_name'] = ucfirst($user[0]->last_name);
        $data['mail']['receiver_first_name'] = ucfirst($user[0]->first_name);
        $data['mail']['share_name'] = $share['share_name'];
        $data['mail']['company_seller_logo'] = $company_seller_logo;
        $data['mail']['company_buyer_logo'] = $company_buyer_logo;

        if (!isset($data['resend_mail']))
            $space_user_id = SpaceUser::spaceUserCreated($requested_data['share_id'],$user[0]->id,3,$data);
        (new Logger)->log([
            'user_id' => $requested_data['user_id'],
            'description' => Config::get('constants.SEND_INVITATION'),
            'metadata' => ['invited_by' => $requested_data['user_id'], 'invited_to' => $user[0]->id]
            ]);
        dispatch(new SendCampaignNotification($user[0]->id, $requested_data['share_id'], 'email.campaign_user_invitation', $data));
        return;
    }

    private function shareSessiondata($input_data) {
        if(!isset($input_data['share_id'])) abort(404);
        (new UserController)->updateSpaceSessionData($input_data['share_id']);
        $session_space_info = Session::get('space_info')->toArray();
        $session_space_info['sender_user']['id'] = Auth::user()->id;
        $session_space_info['sender_user']['email'] = Auth::user()->email;
        $session_space_info['sender_user']['first_name'] = Auth::user()->first_name;
        $session_space_info['sender_user']['last_name'] = Auth::user()->last_name;
        return $session_space_info;
    }

    public function inviteAdminUser(Request $request) {
        $input = $request->all();
        $share = Space::find($input['share_id']); 
        if(!isset($share['metadata']['rule'])) {
            $new_data =  array (
              'name' => 'rule[]',
              'value' => explode('@',Auth::user()->email)[1]
            );
            $data['metadata']['rule'][] = $new_data;
            $data['metadata']['rule'] = strtolower(json_encode($data['metadata']['rule']));
            $data['metadata']['rule'] = json_decode($data['metadata']['rule'], true);
            $data['metadata'] = ['rule'=>filterArray($data['metadata']['rule'], 'value')];
            $data['metadata'] = json_encode($data['metadata']);
            Space::updateSpaceById($input['share_id'], $data);
        }
        $session_space_info = $this->shareSessiondata($request->all());
        $tags = ""; 
        $share = Space::find($input['share_id']);

        if ($share['domain_restriction']) {
            if(isset($share['metadata']['rule'])){ 
                foreach ($share['metadata']['rule'] as $value)
                    $metadata_rules[] = '@' . $value['value'];
                $tags = implode(', ', $metadata_rules);
            }

        $common = false;
        if(sizeOfCustom($input['admin_invite']) > 0 && isset($share['metadata']['rule'])) {
            $domain_array = [];
            foreach ($share['metadata']['rule'] as $key => $value)
                $domain_array[] = strtolower($value['value']);

            foreach($input['admin_invite'] as $index => $email) {
                if($email && sizeOfCustom($domain_array) > 0) {
                    $invitee_email = explode("@", $email); 
                    if (!in_array(strtolower($invitee_email[1]), $domain_array))
                        return ['code' => 400,'key' => $index+1, 'message' => 'Invalid Email Domain: your Client Share has been locked down to ' . $tags];
                }
            }
         }
        }

        if(sizeOfCustom($input['admin_invite']) > 0) {
            foreach($input['admin_invite'] as $index => $email) {
                if($email) {
                    $user_data = User::getUserByEmail($email);
                    if($user_data) {
                        $space_user = SpaceUser::getSpaceUserInfo($input['share_id'], $user_data->id);
                        if (sizeOfCustom($space_user) && !isset($input['resend_mail'])) {
                            if (!isset($space_user[0]['metadata']['invitation_code']) 
                                || ($space_user[0]['metadata']['invitation_code'] == config('constants.COUNT_ONE')))
                                return ['code' => Config::get('constants.BAD_REQUEST'),'key'=>$index+1, 'message' => trans('messages.error.already_member')];

                            if (!isset($space_user[0]['metadata']['invitation_code']) 
                                || ($space_user[0]['metadata']['invitation_code'] == config('constants.INACTIVE_USER')))
                                return ['code' => Config::get('constants.BAD_REQUEST'),'key'=>$index+1, 'message' => trans('messages.error.already_pending')];
                        }
                    }
                }
            }
        }

        if(sizeOfCustom($input['admin_invite']) > 0) {
            $user_array = array();
            foreach($input['admin_invite'] as $index => $email) {
                if($email) {
                    $input['user']['email'] = $email;
                    $input['mail']['to'] = $email;
                    $response = $this->processInvitation($input, $session_space_info, null, $index+1);
                    if(is_array($response) && isset($response['code']) && $response['code'] == 400)
                        return $response;
                }
            }
        }
        $space_admin = (new SpaceUser)->getSpaceAdmin($input['share_id']);
        return ['code' => 200, 'space_admin' => $space_admin];
    }

    public function inviteUser(Request $request) {
        $session_space_info = $this->shareSessiondata($request->all());
        return $this->processInvitation($request->all(), $session_space_info);
    }

    /**/
    public function processInvitation($data=null, $session_space_info, $user_invite=null, $index = null) {
        $share = Space::find($data['share_id']);
        $metadata_rules = array();
        $user_type_id = $data['user']['user_type_id'] ?? UserType::USER_TYPE['user'];
        if (isset($data['user_type'])) {
            if (!in_array($data['user_type'], array_keys(UserType::USER_TYPE))) {
                return ['code' => Config::get('constants.BAD_REQUEST'), 'key' => $index, 'message' => "You entered an invalid user type"];
            }
            $user_type_id = $data['user']['user_type_id'] = UserType::USER_TYPE[$data['user_type']];
        }
        $tags = "";
        if ($share['domain_restriction'] && !isset($data['call_via_bulk_invitation']) ) {
            if(isset($share['metadata']['rule'])){
                foreach ($share['metadata']['rule'] as $v) {
                    $metadata_rules[] = '@' . $v['value'];
                }
                $tags = implode(', ', $metadata_rules);
            }
        }
        $user_name = SpaceUser::with('User')->where('space_id', $data['share_id'])->where('user_type_id', 2)->orderBy('updated_at', 'DESC')->get()->toArray();
        $user_emails = array();
        foreach ($user_name as $val) {
            $user_emails[] = $val['user']['email'];
        }
        $username = implode(', ', $user_emails);
        
        if(isset($data['user']['onboarding'])) {
            $validator = Validator::make($data, [
                    'user.email' => array('required', 'email', 'not_in:' . Config::get('constants.email.restricted_emails')),
                ], [
                    'required' => 'This field is required',
                    'email' => 'Invalid email address.',
                    'user.email.not_in' => 'Email cannot be used for share.'
                ]
            );
        } else {
            $validator = Validator::make($data, [
                'user.first_name' => array('required','max:25'),
                'user.last_name' => array('required','max:25'),
                    'user.email' => array('required', 'email', 'not_in:' . Config::get('constants.email.restricted_emails')),
                ], [
                    'required' => 'This field is required',
                    'user.first_name.max' => 'First name cannot be greater than 25 characters',
                    'user.last_name.max' => 'Last name cannot be greater than 25 characters',
                    'email' => 'Invalid email address.',
                    'user.email.not_in' => 'Email cannot be used for share.'
                ]
            );
        }

        if ($validator->fails())
            return ['code' => HttpResponse::HTTP_UNAUTHORIZED, 'message' => $validator->errors()];

        if ($share['domain_restriction'] && !isset($data['call_via_bulk_invitation']) && !isset($data['resend_mail'])) {
            $invitee_email = explode("@", $data['user']['email']);
            $common = false;
            if(isset($share['metadata']['rule'])){
                foreach ($share['metadata']['rule'] as $key => $value) {
                    if (strtolower($value['value']) == strtolower($invitee_email[1])) $common = true;
                }
            }
            if (!$common) {
                $validator->getMessageBag()->add('user.email', 'Invalid Email Domain: your Client Share has been locked down to ' . $tags . '. <br/>If you wish to add a domain please email an administrator: ' . $username . '');
                return ['code' => 401, 'message' => $validator->errors()];
            }
        }
        if ($data['user']['email'] == $session_space_info['sender_user']['email']) {
            // if already user is memeber of space
            return ['code' => 400, 'message' => "You are already member of this Share. "];
        }
        $user = User::where("email", 'ilike', $data['user']['email'])->get();
        if (!sizeOfCustom($user)) {
            $validator_unique = Validator::make($data, [
                'user.email' => 'unique:users,email|required|email',
            ]);
            if ($validator_unique->fails()) {
                return ['code' => 401, 'message' => $validator_unique->errors()];
            }
            $data['user']['user_type_id'] = $user_type_id;
            $data['user']['password'] = Hash::make('password');
            $user[0] = User::create($data['user']);
        } else {
            if ($user[0]->registration_status == '0') {
                $data['user_name_change']['first_name'] = $data['user']['first_name'];
                $data['user_name_change']['last_name'] = $data['user']['last_name'];
                $chk_user_status = SpaceUser::where('user_id', $user[0]->id)->where('space_id', $data['share_id'])->get();
                if (isset($chk_user_status[0]) && $chk_user_status[0]->metadata['invitation_code'] == 0) {
                    User::where('id', $user[0]->id)->update($data['user_name_change']);
                }
            }
        }

        $space_user = SpaceUser::getSpaceUserInfo($data['share_id'], $user[0]->id);
        if (sizeOfCustom($space_user) && !isset($data['resend_mail'])) {
            if (!isset($space_user[0]['metadata']['invitation_code']) || ($space_user[0]['metadata']['invitation_code'] == 1))
                return ['code' => Config::get('constants.BAD_REQUEST'),'key'=>$index, 'message' => "This user is already a member of this Client Share"];

            if (!isset($space_user[0]['metadata']['invitation_code']) || ($space_user[0]['metadata']['invitation_code'] == 0))
                return ['code' => Config::get('constants.BAD_REQUEST'),'key'=>$index, 'message' => "This user already has a pending invite for this Client Share. You can resend the invitation via Pending Invites in Settings"];
        }
        if ((!empty($session_space_info['seller_processed_logo'])) && (!empty($session_space_info['company_seller_logo']))) {
            $company_seller_logo = composeEmailURL($session_space_info['seller_processed_logo']);
        } elseif ((empty($session_space_info['seller_processed_logo'])) && (!empty($session_space_info['company_seller_logo']))) {
            $company_seller_logo = composeEmailURL($session_space_info['company_seller_logo']);
        } else {
            $company_seller_logo = env('APP_URL').'/images/login_user_icon.png';
        }
        if ((!empty($session_space_info['buyer_processed_logo'])) && (!empty($session_space_info['company_buyer_logo']))) {
            $company_buyer_logo = composeEmailURL($session_space_info['buyer_processed_logo']);
        } elseif ((empty($session_space_info['buyer_processed_logo'])) && (!empty($session_space_info['company_buyer_logo']))) {
            $company_buyer_logo = composeEmailURL($session_space_info['company_buyer_logo']);
        } else {
            $company_buyer_logo = env('APP_URL').'/images/login_user_icon.png';
        }
        
        $data['mail']['subject'] = htmlspecialchars_decode($data['user']['subject']);
        $data['mail']['link'] = "/registeruser/" . $user[0]->id . "/" . $data['share_id'];
        $data['mail']['companyx'] = $session_space_info['seller_name']['company_name'];
        $data['mail']['companyy'] = $session_space_info['buyer_name']['company_name'];
        $data['mail']['sender_first_name'] = ucfirst($session_space_info['sender_user']['first_name']);
        $data['mail']['sender_last_name'] = ucfirst($session_space_info['sender_user']['last_name']);
        $data['mail']['receiver_last_name'] = ucfirst($data['user']['last_name']);
        $data['mail']['receiver_first_name'] = empty($data['user']['first_name']) ? '' : ' '.ucfirst($data['user']['first_name']) ;
        $data['mail']['share_name'] = $session_space_info['share_name'];
        $data['mail']['company_seller_logo'] =  $share['seller_circular_logo']; 
        $data['mail']['company_buyer_logo'] = $share['buyer_circular_logo']; ;
        $data['mail']['colleagues'] = $share->getColleagueForInvitaionMail($session_space_info['sender_user']['id']);
        $data['mail']['sender_image'] = User::find($session_space_info['sender_user']['id'])->getImageOrInitialsEmail();
        $data['mail']['supplier_name'] = $share->SellerName->company_name;
        $data['mail']['unsubscribe_share'] = env('APP_URL') . "/setting/" . $data['share_id'] . "?email=".base64_encode($data['user']['email']). '&alert=true&via_email=1&notification=1&tab_name=notifications-tab';
        if (!isset($data['resend_mail'])){
            $space_user_id = SpaceUser::create(['created_by' =>$session_space_info['sender_user']['id'], 'space_id' => $data['share_id'], 'user_id' => $user[0]->id, 'user_type_id' => $user_type_id, 'metadata' => ["invitation_status" => "Pending from User", "invitation_code" => 0, 'mail_data' => $data]]);
        }


        $delete_cancel_invited_user = SpaceUser::where('space_id', $data['share_id'])->where('user_id', $user[0]->id)->where('metadata->invitation_code', '-1');
        $delete_cancel_invited_user->delete();

        (new Logger)->log([
            'user_id' => $session_space_info['sender_user']['id'],
            'description' => Config::get('constants.SEND_INVITATION'),
            'space_id' => $data['share_id'],
            'metadata' => ['invited_by' => $session_space_info['sender_user']['id'], 'invited_to' => $user[0]->id]
        ]);
        $invitation_id = SpaceUser::getActiveSpaceUser($data['share_id'], $session_space_info['sender_user']['id'], 'first');
        $data['invitation_id'] = $invitation_id;
        if (!isset($data['resend_mail'])) {
            $data['invitation_id']['space_user_id']=$space_user_id['id'];
        }else{
           $data['invitation_id']['space_user_id']=$space_user[0]['id'];
        }
        $event_tag = isset($data['resend_mail']) ? Logger::MIXPANEL_TAG['resend_invite'] : Logger::MIXPANEL_TAG['invite'];

        if (!isset($data['resend_mail'])){
            (new Logger)->mixPannelInitial($session_space_info['sender_user']['id'], $data['share_id'], $event_tag);
        }
        (new Logger)->mixPannelInitial($session_space_info['sender_user']['id'], $data['share_id'], $event_tag);
         if(isset($data['user']['invite']) && $data['user']['invite']==true){
            return true;
        }elseif($user_invite != Config::get('constants.INVITE_EXPORT')) {
            return (new MailerController)->userInvitation($user[0]->id, $data['share_id'], 'email.user_invitation', $data);
        }
    }

    public function getTopPost(Request $request) {
        $data = $request->all();
        $data['space_id'] = $data['space_id']??Session::get('space_info')['id'];
        $company = '';
        if (isset($data['company'])) {
            if (!empty($data['company'])) {
                $comapny = Company::select('id')->where('company_name', $data['company'])->first();
                if(!empty($comapny->id))
                {
                 $company = $comapny->id;
                }
            }
        }
        if (!empty($data['month']) && !empty($data['year'])) {
            $posts = $this->topPosts($data['month'], $data['year'], $data['space_id'], Auth::User()->id, $company);
            return view('pages/top_post_ajax', ['toppost' => $posts, 'month' => $data['month'], 'year' => $data['year'],'space_id'=>$data['space_id']]);
        }
    }

    public function topPosts($month, $year, $spaceid, $login_user, $company = NULL) {
        $post_data = [
            'space_id' =>$spaceid,
            'company_id' => $company,
            'limit' =>Config::get('constants.TOTAL_POSTS_FETCH_COUNT'),
            'month' =>$month,
            'year' =>$year,
            'user_id' => $login_user
        ];
        $posts = empty(trim($company)) ? Post::getTopPosts($post_data) : Post::getTopPostsByCompany($post_data);

        $top_posts = array();
        foreach ($posts as $post) {
            $post_details = Post::getPostWithUser($post->id);
            array_push($top_posts, array('post_details' => $post_details, 'post_score' => $post->score));
        }
        return $top_posts;
    }
    
    public function getEditPostTemplate(Request $request, $id = null, $post_id= null, $notification_id= null) {
        $total_post_to_fetch = Config::get('constants.TOTAL_POSTS_FETCH_COUNT');
        if (!$id) {
            $id = $this->index($request);
            if (!(new \App\Helpers\Generic)->check_uuid_format($id)) {
                return $id;
            }
        }
        $month = date('n');
        $year = date('Y');
        $get_landing_data = $request->all();
        if (isset($get_landing_daats['email']) && base64_decode($get_landing_data['email']) != auth::user()->email) {
            if (isset($get_landing_data['feedback'])) {
                return redirect('logout?spaceid='.$id.'&'.$_SERVER['QUERY_STRING'].'&status=logout&from=feedback' );
            }elseif(isset($get_landing_data['like'])){
               return redirect('logout?spaceid=' . $id . '&postid=' . $post_id . '&notiid=' . $notification_id . '&email=' . $get_landing_data['email'] . '&status=logout&from=like');
            } else {
                return redirect('logout?spaceid=' . $id . '&postid=' . $post_id . '&notiid=' . $notification_id . '&email=' . $get_landing_data['email'] . '&status=logout&from=post');
            }
        }
        $space_user_check = SpaceUser::getSpaceUserInfo($id,Auth::user()->id);
        if (!sizeOfCustom($space_user_check)) {
            User::updateUser(Auth::user()->id, ['active_space' => '{"":""}']);
            abort(404);
        }
        $this->setClientShareList();

        $space_info = Space::getSpaceBuyerSeller($id);
        $space_user = SpaceUser::getSpaceUserRole($id,Auth::user()->id);
        $space_info['space_user'] = $space_user;
        Session::put('space_info', $space_info);
        $space_company = Company::getCompanyById($space_user[0]['user_company_id']);
        Session::put('space_company', $space_company??'');
        if (!$space_info)
            abort(404);

        $user_id_get = Auth::User()->toArray();
        $user_id = $user_id_get['id'];
        $space_data = Space::spaceById($id, 'get');


        $post_data = [];
        $case_post = Config::get('constants.POST_NOT_EMPTY_CASE');

        $groups = SpaceGroups::invitedSpaceUserGroups($space_data[0]['id'],Auth::user()->id);
        $approve_user_ref = User::getApprovedUsers($id);
        $approve_user = SpaceUser::spaceUsersWithSubCompany($id);
        $companies_dictonary = Company::getAllCompaniesById(array_unique(array_column($approve_user, 'company_id')), ['id', 'company_name']);
        usort($approve_user, function($first_name_value, $second_name_value) {
            $compare_first_name = $first_name_value['user']['first_name'];
            $compare_second_name = $second_name_value['user']['first_name'];
            $comparison_output = strcasecmp($compare_first_name, $compare_second_name);
            if ($comparison_output == 0) {
                return 0;
            }
            if ($comparison_output > 0) {
                return 1;
            }
            if ($comparison_output < 0) {
                return -1;
            }
        });

        $community_user = SpaceUser::getSpaceUsersInvited($id);
        $previous_space_data = SpaceUser::userProfileNotNull(Auth::user()->id);
        if (sizeOfCustom($community_user) > Config::get('constants.COMMUNITY_USERS_COUNT')) {
            $community_user[] = shuffle($community_user);
            $community_user = array_slice($community_user, 0, Config::get('constants.COMMUNITY_USERS_COUNT'));
        }

        $get_usertype = Space::getUserSpace($user_id,$id);

        $companies_info  = Company::getCompanysById([$space_info->company_seller_id, $space_info->company_buyer_id]);
        $seller_name = $companies_info[$space_info->company_seller_id];
        $buyer_name = $companies_info[$space_info->company_buyer_id];

        $buyerseller_name = array_merge($buyer_name, $seller_name);

        $check_buyer = (new FeedbackController)->checkBuyer($id, $user_id);
        $give_feedback = (new FeedbackController)->giveFeed($id, $user_id);
        $feedback_status = (new FeedbackController)->feedbackStatus($id);
        $s3_form_details = (new Aws)->uploadClientSideSetup();
        $space_info['days_left'] = (Config::get('constants.feedback.feedback_opened_till') - Carbon::now()->day) + 1;
        $space_info['quater'] = Carbon::parse($space_info['feedback_status_to_date'])->subMonth(3)->format('F Y') . ' - ' .Carbon::parse($space_info['feedback_status_to_date'])->subMonth(1)->format('F Y');

        $pinned_post_count = Post::pinPostCount($space_data[0]['id']);
        $media = Media::bySpaceId($space_data[0]['id'], 'get');

        User::updateLastAccessedSpace($id, Auth::user()->id);

        $rendered_view = view('posts/edit_post_template_ajax', ['companies_dictonary'=>$companies_dictonary, 'media_data' => $media,'s3FormDetails' => $s3_form_details, 'previous_space_data' => $previous_space_data, 'postdata' => $post_data, 'data' => $space_info, 'space_user' => $space_info['space_user'], 'spaceid' => $id, 'approve_user' => $approve_user, 'approve_user_ref' => $approve_user_ref, 'user_role' => $get_usertype, 'buyerseller' => $buyerseller_name, 'community_user' => $community_user, 'case_post' => $case_post, 'post_show' => $total_post_to_fetch, 'toppost' => [], 'checkBuyer' => $check_buyer, 'giveFeedback' => $give_feedback, 'feedback_status' => $feedback_status, 'load_type' => 'simple', 'group' => $groups, 'buyer_info' => $buyer_name[0],'pinned_post_count'=>$pinned_post_count??0]);

        return $rendered_view;
    }
    public function getAddPostTemplate(Request $request, $id = null, $post_id= null, $notification_id= null) {
        $total_post_to_fetch = Config::get('constants.TOTAL_POSTS_FETCH_COUNT');
        if (!$id) {
            $id = $this->index($request);
            if (!(new \App\Helpers\Generic)->check_uuid_format($id)) {
                return $id;
            }
        }
        $month = date('n');
        $year = date('Y');
        $get_landing_data = $request->all();
        if (isset($get_landing_daats['email']) && base64_decode($get_landing_data['email']) != auth::user()->email) {
            if (isset($get_landing_data['feedback'])) {
                return redirect('logout?spaceid='.$id.'&'.$_SERVER['QUERY_STRING'].'&status=logout&from=feedback' );
            }elseif(isset($get_landing_data['like'])){
               return redirect('logout?spaceid=' . $id . '&postid=' . $post_id . '&notiid=' . $notification_id . '&email=' . $get_landing_data['email'] . '&status=logout&from=like');
            } else {
                return redirect('logout?spaceid=' . $id . '&postid=' . $post_id . '&notiid=' . $notification_id . '&email=' . $get_landing_data['email'] . '&status=logout&from=post');
            }
        }
        $space_user_check = SpaceUser::getSpaceUserInfo($id,Auth::user()->id);
        if (!sizeOfCustom($space_user_check)) {
            User::updateUser(Auth::user()->id, ['active_space' => '{"":""}']);
            abort(404);
        }
        $this->setClientShareList();

        $space_info = Space::getSpaceBuyerSeller($id);
        $space_user = SpaceUser::getSpaceUserRole($id,Auth::user()->id);
        $space_info['space_user'] = $space_user;
        Session::put('space_info', $space_info);
        $space_company = Company::getCompanyById($space_user[0]['user_company_id']);
        Session::put('space_company', $space_company??'');
        if (!$space_info)
            abort(404);

        $user_id_get = Auth::User()->toArray();
        $user_id = $user_id_get['id'];
        $space_data = Space::spaceById($id, 'get');


        $post_data = [];
        $case_post = Config::get('constants.POST_NOT_EMPTY_CASE');

        $groups = SpaceGroups::invitedSpaceUserGroups($space_data[0]['id'],Auth::user()->id);
        $approve_user_ref = User::getApprovedUsers($id);
        $approve_user = SpaceUser::spaceUsersWithSubCompany($id);
        $companies_dictonary = Company::getAllCompaniesById(array_unique(array_column($approve_user, 'company_id')), ['id', 'company_name']);
        usort($approve_user, function($first_name_value, $second_name_value) {
            $compare_first_name = $first_name_value['user']['first_name'];
            $compare_second_name = $second_name_value['user']['first_name'];
            $comparison_output = strcasecmp($compare_first_name, $compare_second_name);
            if ($comparison_output == 0) {
                return 0;
            }
            if ($comparison_output > 0) {
                return 1;
            }
            if ($comparison_output < 0) {
                return -1;
            }
        });

        $community_user = SpaceUser::getSpaceUsersInvited($id);
        $previous_space_data = SpaceUser::userProfileNotNull(Auth::user()->id);
        if (sizeOfCustom($community_user) > Config::get('constants.COMMUNITY_USERS_COUNT')) {
            $community_user[] = shuffle($community_user);
            $community_user = array_slice($community_user, 0, Config::get('constants.COMMUNITY_USERS_COUNT'));
        }

        $get_usertype = Space::getUserSpace($user_id,$id);

        $companies_info  = Company::getCompanysById([$space_info->company_seller_id, $space_info->company_buyer_id]);
        $seller_name = $companies_info[$space_info->company_seller_id];
        $buyer_name = $companies_info[$space_info->company_buyer_id];

        $buyerseller_name = array_merge($buyer_name, $seller_name);

        $check_buyer = (new FeedbackController)->checkBuyer($id, $user_id);
        $give_feedback = (new FeedbackController)->giveFeed($id, $user_id);
        $feedback_status = (new FeedbackController)->feedbackStatus($id);
        $s3_form_details = (new Aws)->uploadClientSideSetup();
        $space_info['days_left'] = (Config::get('constants.feedback.feedback_opened_till') - Carbon::now()->day) + 1;
        $space_info['quater'] = Carbon::parse($space_info['feedback_status_to_date'])->subMonth(3)->format('F Y') . ' - ' .Carbon::parse($space_info['feedback_status_to_date'])->subMonth(1)->format('F Y');

        $pinned_post_count = Post::pinPostCount($space_data[0]['id']);
        $media = Media::bySpaceId($space_data[0]['id'], 'get');

        User::updateLastAccessedSpace($id, Auth::user()->id);

        $rendered_view = view('posts/add_post_template_ajax', ['companies_dictonary'=>$companies_dictonary, 'media_data' => $media,'s3FormDetails' => $s3_form_details, 'previous_space_data' => $previous_space_data, 'postdata' => $post_data, 'data' => $space_info, 'space_user' => $space_info['space_user'], 'spaceid' => $id, 'approve_user' => $approve_user, 'approve_user_ref' => $approve_user_ref, 'user_role' => $get_usertype, 'buyerseller' => $buyerseller_name, 'community_user' => $community_user, 'case_post' => $case_post, 'post_show' => $total_post_to_fetch, 'toppost' => [], 'checkBuyer' => $check_buyer, 'giveFeedback' => $give_feedback, 'feedback_status' => $feedback_status, 'load_type' => 'simple', 'group' => $groups, 'buyer_info' => $buyer_name[0],'pinned_post_count'=>$pinned_post_count??0]);

        return $rendered_view;
    }


    public function show(Request $request, $space_id = null, $post_id= null, $notification_id= null) {
        $id = $space_id;
        $total_post_to_fetch = Config::get('constants.TOTAL_POSTS_FETCH_COUNT');
        if (!$id) {
            $id = $this->index($request);
            if (!(new \App\Helpers\Generic)->check_uuid_format($id))
                return $id;
        } else if (!(new SpaceUser)->getActiveOrPendingSpaceUser($id, Auth::user()->id, 'count') && !isset($request->linkedin)) {
            abort(404);
        } else {
            $space_info = Space::getSpaceBuyerSeller($id);
            if(empty($space_info))
                return redirect('/');
        }
        
        $get_landing_data = $request->all();
        if (isset($get_landing_data['email']) && base64_decode($get_landing_data['email']) != auth::user()->email) {
            if (isset($get_landing_data['feedback']))
                return redirect('logout?spaceid='.$id.'&'.$_SERVER['QUERY_STRING'].'&status=logout&from=feedback' );
            elseif(isset($get_landing_data['like']))
               return redirect('logout?spaceid=' . $id . '&postid=' . $post_id . '&notiid=' . $notification_id . '&email=' . $get_landing_data['email'] . '&status=logout&from=like');
            else
                return redirect('logout?spaceid=' . $id . '&postid=' . $post_id . '&notiid=' . $notification_id . '&email=' . $get_landing_data['email'] . '&status=logout&from=post');
        }
        
        $this->setClientShareList();
        $space_info = Space::getSpaceBuyerSeller($id);
        $space_user = SpaceUser::getSpaceUserRole($id,Auth::user()->id);
        if(!$space_user || !$space_info || count($space_user) <= 0)
            abort(404);
        $space_info['space_user'] = $space_user;
        Session::put('space_info', $space_info);
        $space_company = Company::getCompanyById($space_user[0]['user_company_id']);
        Session::put('space_company', $space_company??'');

        if($space_info->ip_restriction && !$this->ipChecker($space_info, getRealIpAddr())) {
            return view('errors/custom_errors.generic');
        }
        if (!$space_info){
            abort(404);
        }
        $user_id_get = Auth::User()->toArray();
        $user_id = $user_id_get['id'];
        
        $case_post = Config::get('constants.POST_NOT_EMPTY_CASE');
        $give_feedback = (new FeedbackController)->giveFeed($id, $user_id);
        
        $space_info['days_left'] = (Config::get('constants.feedback.feedback_opened_till') - Carbon::now()->day) + 1;
        if(!empty($space_info['feedback_status_to_date'])){
            $space_info['quater'] = Carbon::parse($space_info['feedback_status_to_date'])->subMonth(3)->format('F Y') . ' - ' .Carbon::parse($space_info['feedback_status_to_date'])->subMonth(1)->format('F Y');
            }
        User::updateLastAccessedSpace($id, Auth::user()->id);
        $is_single_post = isset($post_id) && !is_null($post_id) ? 1 : 0;
        $feedback_status = (new FeedbackController)->feedbackStatus($id);

        $user_current_quater_feedback = (new Feedback)->userCurrentQuaterFeedback($id);
        $next_due = $space_info['feedback_status_to_date'];
        $next_due_month = Carbon::parse($next_due)->month;
        $next_due_year = Carbon::parse($next_due)->year;
        $post_dated_or_isseller = (date('n') == Carbon::now()->month) && (date('Y') == Carbon::now()->year)?false:true;
        $check_buyer = checkBuyerSeller($id, Auth::user()->id);
        $feedback_opened_till = Config::get('constants.feedback.feedback_opened_till');
        $post_dated_or_isseller = $check_buyer == 'seller'?true:$post_dated_or_isseller;
        $feedback_on_off_status = !$post_dated_or_isseller && !sizeOfCustom($user_current_quater_feedback)
                                  && $next_due_month == Carbon::now()->month && $next_due_year == Carbon::now()->year
                                  && $feedback_opened_till-Carbon::now()->day >= 0;

        if($is_single_post) {
            PostViews::logSingleView([
                'user_id' => $user_id,
                'post_id' => $post_id,
                'space_id' => $id
            ]);
        }
        $rendered_view = view('posts/admin_landing',[
            'postdata' => [], 
            'data' => $space_info, 
            'space_user' => $space_info['space_user'], 
            'space_id' => $id, 
            'case_post' => $case_post, 
            'post_show' => $total_post_to_fetch, 
            'give_feedback' => $give_feedback, 
            'load_type' => 'simple',
            'single_post_id' => $post_id ?? 0,
            'single_post_view' => $is_single_post,
            'feedback_status' => $feedback_status,
            'feedback_on_off_status' => $feedback_on_off_status
        ]);
        return $rendered_view;
    }

    public function community(Request $request, $space_id){
        return SpaceUser::getSpaceUsers($space_id);
    }

    public function communityMember(Request $request){
       if($request['space_id']){
           $space_id = $request['space_id'];
           $total_member = SpaceUser::getSpaceUserActiveCount($space_id);
           $space_members = SpaceUser::communityMemberProfileImage($space_id);
           $space_user_members = [];
           foreach($space_members as $key => $space_member){
                $space_user_members[$key]['profile_image'] = wrapUrl(composeUrl($space_member['profile_thumbnail']??$space_member['profile_image']));
                $space_user_members[$key]['company_id'] = $space_member['company_id'];
                $space_user_members[$key]['profile_image_url'] = $space_member['profile_image_url'];
           }
           $total_pending_invitations = SpaceUser::getPendingInvitationsCount($space_id);
           return ['result'=>true,'total_member'=>$total_member,'community_members'=>$space_user_members, 'total_pending_invitations'=>$total_pending_invitations];
       }
       return ['result'=>false];
    }

    public function saveQuickLinks(Request $request){
         $form_data = $request->all();
         QuickLinks::saveLinks($form_data);
         $quick_links_record = QuickLinks::getQuickLinks($form_data['space_id']);
         return ['result'=>true,'quick_links'=>$quick_links_record];
    }

    public function QuickLinks(Request $request){
        if(isset($request['space_id']) && !empty(trim($request['space_id'])) && checkUuidFormat(trim($request['space_id']))){
          $quick_links_record = QuickLinks::getQuickLinks($request['space_id']);
          return ['result'=>true,'quick_links'=>$quick_links_record];
        }
        return ['result'=>false,'quick_links'=>''];
    }

    public function fileViewCount(Request $request){
          $request['filters'] = "file_name=&date_range=&post_subject=";
          $request['limit'] = 'all';
          $files_data = PostMedia::PostFiles($request->all(), Auth::user()->id);
          return ['count'=>sizeOfCustom($files_data)];
    }

    public function executiveSummary($space_id){
        $media = Media::bySpaceId($space_id, 'get');
        $s3_form_details = (new Aws)->uploadClientSideSetup();
        return response()->json(['result'=>true, 'media'=>$media, 's3_form_details'=>$s3_form_details]);
    }

    public function saveTwitterHandles(Request $request){
         $form_data = $request->all();
         if(!empty($form_data)){
            if(isset($form_data['twitter_handles']))
            {
                foreach ($form_data['twitter_handles'] as $key => $value)
                    if($value != '' && $value != '@'){
                        $twitter_array['request_url'] = 'users/show';
                        $twitter_array['option']['screen_name']=$value;
                        $response = $this->checkTwitter($twitter_array);
                        if(isset($response->getData()->error) && $request->ajax())
                            return ['result'=>false,'error'=>$response->getData()->error,'key'=>$key];
                        $form_data['twitter_handles'][$key] = (substr( $value, 0, 1 ) != "@")?'@'.$value:$value;
                    } else {
                        $form_data['twitter_handles'][$key] = NULL;
                    }
            }
            Space::saveTwitterHandles($form_data);
          }
         if($request->ajax())
            return ['result'=>true];
         return redirect('clientshare/'.$form_data['space_id']);
    }

    public function twitterHandles(Request $request){
        if(!empty($request['space_id']) && checkUuidFormat(trim($request['space_id']))){
          $twitter_handles = Space::getTwitterHandles($request['space_id']);
          if(!empty($twitter_handles))
              $twitter_handles = $twitter_handles[0]['twitter_handles'];
          else
              return false;

          return view('posts.admin_landing_twitter_feed', compact(['twitter_handles']))->render();
        }
        return false;
    }

    public function singlePostView(Request $request, $space_id, $post_id, $notification_id=null){
    if(isset($request->like)) (new PostHelper)->likePost($post_id, Auth::user()->id);
    $this->setClientShareList();

    $total_post_to_fetch = Config::get('constants.TOTAL_POSTS_FETCH_COUNT');
    $space_data[] = Space::spaceById($space_id, 'firstOrFail');
    $vis_post = Post::postAllVisibility($post_id,Auth::user()->id);
    $post_data = Post::postWithEndorseUser($post_id,$space_data,$total_post_to_fetch);
    $user_type = Auth::User();
    $user_id_get = Auth::User()->toArray();
    $user_id = $user_id_get['id'];

    $space_info = Space::getSpaceBuyerSeller($space_id);
    $space_user = SpaceUser::getSpaceUserRole($space_id,Auth::user()->id);
    $space_info['space_user'] = $space_user;
    Session::put('space_info', $space_info);

    if ($notification_id && !isset($_REQUEST['email'])) {
        $notification = Notification::findorfail($notification_id);
        $notification->timestamps = false;
        $notification->notification_status = TRUE;
        $notification->save();
    }

    $media = Media::bySpaceId($space_data[0]['id'], 'get');

    $get_user_type = Space::getUserSpace($user_id,$space_id);
    $space_users = SpaceUser::getSpaceUsers($space_id);
    $space_id = Session::get('space_info')['id'];
    $check_buyer = (new FeedbackController)->checkBuyer($space_id, $user_id);
    $give_feedback = (new FeedbackController)->giveFeed($space_id, $user_id);
    $feedback_status = (new FeedbackController)->feedbackStatus($space_id);
    $s3_form_details = (new Aws)->uploadClientSideSetup();
    $space_info['days_left'] = (Config::get('constants.feedback.feedback_opened_till') - Carbon::now()->day) + 1;
    $space_info['quater'] = Carbon::parse($space_info['feedback_status_to_date'])->subMonth(3)->format('F Y') . ' - ' . Carbon::parse($space_info['feedback_status_to_date'])->subMonth(1)->format('F Y');

    $approve_user = SpaceUser::spaceUsersWithSubCompany($space_id);
    $companies_dictonary = Company::getAllCompaniesById(array_unique(array_column($approve_user, 'company_id')), ['id', 'company_name']);
        $approve_user_ref = User::getApprovedUsers($space_id);
        usort($approve_user, function($first_name_value, $second_name_value) {
            $compare_first_name = $first_name_value['user']['first_name'];
            $compare_second_name = $second_name_value['user']['first_name'];
            $comparison_output = strcasecmp($compare_first_name, $compare_second_name);
            if ($comparison_output == 0) {
                return 0;
            }
            if ($comparison_output > 0) {
                return 1;
            }
            if ($comparison_output < 0) {
                return -1;
            }
        });

        $community_user = SpaceUser::getSpaceUsersInvited($space_id);
        $previous_space_data = SpaceUser::userProfileNotNull(Auth::user()->id);
        if (sizeOfCustom($community_user) > Config::get('constants.COMMUNITY_USERS_COUNT')) {
            $community_user[] = shuffle($community_user);
            $community_user = array_slice($community_user, 0, Config::get('constants.COMMUNITY_USERS_COUNT'));
        }

        $_companies = Company::getAllCompanyNames();
        $companies = [];
        foreach($_companies as $company) {
            $companies[$company['id']] = $company['company_name'];
        }
        unset($_companies);
        foreach ($approve_user as $key => $approved_user_profile) {
            if (isset($approved_user_profile['metadata']['user_profile']) && !empty($approved_user_profile['metadata']['user_profile']['company']) ) {
                $approve_user[$key]['company'] = $companies[$approved_user_profile['metadata']['user_profile']['company']];
            } else {
                $approve_user[$key]['company'] = '';
            }
        }
    $companies_info  = Company::getCompanysById([$space_info->company_seller_id, $space_info->company_buyer_id]);
    $seller_name = $companies_info[$space_info->company_seller_id];
    $buyer_name = $companies_info[$space_info->company_buyer_id];
    $buyerseller_name = array_merge($buyer_name, $seller_name); 

    $space_pinned_post_count = Post::pinPostCount($space_data[0]['id']);
    $groups = SpaceGroups::invitedSpaceUserGroups($space_data[0]['id'],Auth::user()->id);
    if (isset($post_data[0]) && (stripos($post_data[0]['visibility'], $user_id) !== false || stripos($post_data[0]['visibility'], 'all') !== false)) {
        if(isset($get_landing_data['via_email'])) PostViews::logSingleView(['user_id' => $user_id, 'post_id'=>$post_id, 'space_id' => $space_id]);
    return view('posts/admin_landing', ['companies_dictonary'=>$companies_dictonary, 'toppost'=>[],'single_post_view'=>true, 'case_post'=>1,'load_type' => 'simple', 'group' => $groups, 's3FormDetails' => $s3_form_details,'userdetail' => $user_type, 'spaceid' => $space_data, 'postdata' => $post_data, 'data' => $space_info, 'media_data' => $media, 'space_users' => $space_users, 'space_user' => $space_info['space_user'], 'spaceid' => $space_id, 'approve_user' => $approve_user, 'approve_user_ref' => $approve_user_ref, 'user_role' => $get_user_type, 'community_user' => $community_user, 'post_show' => $total_post_to_fetch, 'checkBuyer' => $check_buyer, 'giveFeedback' => $give_feedback, 'feedback_status' => $feedback_status, 'space_pinned_post_count'=> $space_pinned_post_count,'buyerseller' => $buyerseller_name,'buyer_info' => $buyer_name[0],'previous_space_data' => $previous_space_data, 'space_id'=>$space_id]);
    } else {
        return response()->view('errors.404', [], 404);
    }
}

    public function getSinglePostAjax(Request $request, $post_id = null) {
        $logged_in_user = Auth::User();
        $post = Post::findorfail($post_id);
        $space_data = Space::getSpaceBuyerSeller($post->space_id);
        $space_user = SpaceUser::getActiveSpaceUser($post->space_id, $logged_in_user->id);

        if (!sizeOfCustom($space_user)) {
            abort(404);
        }
        $space_data['space_user'] = $space_user;
        Session::put('space_info', $space_data);
        if (isset($space_user[0]['metadata']['user_profile']) && $space_user[0]['metadata']['user_profile']['company'] != '') {
            $current_user_company = trim($space_user[0]['metadata']['user_profile']['company']);
            $space_company = Company::getCompanyById($current_user_company);
            Session::put('space_company', $space_company);
            Session::put('space_meta', $space_user[0]['metadata']);
        } elseif (isset($space_user[0]['metadata']['user_profile'])) {
            Session::put('space_company', '');
            Session::put('space_meta', $space_user[0]['metadata']);
        } else {
            Session::put('space_company', '');
            Session::put('space_meta', '');
        }

        $post_data = Post::getSinglePostDataWithUserOrAll($post_id, $post->space_id, $logged_in_user->id);

        Notification::markPostNotificationsAsRead($post_id, $logged_in_user->id);

        return view('posts/single_post_ajax', [
            'postdata' => $post_data,
            'data' => $space_data,
            'space_id' => $post->space_id,
            'approve_user' => [],
            'approve_user_ref' => [],
            'space_pinned_post_count' => []
        ]);
    }

    /* */

    public function admin_landing_addpost_content($id = null) {
        if (Session::get('space_info')['id'] != $id) {
            $data = Space::where('id', $id)->with('BuyerName', 'SellerName')->get()[0];
            $space_user = SpaceUser::with('user_role')->where('user_id', Auth::user()->id)->where('space_id', $id)->with('sub_comp')->get();
            $data['space_user'] = $space_user;
            Session::put('space_info', $data);
        }
        $approve_user = SpaceUser::with('User', 'Share', 'sub_comp')
        ->where('space_id', $id)->where('user_status', '0')
        ->where('metadata->invitation_code', '1')
        ->get()->toArray();
        $spacedata = Space::where('id', $id)->orderBy('created_at', 'DESC')->take(1)->get()->toArray();
        $s3FormDetails = (new Aws)->uploadClientSideSetup();
        $groups = SpaceGroups::where('space_id', $spacedata[0]['id'])->where('created_by', Auth::user()->id)
        ->with(['SpaceUserGroups.SpaceUser' => function($qry) {
            $qry->where('user_status', '0')->where('metadata->invitation_code', '1');
        }])
        ->with(['SpaceUserGroups.SpaceUser.user'])
        ->orderBy('created_at', 'desc')
        ->get()->toArray();
        $data = Session::get('space_info');
        return view('posts/admin_landing_addpost', ['data' => $data,
            'approve_user' => $approve_user,
            'group' => $groups,
            's3FormDetails' => $s3FormDetails
            ]);
    }

    /**
     * Get list of posts by ajax
     *
     * @param  int  $share_id
     * @return \Illuminate\Http\Response
     * creator - vikramjeet
     * date - december 21 ,2016
     */
    public function get_posts($share_id, Request $request) {
        $offset = $request->limit;
        if (!$offset) {
            return;
        }
        $logged_in_user = Auth::user();
        $space_data = Space::getSpaceBuyerSeller($share_id);
        if (!$space_data)
            abort(404);
        $this->setClientShareList();
        $space_user = SpaceUser::getSpaceUserRole($share_id,$logged_in_user->id);
        $space_data['space_user'] = $space_user;
        $media = Media::where('space_id', $space_data['id'])->get()->toArray();
        $space_users = SpaceUser::with('User', 'Share')->where('space_id', $share_id)->get();
        Session::put('space_info', $space_data);
        $postdata = Post::where('space_id', $space_data['id'])
        ->whereRaw(" ( '{$logged_in_user->id}' = ANY (string_to_array(visibility,',')) or 'All' = ANY (string_to_array(visibility,','))  ) ")
        ->with('PostMedia')->with('postmediaview')->with('viewUrlPostCount')
        ->with(['User.SpaceUser' => function($q3) use($space_data) {
            $q3->where('space_id', $space_data['id']);
        }])
        ->with(['endorse.space_user' => function($q) use($space_data) {
            $q->where('space_id', $space_data['id']);
        }])
        ->with(['comments.user', 'comments.spaceuser' => function($q4) use($space_data) {
            $q4->where('space_id', $space_data['id']);
        }])
        ->with('endorse.user')
        ->with(['postuser' => function($q5) use($logged_in_user) {
            $q5->where('user_id', $logged_in_user->id);
        }])
        ->orderByRaw('pin_status desc, case when pin_status = false then (case when reposted_at is null then created_at else reposted_at end) else pinned_at end desc')
        ->skip($offset)
        ->take(Config::get('constants.post_limit'))
        ->get()
        ->toArray();
        $case_post = 3;
        $approve_user = SpaceUser::with('User', 'Share')->where('space_id', $share_id)->where('user_status', '0')->where('metadata->invitation_code', '1')->get()->toArray();
        $approve_user_ref = User::whereHas('SpaceUser', function($q)use($share_id) {
            $q->where('space_id', $share_id);
        })->get()->pluck('full_name', 'id');
        usort($approve_user, function($a, $b) {
            $a1 = $a['user']['first_name']; //get the name string value
            $b1 = $b['user']['first_name'];

            $out = strcasecmp($a1, $b1);
            if ($out == 0) {
                return 0;
            } //they are the same string, return 0
            if ($out > 0) {
                return 1;
            } // $a1 is lower in the alphabet, return 1
            if ($out < 0) {
                return -1;
            } //$a1 is higher in the alphabet, return -1
        });
        $community_user = SpaceUser::with('User', 'Share')->where('user_status', '0')->where('space_id', $share_id)->where('metadata->invitation_code', '1')->get()->toArray();
        if (sizeOfCustom($community_user) > 3) {
            $community_user[] = shuffle($community_user);
            $community_user = array_slice($community_user, 0, 3);
        }
        $get_usertype = Space::where('user_id', $logged_in_user->id)->where('id', $share_id)->get()->toArray();
        $seller_name = Company::where('id', $space_data->company_seller_id)->get()->toArray();
        $buyer_name = Company::where('id', $space_data->company_buyer_id)->get()->toArray();
        $buyerseller_name = array_merge($seller_name, $buyer_name);
        if (sizeOfCustom($postdata)) {
            return view('posts/admin_landing_post', [
                'userdetail' => $logged_in_user,
                'spaceid' => $space_data,
                'postdata' => $postdata,
                'data' => $space_data,
                'media_data' => $media,
                'space_users' => $space_users,
                'space_user' => $space_user,
                'spaceid' => $share_id,
                'approve_user' => $approve_user,
                'approve_user_ref' => $approve_user_ref,
                'user_role' => $get_usertype,
                'buyerseller' => $buyerseller_name,
                'community_user' => $community_user,
                'case_post' => $case_post,
                'load_type' => 'ajax']);
        }
        return "";
    }

    public function spacePosts($space_id, Request $request) {
        if(!isset($request->limit)) return;
        $logged_in_user = Auth::User();
        Space::findorfail($space_id);

        $space_data = Space::getSpaceBuyerSeller($space_id);

        $space_user = SpaceUser::getActiveSpaceUser($space_id, $logged_in_user->id);

        if(!sizeOfCustom($space_user)) abort(404);
        $space_data['space_user'] = $space_user;
        Session::put('space_info', $space_data);
        if (!$space_data) abort(404);
        if (isset($space_user[0]['metadata']['user_profile']) && $space_user[0]['metadata']['user_profile']['company'] != '') {
            $current_user_company = trim($space_user[0]['metadata']['user_profile']['company']);
            $space_company = Company::getCompanyById($current_user_company);
            Session::put('space_company', $space_company);
            Session::put('space_meta', $space_user[0]['metadata']);
        } elseif (isset($space_user[0]['metadata']['user_profile'])) {
            Session::put('space_company', '');
            Session::put('space_meta', $space_user[0]['metadata']);
        } else {
            Session::put('space_company', '');
            Session::put('space_meta', '');
        }

        $post_data = Post::getPostDataWithUserOrAll($space_id, $request->limit, Config::get('constants.post_limit'), $logged_in_user->id, $request->tokencategory??null, null);
        if(!sizeOfCustom($post_data)) return;

        $invited_user = SpaceUser::getSpaceUserActiveCount($space_id, 'get');
        $approved_users = User::getApprovedUsers($space_id);
        $space_pinned_post_count = Post::pinPostCount($space_id);

        return view('posts/admin_landing_post', [
            'postdata' => $post_data,
            'data' => $space_data,
            'space_id' => $space_id,
            'approve_user' => $invited_user,
            'approve_user_ref' => $approved_users,
            'space_pinned_post_count' => $space_pinned_post_count,
            'posts' => $post_data
        ]);
    }

    /* Last Edited 6Feb By Parshant */

    public function edit_visibility(Request $request) {
        $data = $request->all();
        $updat = ['visibility' => $data['visibleusers']];
        $post_update = Post::where('id', $data['postid'])->where('space_id', $data['spaceid'])->update($updat);
        $visible_users = Post::where('id', $data['postid'])->select('visibility', 'space_id')->get()->toArray();
        $resp['user'] = 'all';
        $resp['user_id'] = $visible_users[0]['visibility'];
        $resp['names'] = 'Everyone';
        /* Log "add comments" event */
        (new Logger)->log([
            'action' => 'change post visibility',
            'description' => 'change post visibility to everyone'
            ]);
        return $resp;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $data = $request->all();
      
        $validator = Validator::make( $request->metadata['rule'], [
            '*.value' => [
                    'required',
                    'distinct',
                    'min:4',
                    'regex:/('.config('constants.EMAIL_SETTING.regex.domain').')/u'
            ],
        ], [
        
            'regex' => 'invalid email domain',
            'required' => 'Domain name is required',
            'distinct' => 'Domain is already taken',
            'min' => 'Your email domain must consist of at least 4 characters'
        ]);


        $data['metadata']['rule'] = json_encode($data['metadata']['rule']);
        $data['metadata']['rule'] = str_replace('@', '', $data['metadata']['rule']);
        $data['metadata']['rule'] = json_decode($data['metadata']['rule'], true);

        $validator->sometimes('0.value', 'required', function ($input) use ($request){
            return !array_filter(array_column($request->metadata['rule'], 'value'));
        });

        if ($validator->fails()) {
            return ['code' => 401, 'message' => $validator->errors()];
        }
        
        unset($data['onboarding_domain_flag']);
        unset($data['_method']);
        if (isset($data['metadata'])) 
        {
            $domain_exist = false;
            foreach ($data['metadata']['rule'] as $key => $value) 
            {
               if ($value['value'] != '' && $value['value'] == explode('@',Auth::user()->email)[1])
                   $domain_exist = true;
            }
            if(!$domain_exist)
            {
               $new_data =  array (
                  'name' => 'rule[]',
                  'value' => explode('@',Auth::user()->email)[1]
                );
               $data['metadata']['rule'][] = $new_data;
            }
            if (isset($data['metadata']['rule'])) {
                $data['metadata']['rule'] = strtolower(json_encode($data['metadata']['rule']));
                $data['metadata']['rule'] = json_decode($data['metadata']['rule'], true);
            }
            $data['metadata'] = ['rule'=>filterArray($data['metadata']['rule'], 'value')];
            $data['metadata'] = json_encode($data['metadata']);
        }
        /* Setting session start */
        $data_temp = Space::where('id', $id)->with('BuyerName', 'SellerName')->get()[0];
        $space_user = SpaceUser::with('user_role')->where('user_id', Auth::user()->id)->where('space_id', $id)->get();
        $data_temp['space_user'] = $space_user;
        Session::put('space_info', $data_temp);
        (new Logger)->mixPannelInitial(Auth::user()->id, $id, Logger::MIXPANEL_TAG['add_domain']);
        return Space::where('id', $id)->update($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        return $id;
    }


    public function setClientShareList() {
        $user_active_spaces = SpaceUser::userShares(Auth::user()->id);
        usort($user_active_spaces, function($current_share, $next_share) {
            $out = strcasecmp($current_share['share']['share_name'], $next_share['share']['share_name']);
            if($out == 0) return 0;
            if($out > 0) return 1;
            if($out < 0) return -1;
        });
        Session::put('user_spases_space_user', $user_active_spaces);
        return Session::get('user_spases_space_user');
    }

    public function add_comments(Request $request) {
        (new Logger)->log([
            'action' => 'add|remove comment',
            'description' => 'add|remove comment'
            ]);
        $data = $request->all();
        $this->update_share_session( $data['spaceid'] );
        if (isset($data['postid'])) {
            Post::findorfail($data['postid']);
        } else {
            abort(404);
        }
        $post_id = $data['postid'];
        $space_id = $data['spaceid'];

        Notification::updateBadgeStatus($post_id,Auth::user()->id);
        $comment_limit = $data['commentlimit'];
        $view_more = $data['morecheck'];
        if (isset($data['view_more']) && $data['view_more'] == config('constants.REQUESTED_FORM.status.true')) {
            $view_more = $data['view_more'];
        }
        $post_data = Post::postData($post_id,$space_id);
        $total_comments = '';
        if (isset($comment_limit) && $comment_limit != '') {
            Comment::addComment($data);
            $post_comment_count = Post::getCommentCount($post_id);
            $comment_final_count = $post_comment_count[0]['comment_count'] + 1;
            $post_user_id = $post_comment_count[0]['user_id'];
            Post::updateCommentCount($post_id,$comment_final_count);
            $commented_post = Post::postById($post_id);
            $valid_post_users = explode(',', $commented_post['visibility']);
            $post_users = array();
            if (($key = array_search('All', $valid_post_users)) !== false) {
                $all_space_user = SpaceUser::selectUserIdFromSpaceUser($space_id);
                foreach ($all_space_user as $all_space_user_single) {
                    array_push($post_users, $all_space_user_single['user_id']);
                }
                $valid_post_users = $post_users;
            }
            $user_name = User::getFirstLastNameOfUser($data['userid']);
            $comment_added_mail_data = [];
            $comment_added_mail_data['username'] = ucfirst($user_name->first_name) . ' ' . ucfirst($user_name->last_name);
            $client_share_info = Session::get('space_info');
            if ((!empty($client_share_info['seller_processed_logo'])) && (!empty($client_share_info['company_seller_logo']))) {
                $company_seller_logo = $client_share_info['seller_processed_logo'];
            } elseif ((empty($client_share_info['seller_processed_logo'])) && (!empty($client_share_info['company_seller_logo']))) {
                $company_seller_logo = $client_share_info['company_seller_logo'];
            } else {
                $company_seller_logo = env('APP_URL').'/images/login_user_icon.png';
            }
            if ((!empty($client_share_info['buyer_processed_logo'])) && (!empty($client_share_info['company_buyer_logo']))) {
                $company_buyer_logo = $client_share_info['buyer_processed_logo'];
            } elseif ((empty($client_share_info['buyer_processed_logo'])) && (!empty($client_share_info['company_buyer_logo']))) {
                $company_buyer_logo = $client_share_info['company_buyer_logo'];
            } else {
                $company_buyer_logo = env('APP_URL').'/images/login_user_icon.png';
            }

            $post_details = Post::postDetails($data['spaceid'],$data['postid']);
            $comment_added_mail_data['spacename'] = ucfirst($post_details[0]->first_name);
            $comment_added_mail_data['post_subject'] = $post_details[0]->post_subject;
            $comment_added_mail_data['post_description'] = $post_details[0]->post_description;
            $comment_added_mail_data['clientshare_name'] = $client_share_info['share_name'];
            $comment_added_mail_data['company_seller_logo'] = $company_seller_logo;
            $comment_added_mail_data['company_buyer_logo'] = $company_buyer_logo;
            $comment_added_mail_data['comment'] = trim($data['comment']);

            if (Auth::user()->id != $post_details[0]->suid) {
                $position = strpos($post_data[0]['visibility'], $post_details[0]->suid);
                $position = $position === false ?stripos($post_data[0]['visibility'], 'all'):$position;
                if ($position !== false) {
                    $space_user_info = SpaceUser::getSpaceUserInfo($data['spaceid'],$post_details[0]->suid);
                    if ($space_user_info[0]['comment_alert'] && $space_user_info[0]['user_status'] == 0) {
                        (new MailerController)->sendCommentNotification($data['spaceid'], $post_details[0]->suid, $post_id, $notification_id = '', $comment_added_mail_data, 'email.alert_comments');
                    }
                }
            }

            $commented_users = Comment::usersWhoComment($post_id,Auth::user()->id);
            $user_name = User::getFirstLastNameOfUser(Auth::user()->id);
            $comment_added_mail_data = [];
            $comment_added_mail_data['username'] = ucfirst($user_name->first_name) . ' ' . ucfirst($user_name->last_name);
            $commented_users_count = sizeOfCustom($commented_users);
            if (empty($commented_users)) {
                if (Auth::user()->id != $post_details[0]->suid) {
                    $approve_user_exist = strpos($post_data[0]['visibility'], $post_details[0]->suid);
                    if ($approve_user_exist) {
                        $space_user_info = SpaceUser::getSpaceUserInfo($data['spaceid'],$post_details[0]->suid);
                        if ($space_user_info[0]['user_status'] == Config::get('constants.INACTIVE_USER')) {

                            $comment_notification = Notification::getCommentNotification($post_id,$post_details[0]->suid);
                            if (!sizeOfCustom($comment_notification)) {
                                $notification = new Notification;
                                $notification->post_id = $post_id;
                                $notification->user_id = $post_details[0]->suid;
                                $notification->notification_status = FALSE;
                                $notification->space_id = $space_id;
                                $notification->notification_type =  Config::get('constants.COMMENT');
                                $notification->from_user_id = $post_user_id;
                                $notification->last_modified_by = Auth::user()->id;
                                $notification->comment_count = 1;
                                $notification->save();
                            }
                        }
                    }
                } else {
                    $get_comment_notification = Notification::getCommentNotification($post_id,$post_details[0]->suid)->toArray();
                    if (!empty($get_comment_notification)) {
                        $approve_user_exist = strpos($post_data[0]['visibility'], $post_details[0]->suid);
                        if ($approve_user_exist) {
                            $space_user_info = SpaceUser::getSpaceUserInfo($data['spaceid'],$post_details[0]->suid);
                            if ($space_user_info[0]['user_status'] == Config::get('constants.INACTIVE_USER')) {
                                Notification::updateNotification($post_id,$post_details[0]->suid,'FALSE',Auth::user()->id,$cmt_cont);
                            }
                        }
                    }
                }
            } else {
                if (Auth::user()->id != $post_details[0]->suid) {
                    $comment_count = $commented_users_count + 1;
                    $get_comment_notification = Notification::getCommentNotification($post_id,$post_details[0]->suid)->toArray();
                    if (!empty($get_comment_notification)) {
                        $approve_user_exist = strpos($post_data[0]['visibility'], $post_details[0]->suid);
                        if ($approve_user_exist) {
                            $space_user_info = SpaceUser::getSpaceUserInfo($data['spaceid'],$post_details[0]->suid);
                            if ($space_user_info[0]['user_status'] == Config::get('constants.INACTIVE_USER')) {
                                Notification::updateNotification($post_id,$post_details[0]->suid,'FALSE',Auth::user()->id,$comment_count);
                            }
                        }
                    }
                }
            }
            /* ******************************************************* */

            foreach ($commented_users as $notified_user) {
                $get_comment_notification = Notification::getCommentNotification($post_id,$notified_user)->toArray();
                $notification_status = FALSE;
                $approve_user_exist = strpos($post_data[0]['visibility'], $notified_user);
                if ($approve_user_exist) {
                    if (empty($get_comment_notification)) {
                        if (Auth::user()->id == $notified_user) {
                            $notification_status = TRUE;
                        }
                        $notification = new Notification;
                        $notification->post_id = $post_id;
                        $notification->user_id = $notified_user;
                        $notification->notification_status = $notification_status;
                        $notification->space_id = $space_id;
                        $notification->notification_type = Config::get('constants.COMMENT');
                        $notification->from_user_id = $post_user_id;
                        $notification->last_modified_by = Auth::user()->id;
                        $notification->comment_count = $commented_users_count;
                        $notification->save();
                    } else {
                        if (Auth::user()->id == $notified_user) {
                            $notification_status = TRUE;
                        }

                        Notification::updateNotification($post_id,$get_comment_notification[0]['user_id'],$notification_status,Auth::user()->id,$commented_users_count);
                    }

                    $space_user_info = SpaceUser::getSpaceUserInfo($data['spaceid'],$notified_user);

                    if ($space_user_info[0]['comment_alert']) {
                        $comment_mail_data = [];
                        $comment_mail_data['username'] = ucfirst(Auth::user()->first_name) . ' ' . ucfirst(Auth::user()->last_name);
                        $space_user = Post::postDetails($data['spaceid'],$data['postid']);

                        $comment_mail_data['spacename'] = ucfirst($space_user[0]->first_name);
                        $comment_mail_data['post_subject'] = $space_user[0]->post_subject;
                        $comment_mail_data['post_description'] = $space_user[0]->post_description;
                        $comment_mail_data['clientshare_name'] = $client_share_info['share_name'];
                        $comment_mail_data['company_seller_logo'] = $company_seller_logo;
                        $comment_mail_data['company_buyer_logo'] = $company_buyer_logo;
                        $comment_mail_data['comment'] = trim($data['comment']);

                        $space_user_info = SpaceUser::getSpaceUserInfo($data['spaceid'],$notified_user);

                        if ($space_user_info[0]['comment_alert'] && $space_user_info[0]['user_status'] == 0) {
                            if ($notified_user != $post_details[0]->suid) {
                                (new MailerController)->sendCommentNotification($space_id, $notified_user, $post_id, $notification_id = '', $comment_mail_data, 'email.alert_comments');
                            }
                        }
                    }
                }
            }

            $comment_content = Comment::postComments($space_id, $post_id, Config::get('constants.POST_COMMENT_ROW_LIMIT'));
            if (isset($data['view_more']) && $data['view_more'] == true) {
                $comment_content = Comment::postComments($space_id, $post_id);
            }
        } else {

            if (isset($data['action']) && $data['action'] == 'delete' && isset($data['commentid']) && $data['commentid'] != '') {
                $post = new Comment;
                $post = Comment::where('id', $data['commentid']);
                $post->delete();
            }

            $comment_content = Comment::postComments($space_id, $post_id);
            if (isset($view_more) && $view_more == 'false') {
                $comment_content = Comment::postComments($space_id, $post_id, Config::get('constants.POST_COMMENT_ROW_LIMIT'));
            }
        }

        $comment_total = Comment::postComments($space_id, $post_id);
        $total_comments = sizeOfCustom($comment_total);
        return view('pages/comment_ajax', ['post_comments' => $comment_content, 'postid' => $post_id, 'total_comments' => $total_comments, 'morecheck' => $view_more, 'spaceid' => $space_id]);
    }

    public function endorsePost(Request $request, $api_response=false){
        $post = Post::findOrFail($request->post_id);
        $request['posthonor'] = $post['user_id'];
        $request['endorseid'] = $post['id'];
        $request['spaceid'] = $post['space_id'];        
        $request['userid'] = Auth::user()->id;
        $request['like_status'] = $request->endorse;
        $request['liked_from_email'] = 0;

        return $this->endorse($request, $api_response);
    }

    public function endorse(Request $request, $api_response=false) {
        $post_author = '';
        $data = $request->all();
        if (isset($data['posthonor'])) {
            $post_author = $data['posthonor'];
        }
        $like_status = $data['like_status'];
        if (isset($data['endorseid']) && isset($data['userid']) && Auth::check() != '') {
            $endorse = new EndorsePost;
            $endorse->post_id = $data['endorseid'];
            $endorse->user_id = $data['userid'];
            $endrosed_by_me = array();
            $existing_endorse = EndorsePost::where('post_id', $data['endorseid'])->where('user_id', $data['userid'])->get();
            if (sizeOfCustom($existing_endorse) == '0') {
                $endorse->save();
                $endrosed_by_me = array('true');
            } else {
                if(isset($data['liked_from_email']) && $data['liked_from_email']==1){
                    $endrosed_by_me = array('true');
                    }else{
                $endorse = EndorsePost::where('post_id', $data['endorseid'])->where('user_id', $data['userid']);
                $endorse->delete();
                    }
            }
            $endorse_data = EndorsePost::where('post_id', $data['endorseid'])
            ->with('user', 'space_user')
            ->whereHas('space_user', function($q)use($data) {
                $q->where('space_id', $data['spaceid']);
            })->get()->toArray();
            /* ADD NOTIFICATION START */
            if ($post_author != $data['userid']) 
            { 
                $notficatin_exist = (new Notification)->getUserLikeNotification($data['endorseid'], $post_author, 'like');
                    if ($like_status == 1)
                        $this->sendEmailUserLikedPost($data['userid'],$data['endorseid'],$post_author);

                if ($notficatin_exist) 
                {
                    $totlal_like = $notficatin_exist['comment_count'];
                    if ($like_status == 0 && $totlal_like >= 1) 
                    { 
                        $update_array = ['notification_status' => FALSE, 'badge_status' => TRUE, 'last_modified_by' => Auth::user()->id, 'comment_count' => $totlal_like + 1];
                        (new Notification)->updateUserNotification($data['endorseid'], $post_author, 'like', $update_array);
                    }
                    if ($like_status == 1 && $totlal_like > 1) 
                    {
                        $get_last_liked_user = (new EndorsePost)->getLikePost($data['endorseid']);
                        $update_liked_status = ['last_modified_by' => $get_last_liked_user['user_id'], 'comment_count' => $totlal_like - 1];
                        (new Notification)->updateUserNotification($data['endorseid'], $post_author, 'like', $update_liked_status);
                    }
                    if ($like_status == 1 && $totlal_like == 1) 
                        (new Notification)->deleteUserNotifications($data['endorseid'], $post_author, 'like');

                } else {
                    $user_notification_create = ['post_id'=>$data['endorseid'],'user_id'=>$post_author, 'space_id'=>$data['spaceid'], 'notification_type'=>'like', 'from_user_id'=>$post_author, 'last_modified_by'=>$data['userid'], 'comment_count' => 1];
                    (new Notification)->saveUserNotifications($user_notification_create);
                }
            }
            /* ADD NOTIFICATION END */
            $endorse_by_others = $endorse_data;
            foreach($endorse_by_others as $key => $value) {
                if(in_array(Auth::user()->id, $value))
                    unset($endorse_by_others[$key]);
            }
            $response = array('posts'=>array(['endorse_by_others' => $endorse_by_others,'endorse' => $endorse_data, 'id' => $data['endorseid'], 'userid' => Auth::user()->id, 'endorse_by_me' => $endrosed_by_me, 'spaceid' => $data['spaceid']]));
            
            return $api_response ? apiResponse((new Post)->getPost($data['endorseid'], Auth::user()->id)):$response;

          } else {
            return $api_response ? apiResponse([], 400) : false;
        }
    }

    /* ENDORSE POST END */

    function view_community_popup(Request $request) {
        $data = $request->all();
        $community_user_profile = SpaceUser::with('User', 'Share')->where('user_id', $data['user_id'])->where('space_id', $data['space_id'])->where('metadata->invitation_code', '1')->first();
        return view('pages/view_user_community_ajax', ['user_profile' => $community_user_profile]);
    }

    /* ENDORSE POPU AJAX FUNCTION */

    function endorsepopup_ajax(Request $request) {
        $data = $request->all();
        $endorse_data = EndorsePost::where('post_id', $data['endorseid'])
        ->with('user')
        ->with(['space_user' => function($q)use($data) {
            $q->where('space_id', $data['spaceid']);
        }])
        ->whereHas('space_user', function($q)use($data) {
            $q->where('space_id', $data['spaceid']);
        })
        ->get()->toArray();
        return view('pages/endorse_popup_ajax', ['data' => $endorse_data, 'post_id' => '', 'userid' => Auth::user()->id, 'endorsed_by_me' => '']);
    }

    public function notificationCount(Request $request) {
        $request->space_id ?: abort(404);
        $notification = Notification::notificationCount($request->space_id, Auth::user()->id);
        return $notification[0]->sum??'';
    }

    public function activityNotification(Request $request) {

        $current_page['space_data'] = Space::where('id', $request->space_id)->get();
        $current_page['space_id'] = $request->space_id;
        $notification_header = $request->notification_header;

        $notifications = Notification::activityNotification($request, Auth::User()->id);
        return view('pages/notifications_ajax', ['feedback' => $notifications['feedback'], 'data' => $notifications['notification'], 'notification_header' => $notification_header, 'notification_count' => sizeOfCustom($notifications['notification']), 'current_page' => $current_page]);
    }

    public function communityMembers(Request $request) {

        $space_id = $request->space_id;
        $company_id = $request->company_id ?? null;
        $offset = $request->offset;
        $search = $request->search;
        $space_members = SpaceUser::communityMember($space_id, $company_id, $offset, null, $search);
        $companies_dictonary = Company::getAllCompaniesById(array_unique(array_column($space_members, 'company_id')), ['id', 'company_name']);
        return view('pages/community_members_ajax', ['space_members' => $space_members, 'companies_dictonary' => $companies_dictonary]);
    }

    public function getAllShareNotifications(Request $request) {
        $notifications = Notification::getAllShareNotifications($request->spaceid, Auth::User()->id);
        return $notifications??'';
    }

    public function resetNotificationsBadge($space_id) {
        (new Logger)->log([
            'action' => 'view notification',
            'description' => 'view notification'
            ]);
        $space_id = Session::get('space_info')['id'];
        $usreid = Auth::User()->id;
        $delete_not_exist_entry = DB::select("UPDATE notifications set badge_status = false where space_id = '$space_id' and user_id = '$usreid'");
    }

    public function editVisibillitypopupAjax(Request $request) {
        $visibility_popup_form_data = $request->all();
        $space_id = Session::get('space_info')['id'];
        if (Auth::check() != '') {
            $explode_visible_user_info = explode(',', $visibility_popup_form_data['visibleuser']);
            $unique_array_visible_user_info = array_unique($explode_visible_user_info);
            $implode_visible_user_info = implode(',', $unique_array_visible_user_info);
            Post::where('id', $visibility_popup_form_data['postid'])->update(['visibility' => $implode_visible_user_info]);
            $visible_users = Post::where('id', $visibility_popup_form_data['postid'])->select('visibility', 'space_id')->get()->toArray();
            $user_avilable = str_replace('All,', '', $visible_users[0]['visibility']);
            $user_avilable = str_replace('checkboxall=1', '', $user_avilable);
            $delete_user_available_id = array_filter(explode(",", $user_avilable));
            $visible_to_all = $visibility_popup_form_data['visible_to'];
            //DELETE ALL NOTIFICATION EXCEPT $delid OF THIS POST
            $delete_user_available_ids = implode('\',\'', $delete_user_available_id);
            $delete_not_exist_entry = DB::select("delete from notifications where user_id NOT IN ('" . $delete_user_available_ids . "') and post_id = '" . $visibility_popup_form_data['postid'] . "' ");
            $visibilty_members_string = $visible_users[0]['visibility'];
            $visibilty_members_array = explode(',', $visibilty_members_string);
            $approve_user_ref = User::getApprovedUsers($space_id);
            /* Log "view notification" event */
            (new Logger)->log([
                'action' => 'edit post visibility',
                'description' => 'edit post visibility'
                ]);
            if (strpos($visible_users[0]['visibility'], 'All') !== false) {
                $visibility_response['user'] = 'all';
                $visibility_response['user_id'] = $visible_users[0]['visibility'];
                $visibility_response['names'] = 'Everyone';
                return $visibility_response;
            } else {
                $visibility_response['user'] = 'restrict';
                $visibility_response['user_id'] = $visible_users[0]['visibility'];
                $approved_users = array();
                foreach ($visibilty_members_array as $visible_user => $index) {
                    if (isset($approve_user_ref[$index])) {
                        if ($visible_user < 5) {
                            array_push($approved_users, $approve_user_ref[$index]);
                        } else {
                            $other_users_count = 'and ' . (sizeOfCustom($visibilty_members_array)-5) . ' others';
                            array_push($approved_users, $other_users_count);
                            break;
                        }
                    }
                }
                $visibility_response['user'] = 'restrict';
                $visibility_response['user_id'] = $visible_users[0]['visibility'];
                $approved_users_name = implode(',', $approved_users);
                $visibility_response['names'] = str_replace(',', '<br/>', $approved_users_name);
                return $visibility_response;
            }
        }
    }

    /* VISIBILTIY EDIT POPU END */

    public function update_password(request $request) {
        $data = $request->all();
        $update_pass = \Validator::make($request->all(), ['current_password' => 'required',
            'new_password' => 'required|min:8|max:60|confirmed',
            ]);
        $user = User::find(auth()->user()->id);
        if (!Hash::check($data['current_password'], $user->password)) {
            return $update_pass;
        } else {
            $update = [
            'password' => bcrypt($data['new_password']),
            ];
            User::where('id', $user->id)->update($update);
            /* Log "visit community" event */
            (new Logger)->log([
                'action' => 'change password',
                'description' => 'change password'
                ]);
            return "Password Updated Sucessfully.";
        }
    }

    public function check_pass(Request $request) {
        $data = $request->all();
        $user = User::find(auth()->user()->id);
        if (!Hash::check($data['current_pass'], $user->password)) {
            return 'false';
        } else {
            return 'true';
        }
    }


    public function visibility_popupmore_ajax(Request $request) {
        $data = $request->all();
        if (isset($data['endorseid']) && isset($data['spaceid']) && Auth::check() != '') {
            $postid = $data['endorseid'];
            $spaceid = $data['spaceid'];
            $postdata = Post::where('id', $postid)->get()->toArray();
            $spacedata = SpaceUser::with('User', 'Share')->where('user_status', '0')->where('space_id', $spaceid)->where('metadata->invitation_code', '1')->get()->toArray();
            usort($spacedata, function($a, $b) {
                $a1 = $a['user']['first_name']; //get the name string value
                $b1 = $b['user']['first_name'];

                $out = strcasecmp($a1, $b1);
                if ($out == 0) {
                    return 0;
                } //they are the same string, return 0
                if ($out > 0) {
                    return 1;
                } // $a1 is lower in the alphabet, return 1
                if ($out < 0) {
                    return -1;
                } //$a1 is higher in the alphabet, return -1
            });

            return view('pages/visibility_popupmore_ajax', ['postdata' => $postdata, 'spacedata' => $spacedata, 'logeduser' => Auth::user()->id, 'spaceid' => $spaceid]);
        } else {
            return false;
        }
    }
    /* VISIBILITY SETTING POPUP END */

    /* ENDORSE POPU AJAX FUNCTION */
    public function view_file(Request $request) {
        $data = $request->all();
        $space_id = $data['space_id'] ?? Session::get('space_info')['id'];
        $user_id = Auth::user()->id;
        if (isset($data['post_id'])) {
            $post_id = $data['post_id'];
        } else {
            $post_id = '';
        }

        if ($post_id == '' && $user_id == '') {
            return 0;
        }
        $postviews = new PostViews;
        if ($user_id != '') {
            $postviews = new PostViews;
            $postviews->user_id = $user_id;
            $postviews->space_id = $space_id;
            $postviews->post_id = $post_id;
            $postviews->save();
            $view_status = 'n';
        }
        return array('check_user_view_exist' => $view_status);
    }

    public function updateshare(Request $request) {
        $request_data = $request->all();
        if(isset($request_data['buyer_logo']) && !empty($request_data['buyer_logo']) && $request_data['buyer_logo_status'] == Config::get('constants.REQUESTED_FORM.field.file.browsed'))
            $request_data['space']['buyer_logo'] = $this->save_logo($request_data['buyer_logo']);

        if(isset($request_data['seller_logo']) && !empty($request_data['seller_logo']) && $request_data['seller_logo_status'] == Config::get('constants.REQUESTED_FORM.field.file.browsed'))
            $request_data['space']['seller_logo'] = $this->save_logo($request_data['seller_logo']);
        $job_data = [
            'seller_logo' => isset($request_data['space']['seller_logo'])?$request_data['space']['seller_logo']:'',
            'seller_name' => $request_data['seller_name'],
            'buyer_logo' => isset($request_data['space']['buyer_logo'])?$request_data['space']['buyer_logo']:'',
            'buyer_name' => $request_data['buyer_name'],
            'share_id' => $request_data['space_id'],
            'user_id' => $request_data['user_id'],
            'form_edit' => Config::get('constants.REQUESTED_FORM.status.true')
        ];

        dispatch(new ConvertRoundImage($job_data));
        $request_data['space']['share_name'] = $request_data['client_share_name'];
        $request_data['space']['contract_value'] = (trim($request_data['contract_value']) != '')?trim($request_data['contract_value']):NULL;
        $request_data['space']['contract_end_date'] = (trim($request_data['contract_end_date']) != '')?trim($request_data['contract_end_date']):NULL;
        $request_data['space']['status'] = (trim($request_data['status']) != '')?trim($request_data['status']):config::get('constants.SHARE_STATUS');

        $users = SpaceUser::getSpaceInfo($request_data['space_id']);
        $prev_seller_id = $request_data['company_seller_id_hidden'];
        $prev_buyer_id = $request_data['company_buyer_id_hidden'];

        $seller_company = Company::firstOrCreate(['company_name'=>trim($request_data['seller_name'])]);
        $buyer_company = Company::firstOrCreate(['company_name'=>trim($request_data['buyer_name'])]);

        SpaceUser::updateCompanyId(['space_id'=>$request_data['space_id'], 'new_id'=>$seller_company['id'], 'previous_id'=>$prev_seller_id]);
        SpaceUser::updateCompanyId(['space_id'=>$request_data['space_id'], 'new_id'=>$buyer_company['id'], 'previous_id'=>$prev_buyer_id]);
        $request_data['space']['company_seller_id'] = $seller_company['id'];
        $request_data['space']['company_buyer_id'] = $buyer_company['id'];
        $request_data['space']['domain_restriction'] = $request_data['space']['domain_restriction'] ?? false;
        $request_data['space']['ip_restriction'] = $request_data['space']['ip_restriction'] ?? false;
        $request_data['space']['allowed_ip'] = isset($request_data['space']['allowed_ip']) && $request_data['space']['allowed_ip'] ? json_encode(array_filter($request_data['space']['allowed_ip'])) : false;
        if (isset($request_data['space']['seller_logo']))
            $request_data['space']['seller_logo'] = json_encode($request_data['space']['seller_logo']);
        if (isset($request_data['space']['buyer_logo']))
            $request_data['space']['buyer_logo'] = json_encode($request_data['space']['buyer_logo']);
            
        Space::updateSpaceById($request_data['space_id'], $request_data['space']);
        dispatch(new EmailHeaderImage($request_data['space_id']));
        return back();
    }

    public function createImageOrientation($logo_url, $file_input){
        if(!empty($file_input)){
            $image_object = array();
            $image_object['url'] = $logo_url; 
            $extension_seller_logo = explode('.', $logo_url['file'])[1]; 
            $image_object['extension'] = $extension_seller_logo;
            if(in_array($extension_seller_logo, config('constants.IMAGE_EXTENSIONS'))) {
                $files_data = (new PostController)->imageOrientation($logo_url,$extension_seller_logo, 'public');
                if($files_data) 
                {
                    $name = substr($files_data, strrpos($files_data, '/') + 1);
                    return [
                        'path' => ['company_logo'],
                        'file' => $name
                    ];
                }
                return $logo_url;
            }
            return false;
        }
        return false;
    }

    public function updateShareHeader(Request $request){
        $request_data = $request->all();
        if(!isset($request_data['space_id'])){
            return ['result'=>false, 'error'=>trans('messages.error.missing_key', ['key' => 'Space Id']), 'key'=>'space_id'];
        }

        if(isset($request_data['seller_twitter_name']) 
            && $request_data['seller_twitter_name'] != '' 
            && $request_data['seller_twitter_name'] != '@') {
            $twitter_array['request_url'] = 'users/show';
            $twitter_array['option']['screen_name']=$request_data['seller_twitter_name'];
            $response = $this->checkTwitter($twitter_array);
            if(isset($response->getData()->error) && $request->ajax())
                return ['result'=>false,'error'=>$response->getData()->error,'key'=>'seller_twitter_name'];
            $request_data['seller_logo'] = $request_data['seller_twitter_logo'];
        }
        if(isset($request_data['buyer_twitter_name'])  
            && $request_data['buyer_twitter_name'] != '' 
            && $request_data['buyer_twitter_name'] != '@') {
            $twitter_array['request_url'] = 'users/show';
            $twitter_array['option']['screen_name']=$request_data['buyer_twitter_name'];
            $response = $this->checkTwitter($twitter_array);
            if(isset($response->getData()->error) && $request->ajax())
                return ['result'=>false,'error'=>$response->getData()->error,'key'=>'buyer_twitter_name'];
        }
        if(!empty($request_data['seller_twitter_logo'])) 
            $request_data['seller_logo'] = $request_data['seller_twitter_logo'];
        if(!empty($request_data['buyer_twitter_logo'])) 
            $request_data['buyer_logo'] = $request_data['buyer_twitter_logo'];
        if(!empty($request_data['banner_image'])) 
            $request_data['share_banner'] = $request_data['banner_image'];
        if(isset($request_data['seller_logo']) && !empty($request_data['seller_logo']))
        {
            $request_data['space']['seller_logo'] = $this->save_logo($request_data['seller_logo']);
            if(empty($request_data['seller_twitter_logo']))
            {
                $company_seller_logo = $this->createImageOrientation($request_data['space']['seller_logo'], $request_data['seller_logo']);
                if(!empty($company_seller_logo)) 
                    $request_data['space']['seller_logo'] = $company_seller_logo;
            }
        } 
        
        if(isset($request_data['buyer_logo']) && !empty($request_data['buyer_logo']))
        {
            $request_data['space']['buyer_logo'] = $this->save_logo($request_data['buyer_logo']);
            if(empty($request_data['buyer_twitter_logo']))
            {
                $company_buyer_logo = $this->createImageOrientation($request_data['space']['buyer_logo'], $request_data['buyer_logo']);
                if(!empty($company_buyer_logo)) 
                    $request_data['space']['buyer_logo'] = $company_buyer_logo;
            }
        }

        if(isset($request_data['share_banner']) && !empty($request_data['share_banner']) )
        {
            $request_data['space']['background_image'] = $this->save_logo($request_data['share_banner'],config('constants.URL_EXIST'));
            if(!empty($request_data['share_banner']) && empty($request_data['banner_image']))
            {
                $share_banner = $this->createImageOrientation($request_data['space']['background_image'], $request_data['share_banner']);
                if(!empty($share_banner)) 
                    $request_data['space']['background_image'] = $share_banner;
            }
        }
        if(isset($request_data['share_name']))
        $request_data['space']['share_name'] = $request_data['share_name'];

        $job_data = [
            'seller_logo' => isset($request_data['space']['seller_logo'])?$request_data['space']['seller_logo']:'',
            'seller_name' => $request_data['seller_name']??time(),
            'buyer_logo' => isset($request_data['space']['buyer_logo'])?$request_data['space']['buyer_logo']:'',
            'background_logo' => isset($request_data['space']['background_image'])?$request_data['space']['background_image']:($request_data['share_banner_url']??''),
            'buyer_name' => $request_data['buyer_name']??time(),
            'share_id' => $request_data['space_id'],
            'user_id' => Auth::user()->id,
            'form_edit' => Config::get('constants.REQUESTED_FORM.status.true')
        ];
        dispatch(new ConvertRoundImage($job_data));
        if (isset($request_data['space']['background_image']))
            $request_data['space']['background_image'] = json_encode($request_data['space']['background_image']);
        if (isset($request_data['space']['seller_logo'])) {
            $request_data['space']['seller_logo'] = json_encode($request_data['space']['seller_logo']);
            $request_data['space']['seller_circular_logo'] = null;
        }
        if (isset($request_data['space']['buyer_logo'])) {
            $request_data['space']['buyer_logo'] = json_encode($request_data['space']['buyer_logo']);
            $request_data['space']['buyer_circular_logo'] = null;
        }
        
        if(isset($request_data['space']))
            Space::updateSpaceById($request_data['space_id'], $request_data['space']); 

        dispatch(new EmailHeaderImage($request_data['space_id'])); 
        dispatch(new CreateJointShareLogos($request_data['space_id'])); 
        $seller_logo = (isset($request_data['space']['seller_logo']))?wrapUrl(composeUrl($request_data['space']['seller_logo'])):'';
        $buyer_logo = (isset($request_data['space']['buyer_logo']))?wrapUrl(composeUrl($request_data['space']['buyer_logo'])):'';
        $background_image = (isset($request_data['space']['background_image']))?wrapUrl(composeUrl($request_data['space']['background_image'])):'';
        return response()->json(['result' => true, 'seller_logo' => $seller_logo, 'buyer_logo' => $buyer_logo, 'background_image' => $background_image, 'space_data' => ($request_data['space']) ?? []]);
    }

    public function view_edit_share(Request $request) {
        $data = $request->all();
        if (!isset($data['space_id'])) {
            abort(404);
        }
        $share_user = space::where('id', $data['space_id'])->with('AdminUser')->with('AdminUser', 'Company', 'SellerName', 'BuyerName')->get()->toArray();
        return view('pages/view_share_ajax', ['share_user' => $share_user]);
    }

    public function update_admin_share(Request $request) {
        $data = $request->all();
        if (!isset($data['spaceid'])) {
            abort(404);
        }
        $updated_space = Space::where('id', $data['spaceid'])->update(['share_name' => trim($data['sharename'])]);
        $data1 = Space::where('id', $data['spaceid'])->with('BuyerName', 'SellerName')->get()[0];
        $space_user = SpaceUser::with('user_role')->where('user_id', Auth::user()->id)->where('space_id', $data['spaceid'])->get();
        $data1['space_user'] = $space_user;
        Session::put('space_info', $data1);
        /* Log "visit community" event */
        (new Logger)->log([
            'action' => 'update share name',
            'description' => 'update share name'
            ]);
        dispatch(new EmailHeaderImage($data['spaceid']));
        return $updated_space;
    }

    public function listCommunityMembers(Request $request, $id) {
        $company_id = $request->company_id ?? null;
        $offset = $request->offset;
        $search = $request->search;
        Space::findorfail($id);
        $get_community_data = $request->all();
        if (isset($get_community_data['email']) && base64_decode($get_community_data['email']) != auth::user()->email) {
            //COMING FROM COMMENT ADD POST MAIL
            return redirect('logout?spaceid=' . $id . '&email=' . $get_community_data['email'] . '&status=logout&from=community');
        }
        /* Log "visit community" event */
        (new Logger)->log([
            'action' => 'visit community',
            'description' => 'visit community'
            ]);

        if (empty(Auth::check())) {
            return redirect('/');
        }

        (new UserController)->updateSpaceSessionData($id);
        $this->setClientShareList();

        if (isset($id)) {
            $space = SpaceUser::getActiveSpaceUser($id, Auth::user()->id, 'first');
        }
        if (!sizeOfCustom($space)) {
            abort(404);
        }
        $space_data = Space::spaceById($id, 'get');
        $space_members = SpaceUser::communityMember($id, $company_id, $offset, null, $search);
        $companies_dictonary = Company::getAllCompaniesById(array_unique(array_column($space_members, 'company_id')), ['id', 'company_name']);
        usort($space_members, function($current_user, $next_user) {
            $name_compare_output = strcasecmp($current_user['user']['first_name'], $next_user['user']['first_name']);
            if ($name_compare_output == 0) return 0;
            if ($name_compare_output > 0) return 1;
            if ($name_compare_output < 0) return -1;
        });

        $space_user = SpaceUser::getActiveSpaceUser($id, Auth::user()->id);
        $active_user_space_data = Space::getSpaceBuyerSeller($id);
        $event_tag = Logger::MIXPANEL_TAG['view_community'];
        if(isset($request['company_id'])){
            $event_tag = (ucfirst(Space::spaceCompany($id, $request['company_id']))??'Company').Logger::MIXPANEL_TAG['filter_community'];
        }
        (new Logger)->mixPannelInitial(Auth::user()->id, null, $event_tag, ['share_name' => $space_data[0]['share_name']]);
        return view('pages/community_members', 
                [
                    'companies_dictonary' => $companies_dictonary,
                    'space_data' => $space_data,
                    'company_id' => $company_id,
                    'space_members' => $space_members,
                    'space_user' => $space_user,
                    'data' => $active_user_space_data,
                    'space_id'=>$id,
                    'search' => $search
                ]);
    }

    public function shareLogo(Request $request) {
        $data1 = $request->all();
        $data = $request['company_logo_fetch'];
        $type = $request['type'];
        if (!isset($data1['clearbit_value'])) {
            return 0;
        }
        $url = "https://autocomplete.clearbit.com/v1/companies/suggest?query=" . $data1['clearbit_value'];
        //  Initiate curl
        $ch = curl_init();
        // Disable SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // Will return the response, if false it print the response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Set the url
        curl_setopt($ch, CURLOPT_URL, $url);
        // Execute
        $result = curl_exec($ch);
        // Closing
        curl_close($ch);
        // Will dump a beauty json :3
        $logo_array = json_decode($result, true);
        $data = "";
        if (!empty($logo_array)) {
            foreach ($logo_array as $logo_data) {
                if (@!is_array(getimagesize($logo_data['logo']))) {
                } else {
                    $data .= "<a src='' class='ffff' type='" . $type . "'' logo='" . $logo_data['logo'] . "' name='" . $logo_data['name'] . "' domain='" . $logo_data['domain'] . "' onclick='return select_logo_fromlist(event, this)'><div class='item'><span class='logo'><img src='" . $logo_data['logo'] . "' style='width:20px;'/></span> <span class='name'>" . $logo_data['name'] . "</span> <span class='domain'>" . $logo_data['domain'] . "</span></div></a>";
                }
            }
        }
        return $data;
    }

    public function editcategory_ajax(Request $request) {
        $data = $request->all();
        if (isset($data['spaceid'])) {
            $spaace_id = $data['spaceid'];
        } else {
            abort(404);
        }
        $categories = Space::where('id', $spaace_id)->get()->toArray();
        if (!empty($categories)) {
            $da = array_filter($categories[0]['category_tags']);
            if (!empty($da)) {
                return view('pages/edit_category_ajax', ['categories' => $categories, 'space_id' => $spaace_id]);
            } else {
                return '';
            }
        }
    }

    public function save_editcategory_ajax(Request $request) {
        $data = $request->all();
        if (isset($data['spaceid'])) {
            $space_id = $data['spaceid'];
        } else {
            abort(404);
        }
        unset($data['spaceid'], $data['_token'], $data['delete_category']);
        if(sizeOfCustom($data) !== sizeOfCustom(array_unique($data))){
           return ['result'=>false];
        }
        $data = array_filter($data);
        $data['space']['category_tags'] = json_encode($data);
        Space::where('id', $space_id)->update($data['space']);
        /* Log "update comments" event */
        (new Logger)->log([
            'action' => 'edit post category',
            'description' => 'edit post category'
            ]);
        $space_data = Space::getSpaceBuyerSeller($space_id);
        $space_user = SpaceUser::getSpaceUserRole($space_id, Auth::user()->id);
        $space_data['space_user'] = $space_user;
        Session::put('space_info', $space_data);
        (new Logger)->mixPannelInitial(Auth::user()->id, $space_id, Logger::MIXPANEL_TAG['edit_category']);
        return ['result'=>true];
    }

    function cancel_editcategory_ajax(Request $request) {
        $data = $request->all();
        $space_id = $data['spaceid'];
        $data = Space::where('id', $space_id)->get()->toArray();
        $data_return = "<ul class='categories'>";
        $data_return .= "<li><a href='" . $space_id . "'?tokencategory=category_1' class='chip disable'><span>General</span></a></li>";
        $data_return .= "<li><a href='" . $space_id . "'?tokencategory=category_1' class='chip disable'><span>" . $data[0]['category_tags']['category_2'] . "</span></a></li>";
        $data_return .= "<li><a href='" . $space_id . "'?tokencategory=category_1' class='chip disable'><span>" . $data[0]['category_tags']['category_3'] . "</span></a></li>";
        $data_return .= "<li><a href='" . $space_id . "'?tokencategory=category_1' class='chip disable'><span>" . $data[0]['category_tags']['category_4'] . "</span></a></li>";
        $data_return .= "<li><a href='" . $space_id . "'?tokencategory=category_1' class='chip disable'><span>" . $data[0]['category_tags']['category_5'] . "</span></a></li>";
        $data_return .= "<li><a href='" . $space_id . "'?tokencategory=category_1' class='chip disable'><span>" . $data[0]['category_tags']['category_6'] . "</span></a></li>";
        $data_return .= "</ul>";
        return $data_return;
    }

    function deleteCategory(Request $request){
        $data = $request->all();
        if (empty(trim($data['spaceid']))) {
           abort(404); 
         }
        if(empty(trim($data['delete_category']))){
            return ['result'=>false]; 
        }

        $space_id = trim($data['spaceid']);
        $removed_category = trim($data['delete_category']);
        $general_category = 'category_1';
        unset($data['spaceid'], $data['_token'], $data[$removed_category], $data['delete_category']);
        $data = array_filter(array_unique($data));
        $data['space']['category_tags'] = json_encode($data);
        Post::updatePostCategory($space_id, $removed_category, $general_category);
        Space::updateSpaceById($space_id, $data['space']);

        (new Logger)->log([
            'action' => 'delete post category',
            'description' => 'delete post category and category posts are assigned to general category'
            ]);
        $space_data = Space::getSpaceBuyerSeller($space_id);
        $space_user = SpaceUser::getSpaceUserRole($space_id, Auth::user()->id);
        $space_data['space_user'] = $space_user;
        Session::put('space_info', $space_data);
        (new Logger)->mixPannelInitial(Auth::user()->id, $space_id, Logger::MIXPANEL_TAG['delete_category']);
        return ['result'=>true];
    }

    function getLikedUserInfo(Request $request) {
        $data = $request->all();
        if (isset($data['uid'])) {
            $userid = $data['uid'];
        } else {
            $userid = "";
        }
        $spaceid = Session::get('space_info')['id'];
        $userdata = SpaceUser::where('space_id', $spaceid)->where('user_id', $userid)->with('user', 'sub_comp')->get()->toArray();
        if (!sizeOfCustom($userdata)) {
            abort(404);
        }
        foreach ($userdata as $key => $udata) {
            if ($udata['metadata']['user_profile']['company'] != '') {
                $company_name = Company::select('company_name')->where('id', $udata['metadata']['user_profile']['company'])->get();
                if (isset($company_name[0])) {
                    $userdata[$key]['company_name'] = $company_name[0]['company_name'];
                } else {
                    $userdata[$key]['company_name'] = '';
                }
            }
        }
        (new Logger)->mixPannelInitial(Auth::user()->id, $spaceid, Logger::MIXPANEL_TAG['view_member']);
        return json_encode($userdata);
    }

    function save_background_image(Request $request) {
        $data = $request->all();
        if(isset($data['image']) && isset($data['spaceid'])) {
            $banner = $data['image'];
            $banner_logo = NULL;
            if ($banner != '') {
                $banner_logo = $this->bannerUpload($banner);
                $banner_logo = $this->save_logo($banner_logo, config('constants.URL_EXIST'));
            }
            $space = Space::updateSpaceById($data['spaceid'],['background_image' => json_encode($banner_logo)]);
            dispatch(new EmailHeaderImage($data['spaceid']));
            return $space;
        }
    }

    public function save_logo($file, $status = false) {
        if(is_object($file)){
            $originalfile = $file->getClientOriginalName();
            $extension = $file->guessExtension();
            $stream_context = '';
        }else if(in_array(pathinfo($file, PATHINFO_EXTENSION), config('constants.IMAGE_EXTENSIONS'))) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            $url_array = explode('/', $file);
            $originalfile = end($url_array);
            $stream_context = array("ssl"=>array(
                                    "verify_peer"=>false,
                                    "verify_peer_name"=>false),
                                    );  

        }
        $name = time().'.'.$extension;
        if(in_array(pathinfo($file, PATHINFO_EXTENSION), config('constants.IMAGE_EXTENSIONS'))) 
            $name = $originalfile;
        $s3 = \Storage::disk('s3');
        $s3_bucket = env("S3_BUCKET_NAME");
        $file_path = '/company_logo/' . $name;
        $full_image_url = config('constants.s3.url').''.$s3_bucket . '' . $file_path;
        if(!empty($stream_context))
            $s3->put($file_path, file_get_contents($file, false, stream_context_create($stream_context)), 'public');
        else
            $s3->put($file_path, file_get_contents($file), 'public');
        
        if($status){
            $name = time().rand().'.'.$extension;
            $image = Image::make($full_image_url);
            $image_resize = $image->resize(1280, 128);
            $image_resize->encode($extension);
            $file_path = '/company_logo/' . $name;
            $s3->put($file_path, (string) $image_resize, 'public');
        }
        return [
            'path' => ['company_logo'],
            'file' => $name
        ];
    }

    public function disable_account(Request $request) {
        $data = $request->all();
        $disable_data = date("Y-m-d h:i:s");
        if (isset($data['sp_id'])) {
            $check = SpaceUser::where('space_id', $data['sp_id'])->where('user_id', Auth::User()->id)->update(['deleted_at' => $disable_data, 'user_status' => '1']);
        } else {
            abort(404);
        }

        (new Logger)->log([
            'description' => 'disable account',
            'action' => 'disable account by user'
        ]);
        if (sizeOfCustom($check)) {
            $space = SpaceUser::where('user_id', Auth::user()->id)->whereRaw("metadata #>> '{user_profile}' != ''")->where('user_status', '0')->orderBy('created_at', 'desc')->first();
            if (sizeOfCustom($space)) {
                (new Logger)->mixPannelInitial(Auth::user()->id, $data['sp_id'], Logger::MIXPANEL_TAG['disable_account']);
                return redirect('clientshare/' . $space->space_id);
            } else {
                Session::flush();
                return redirect('/');
            }
        } else {
            Session::flush();
            return redirect('/');
        }
    }

    public function updateShowtour(Request $request) {
        return User::updateUserShowTour(Auth::user()->id);
    }


    public function makeroundimage() {
        ini_set('max_execution_time', -1);
        $space_data = Space::where('round_img_status', '0')->get();
        foreach ($space_data as $comp_logo) {
            echo $comp_logo['share_name'];
            echo'<br>';
            if (!empty($comp_logo['company_seller_logo'])) {
                $seller_processed_logo = $this->make_circle_image($comp_logo['company_seller_logo'], $nme = 'c_logo');

                $buyer_processed_logo = '';
                if (!empty($comp_logo['company_buyer_logo'])) {
                    $buyer_processed_logo = $this->make_circle_image($comp_logo['company_buyer_logo'], $nme = 'c_logo');
                }
                $editdata = [
                'seller_processed_logo' => $seller_processed_logo,
                'buyer_processed_logo' => $buyer_processed_logo,
                'round_img_status' => '1',
                ];
                Space::where('id', $comp_logo['id'])->update($editdata);
            }
        }
    }

    public function make_circle_image($logo = null, $nme = 'test_name') {
        $logo = $logo ?? "https://static1.squarespace.com/static/570f14c162cd9476aad3e272/570f32adb09f95c9bee19dba/5715c255859fd05621051bb0/1461043799256/logo-web-light1.png";
        $logo = str_replace(" ", "+", $logo);
        $image = Image::make($logo);
        $image->encode('png');
        $image->resize(125, 125);
        $width = $image->getWidth();
        $height = $image->getHeight();
        $canvas_size = $width <= $height ? $width : $height;
        $mask = \Image::canvas($canvas_size, $canvas_size);
        // draw a white circle
        $mask->circle($canvas_size, $canvas_size / 2, $canvas_size / 2, function ($draw) {
            $draw->background('#fff');
            $draw->border(1, '#FA0505');
        });
        $data = $image->mask("https://s3-eu-west-1.amazonaws.com/clientshare-docs/1488362727.png", true)->encode('data-url');
        return $image->response();
    }

    public function merge_two_image($logo1, $logo2) {
        $path = '../public/storage/company_logos/';
        $img_canvas = Image::canvas(280, 200);
        $img1 = Image::make($logo1)->fit(130);
        $img2 = Image::make($logo2)->fit(160);
        $img_canvas->insert($img1, 'top-left', 15, 18);
        $img_canvas->insert($img2, 'top-right', 15, 10);
        $name = rand() . "_" . time();
        $img_canvas->save($path . $name . '.png', 100);
        $s3 = \Storage::disk('s3');
        $s3_bucket = env("S3_BUCKET_NAME");
        $filePath = '/company_logo/' . $name;
        $fulleurl = "https://s3-eu-west-1.amazonaws.com/" . $s3_bucket . "" . $filePath;
        $s3->put($filePath, file_get_contents($path . $name . '.png'), 'public');
        return $fulleurl;
    }

    public function expandPost(Request $request) {
        $data = $request->all();
        if (isset($data['postid'])) {
            $postid = $data['postid'];
        } else {
            abort(404);
        }
        $type = $data['type'];
        $flight = PostUser::firstOrCreate(['post_id' => $postid, 'user_id' => Auth::user()->id]);
        $flight->minimize = $type;
        $flight->save();

        return (new Logger)->log([
            'description' => 'expand|minimize post',
            'action' => 'expand|minimize post'
            ]);
    }

    public function search_sub_comp(Request $request) {
        $data = $request->all();
        $space_id = $data['space_id'];
        $sub_comp_details = SpaceUser::getSpaceSubCompanyIdList($space_id, $data['comp']);
        return view('pages/sub_comp_search', ['sub_comp_details' => $sub_comp_details]);
    }

    public function delete_space(Request $request) {
        $input = $request->all();
        if (isset($input['sapce_id'])) {
            $space_users_info = SpaceUser::getSpaceUsersInfo($input['sapce_id']);
            $space_admin_info = SpaceUser::getSpaceAdminInfo($input['sapce_id']);
            if (sizeOfCustom($space_admin_info) > 0 && sizeOfCustom($space_admin_info) == 1) {
                $data['admin_email_list'] = $space_admin_info[0]['user']['email'];
            } elseif(sizeOfCustom($space_admin_info) > 1) {
                $emails = Array();
                foreach ($space_admin_info as $u)
                    $emails[] = $u['user']['email'];
                $list = implode(", ", $emails);
                $data['admin_email_list'] = $list;
            }
            if(!empty($space_users_info)){
            foreach ($space_users_info as $value) {
                if (!empty($value['user']) && !empty($value['share'])) {
                    $data['to'] = $value['user']['email'];
                    $data['subject'] = "Cancelled - The " . $value['share']['share_name'] . " Client Share";
                    $data['link'] = url('/');
                    $data['user_first_name'] = $value['user']['first_name'];
                    $data['user_last_name'] = $value['user']['last_name'];
                    $data['space_id'] = $value['space_id'];
                    $data['share_name'] = $value['share']['share_name'];
                    $data['seller_logo'] = $value['share']['seller_processed_logo'];
                    $data['buyer_logo'] = $value['share']['buyer_processed_logo'];
                    $data['admin_email'] = preg_replace("/\b" . $value['user']['email'] . ",?|(,\s)?" . $value['user']['email'] . "/i", "", $data['admin_email_list']);
                    $get_active_space = $value['user']['active_space'];
                    $act_space = json_decode($get_active_space, true);
                    if (isset($act_space['last_space'])) {
                        if ($value['space_id'] == $act_space['last_space']) {
                            User::where('id', $value['user']['id'])->update(['active_space' => '{"":""}']);
                        }
                    }
                    if($input['user_email_alert'] == config('constants.REQUESTED_FORM.status.true')) 
                    (new MailerController)->deleteShareEmail('email.delete_share_email', $data);
                }
            }//end for each
        }
            Space::findorfail($input['sapce_id'])->delete();
            SpaceUser::where('space_id', $input['sapce_id'])->update(['deleted_at' => date("Y-m-d h:i:s"), 'user_status' => '1']);
        }
    }

    public function pendingInvites(){
        ini_set('max_execution_time', -1);
        if(date('w') != env('pending_invites', Config::get('constants.email.pending_invites.day')) ) return 0;
        $pending_invitations = SpaceUser::pendingInvites();

        foreach ($pending_invitations as $key => $pending_invitation) {
            $pending_invitation['message'] = "You have a pending invitation from " . $pending_invitation['InvitedBy']['first_name'] . " " . $pending_invitation['InvitedBy']['last_name'] . " to join the " . $pending_invitation['share']['share_name'] . " Client Share.";
            if( !isset($top_post[$pending_invitation['space_id']]) ){
                $top_post[$pending_invitation['space_id']] = $this->topPosts(date('m'), date('Y'), $pending_invitation['space_id'], $pending_invitation['user_id']);
            }
            $pending_invitation['top_posts'] = $top_post[$pending_invitation['space_id']];
            $pending_invitation['mail_headers'] = ['X-PM-Tag' => trans('messages.mail_tags.pending_invite_reminder'), 'space_id' => $pending_invitation['space_id']];
            (new MailerController)->pendingEmail('email.pending_email', $pending_invitation);
        }
    }

    public function cancelInvitationFromMail($id) {
        $data = SpaceUser::getSpaceUserMetaDate($id);
        $data['invitation_status'] = Config::get('constants.INVITATION_STATUS_CANCELLED');
        $data['invitation_code'] = Config::get('constants.INVITATION_CODE_FOR_REMOVED_USER');
        (new Logger)->log([
            'action' => 'cancel invitation',
            'description' => 'cancel invitation'
            ]);
        SpaceUser::where('id', $id)->update(['metadata' => json_encode($data)]);
        return view('errors.404', ['login_btn' => false, 'message' => 'Your invitation has been cancelled']);
    }

    public function postViewCallback(Request $request) {
        $data = $request->all();
        $logs['action'] = 'Post View Callback';
        $logs['metadata'] = json_encode($data);
        ActivityLog::create($logs);
        if (isset($data['MessageID'])) {
            dispatch(new PostViewCallback($data['MessageID']));
        }
        return;
    }
    public function sendEmailUserLikedPost($user_id,$endorse_id,$post_author){
        $user_liked_post   = User::find($user_id);
        $post_subject = Post::postById($endorse_id);
        $author = User::find($post_author);
        $people_reacted =  $user_liked_post->getPeopleReacted((new Post)->getAllReactedUsers($endorse_id,[$user_id,$post_subject->user_id]));
        $space_info = Space::find($post_subject->space_id);
        $liked_data['post_subject'] = $post_subject->post_subject;
        $liked_data['post_description'] = $post_subject->post_description;
        $liked_data['user_liked_post'] = $user_liked_post;
        $liked_data['space_name']  = $space_info->share_name;
        $liked_data['seller_logo']  = $space_info->seller_circular_logo;
        $liked_data['buyer_logo']  = $space_info->buyer_circular_logo;
        $liked_data['space_id']    = $space_info->id;
        $liked_data['post_id']    = $endorse_id;
        $liked_data['people_reacted'] = $people_reacted;
        $liked_data['respond_link'] = url("/clientshare").'/'.$post_subject->space_id.'/'.$post_subject->id;
        $liked_data['share_link'] = url("/clientshare").'/'.$post_subject->space_id;
        $liked_data['user_liked_profile_picture'] = $user_liked_post->getImageOrInitialsEmail();
        $liked_data['unsubscribe_share'] = env('APP_URL') . "/setting/" . $space_info->id . "?email=".base64_encode($author->email). '&alert=true&via_email=1&notification=1&tab_name=notifications-tab';
        $space_user_info = SpaceUser::getSpaceUserInfo($space_info->id,$post_author);
        if ($space_user_info[0]['like_alert'] && $space_user_info[0]['user_status'] == 0) {
            (new MailerController)->postLikedByUser($post_author, $liked_data);
        }
    }

     public function createInviteLink(Request $request) {
        if(!isset($request->all()['share_id'])) abort(404);
        $input = $request->all();
        $validator = Validator::make($input, [
                'first_name' => array('required','max:25'),
                'last_name' => array('required','max:25'),
                'email' => array('required', 'email', 'not_in:' . Config::get('constants.email.restricted_emails')),
            ], [
                'required' => 'This field is required',
                'first_name.max' => 'First name cannot be greater than 25 characters',
                'last_name.max' => 'Last name cannot be greater than 25 characters',
                'email' => 'Invalid email address.',
                'email.not_in' => 'Email cannot be used for share.'
            ]
        );
        if ($validator->fails()) {
            return ['code' => HttpResponse::HTTP_UNAUTHORIZED, 'message' => $validator->errors()];
        }
        $input['user_id'] = Auth::user()->id;
        $user = User::getUserIdFromEmail($input['email']);

        $share = Space::find($input['share_id']);
        $metadata_rules = array();
        $tags = "";
        if ($share['domain_restriction'] && !isset($input['call_via_bulk_invitation']) ) {
            if(isset($share['metadata']['rule'])){
                foreach ($share['metadata']['rule'] as $v) {
                    $metadata_rules[] = '@' . $v['value'];
                }
                $tags = implode(', ', $metadata_rules);
            }
        }
        $user_name = SpaceUser::getSpaceAdminInfo($input['share_id']);
        $user_emails = array();
        foreach ($user_name as $user_data) {
            $user_emails[] = $user_data['user']['email'];
        }
        $username = implode(', ', $user_emails);


        if ($share['domain_restriction'] && !isset($input['call_via_bulk_invitation']) && !isset($input['resend_mail'])) {
            $invitee_email = explode("@", $input['email']);
            $common = false;
            if (isset($share['metadata']['rule'])) {
                foreach ($share['metadata']['rule'] as $key => $value) {
                if (strtolower($value['value']) == strtolower($invitee_email[1])) $common = true;
            }
            }
            if (!$common) {
                $validator->getMessageBag()->add('email', trans('messages.validation.invite_link',
                    ['user_name' => $username, 'tags' => $tags]));
                return ['code' => HttpResponse::HTTP_UNAUTHORIZED, 'message' => $validator->errors()];
            }
        }
        if (sizeOfCustom($user)) {
            $space_user = SpaceUser::getSpaceUserInfo($input['share_id'],$user->id);
            if (sizeOfCustom($space_user) && !isset($data['resend_mail'])) {
                if (!isset($space_user[0]['metadata']['invitation_code']) || ($space_user[0]['metadata']['invitation_code'] == 1)) {
                    return ['code' => Config::get('constants.BAD_REQUEST'), 'message' => trans('messages.validation.already_member_of_share')];
                }
            }
        }

        if(isset($input['user_type'])) {
            $input['user_type_id'] = UserType::USER_TYPE[$input['user_type']];
        }

        $invitation = Invitation::saveInvitedUser($input);
        $url = env('APP_URL').'/registeruser?invite_id='.$invitation->id;
        $invite_url = $this->shortUrl($url);
        (new Logger)->mixPannelInitial(Auth::user()->id, $input['share_id'], Logger::MIXPANEL_TAG['single_url_invite']);
        return ['success'=>true, 'url' => $invite_url];
    }

    public function shortUrl($long_url) {
        $otp = $this->generate_otp(['app_url'=>$long_url, 'method'=>'get'] );
        return env('APP_URL').'/u/'.$otp->id;
    }

    public function shortUrlRedirect($id){
        $otp = $this->otpGetUrl($id);
        if(!$otp) abort(404);
        header("Location: ".$otp['app_url']);
        exit();
    }

    public function copyUserToNewShare(Request $request) {
      $data = $request->all();
      $uploaded_file = $request->file('user_list'); 
      $file_name = $_FILES['user_list']['name'];
      $extension = explode(".", $file_name); 
      if(trim($data['new_share_name']) == '' || trim($data['new_share_name']) == config('constants.DEFAULT_SHARE_NAME') || trim($data['company_name']) == '' || trim($data['company_name']) == config('constants.DEFAULT_COMPANY_NAME')){
        return ['success'=>false, 'message' => "Please add all details"];
      } 
      if(empty($uploaded_file) || (!empty($uploaded_file) && $extension[1] != 'csv')){
        return ['success'=>false, 'message' => "Please upload the csv file"];
      }
      $share_name = $data['new_share_name'];
      $space_data = Space::find($share_name);
      if(empty($space_data)){
        return ['success'=>false, 'message' => "New share not exist"];
      }
      $company_data = Company::find($data['company_name']);
      if(empty($company_data)){
        return ['success'=>false, 'message' => "Company not exist"];
      }

      $file = fopen($uploaded_file,"r");
      $header=fgetcsv($file);         
      while(!feof($file)){
        $row = fgetcsv($file);
        if(!$row) continue;

        $new_users[] = array_combine($header, $row);
      }
      $share = Space::find($space_data['id']);
      $invite_user_emails = $domain_rule = array();

      if(isset($share['metadata']['rule']) && $share['domain_restriction']){
          foreach ($share['metadata']['rule'] as $key => $value)
              $domain_rule[] = strtolower($value['value']);
      }

      $errors = [];
      if(isset($new_users)) {
      foreach ($new_users as $new_user) {
        $new_user = array_change_key_case($new_user,CASE_LOWER);
        $error = null;
                if (!isset($new_user['first_name']) || !isset($new_user['last_name']) || !isset($new_user['email'])) {
                    return ['success' => false, 'message' => "Please check the uploaded file format again"];
                }
                $user = User::getUserIdFromEmail($new_user['email']);
        $space_user = SpaceUser::getSpaceUserInfo($data['old_share_id'],$user['id'], 'first',true);

        $check_duplicate_entry = SpaceUser::getSpaceUserInfo($space_data['id'], $user['id'], 'first');

        if(!sizeOfCustom($space_user)) $error = 'Unable to find any details from source share.';
        if(!sizeOfCustom($user)) $error = 'This user is not exist in database.';
        if(sizeOfCustom($check_duplicate_entry)) $error = 'This user is already member of this share.';
        if(!empty($domain_rule)){ 
            $invitee_email = explode("@", $new_user['email']);
            if(isset($invitee_email[1]) && !in_array(strtolower($invitee_email[1]), $domain_rule))
                $error = trans('messages.error.domain_restriction');
        }

        if($error){
            $errors[] = [
                'user_email' => $new_user['email'],
                'message' => $error
            ];
            continue;
        }

        $space_user_metadata = $space_user['metadata'];
        $space_user_metadata['user_profile']['space_id'] = $space_data['id'];
        $space_user_metadata['user_profile']['company_name'] = $company_data['company_name'];
        $space_user_metadata['user_profile']['company'] = $company_data['id'];
        $new_space_user = [
            'space_id' => $space_data['id'],
            'user_id' => $space_user['user_id'],
            'sub_company_id' => $space_user['sub_company_id'],
            'user_type_id' => $space_user['user_type_id'],
            'created_by' => Auth::user()->id,
            'user_company_id' => $company_data['id'],
            'doj' => 'now()',
            'metadata'=> $space_user_metadata,
        ];
       SpaceUser::create($new_space_user);
      } 
          if(sizeOfCustom($errors))
            return $this->genarteErrorReport([
                'errors' => $errors,
                'requested_users_count' => sizeOfCustom($new_users)
            ]);
      }
      
      return ['success'=>true, 'message' => "Users migrate successfully."];
    }

    public function genarteErrorReport($report_data){
        array_unshift($report_data['errors'], ['email', 'error message']);
        
        $file =  \Excel::create('testing', function($excel) use($report_data){
            $excel->sheet('General', function($sheet) use($report_data){
                $sheet->fromArray($report_data['errors'], null, 'A1', true, false);
            });
        });

        $report_data['file_path'] = uploadFileOnS3([
            'folder' => '/user_copy_error_reports/',
            'file_name' => time().'.xls',
            's3_url' => config('constants.s3.url'),
            'file_content' => $file->string('xls')
        ]);

        $this->sendErrorReport($report_data);
        return ['success'=>false, 'message' => "We had a problem migrating one or more users. Please check your inbox for more information."];
        
    }

    public function sendErrorReport($report_data){
        $mail_data['sender'] = User::find(Auth::user()->id)->toArray();
        $mail_data['view'] = 'email.user_migrate_error_report';
        $mail_data['file_path'] = $report_data['file_path'];
        $mail_data['path'] = env('APP_URL');
        $mail_data['report_data'] = $report_data;

        \Mail::send($mail_data['view'], ['mail_data'=>$mail_data], function ($message) use ($mail_data){
          $message->from(env("SENDER_FROM_EMAIL"), env("SENDER_NAME"));
          $message->to(env('ERROR_REPORT_USER', $mail_data['sender']['email']));
          $message->subject("Error - while creating user(s).");
        });
    }

    public function shareCommunities(Request $request, $space_id){
        return Space::getSpaceBuyerSeller($space_id);
    }

    public function userSearchForm() {
        return view('share/user_search');
    }

    public function getS3FormData(){
       $s3_form_details = (new Aws)->uploadClientSideSetup();
       return response()->json(['result'=>true, 's3_form_details'=>$s3_form_details]);
    }

    public function spaceSearchByUser(Request $request){
        $request_input = $request->all();
        $user_shares = User::searchSpaceByUser(
            $request_input['first_name'],
            $request_input['last_name'],
            $request_input['email']
        );
        
        if(!empty($user_shares)){
            foreach($user_shares as $user_share){
                $result['data'] = $user_share;
                $space_buyer_info[] = $result;
            }
            return $space_buyer_info;
        }
        return ['data'=>false];
    }

    public function twitterAPI(Request $request){
        $request_data = $request->all();
        if(substr($request_data['option']['screen_name'], 0, 1) != '@' || trim(substr($request_data['option']['screen_name'], 1)) == ""){
            return response()->json(['result'=>false, 'error'=>'Please add valid twitter handle e.g. @handle']);
        }
        return $this->checkTwitter($request_data);
    }

    private function checkTwitter($request_data) {
        $request_data['request_url'] = urldecode($request_data['request_url'])?? Config::get('constants.TWITTER_REQUEST_URL');
        $request_data['request_method'] = $request_data['request_method']?? 'get';
        $request_data['options'] = $request_data['options'] ?? [];

        $connection = new TwitterOAuth(env('TWITER_CONSUMER_KEY'), env('TWITER_CONSUMER_SECRET'),env('TWITER_ACCESS_KEY'), env('TWITER_ACCESS_TOKEN_SECRET'));
        $response = $connection->get($request_data['request_url'], $request_data['option']);
        if(!empty($response->errors)){
            $error = $response->errors;
            return response()->json(['result'=>false, 'error'=>$error[0]->message]);
        }
        return response()->json(['result'=>true, 'data'=>$response]);
    }

    public function attachmentUrlRedirect($otp){
        $attachment_url = $this->getAttachmentUrlByOtp($otp);
        if(!$attachment_url) abort(404);
            header("Location: ".$attachment_url);
        exit();
    }

    private function bannerUpload($banner){
        list($type, $data_image) = explode(';', $banner);
        list(, $data_image) = explode(',', $banner);
        $explode_data = explode('base64', $banner);
        $explode_data_image = explode('image/', $explode_data[0]);
        $data_image = base64_decode($data_image);
        $new_extension = str_replace(";", "", $explode_data_image[1]);
        $image_file_name = rand() . "." . $new_extension;
        $s3 = \Storage::disk('s3');
        $s3_bucket = env("S3_BUCKET_NAME");
        $file_path = '/company_logo/' . $image_file_name;
        $banner_image_path = config('constants.s3.url') . $s3_bucket . $file_path;
        $img = Image::make($data_image);
        $image_med = $img->resize(1280, 128);
        $image_med->encode($new_extension);
        $s3->put($file_path, (string) $image_med, 'public');
        return $banner_image_path;
    }

    private function uploadLogo($logo){
        try{
                $file_data = [
                    'folder' => '/company_logo/',
                    'file_name' => rand().time().'.png',
                    's3_url' => config('constants.s3.url'),
                    'file_content' => file_get_contents($logo)
                ];
                uploadFileOnS3($file_data);
                $logo_path = [
                    'path' => ['company_logo'],
                    'file' => $file_data['file_name']
                ];
                return $logo_path;
        } catch(\Exception $e){}
    }

    public function inactiveUser(Request $request, GroupUserRepository $group_user) {
        $data = $request->all();

        SpaceUser::updateByUserSpace(
            $data['id'],
            $data['space_id'],
            ['deleted_at'=>'now()', 'user_status' => '1']
        );
        $user_id = $data['id'];
        $space_id = $data['space_id'];
        $group_user->DeleteUserFromAllGroups($user_id, $space_id);

        (new Logger)->log([
          'user_id'     => Auth::user()->id,
          'content_id'  => SpaceUser::where('space_id',$data['space_id'])->where('user_id',$data['id'])->get()[0]['id'],
          'content_type'=> 'App\SpaceUser',
          'action'      => 'remove',
          'description' => 'Remove User',
          'metadata'    => ['removed_by'=>Auth::user()->id, 'removed_user'=>$data['id'], 'space_id'=>$data['space_id']]
        ]);
        (new Logger)->mixPannelInitial(Auth::user()->id, $data['space_id'], Logger::MIXPANEL_TAG['remove_user']);
    }

    public function promoteUserAsAdmin(Request $request) {
        $data = $request->all();
        $userdata =  ['user_type_id' => UserType::USER_TYPE['admin']];
        
        SpaceUser::updateByUserSpace($data['userid'], $data['spaceid'], $userdata);
        
        (new Logger)->log([
            'action'     => 'promote user',
            'description' => 'promote user as admin'
        ]);
        (new Logger)->mixPannelInitial(Auth::user()->id, $data['spaceid'], Logger::MIXPANEL_TAG['promote_admin']);
    }

    public function getShareProfileStatus(Request $request){
        $request_data = $request->all();
        if(isset($request_data['space_id']) && $request_data['space_id']) {
            $share_profile_status = (new Space)->getShareProfileData($request_data['space_id']);
            $space_admin = (new SpaceUser)->getSpaceAdmin($request_data['space_id']);
            $data['space_admin_data'] = [];
            if(sizeOfCustom($space_admin) > 0)
                $data['space_admin_data'] = $space_admin;

            if(!empty($share_profile_status)) {
                $data['logo'] = $data['background_image'] = $data['category_flag'] = false;
                $data['category'] = $data['executive_summary'] = false;
                $data['quick_links'] = $data['twitter_handles'] = $data['domain'] = false;
                $data['posts'] = $data['space_users'] = $data['space_admin'] = false;
                $data['progress'] = $task_completed = config('constants.COUNT_ZERO');
                if($share_profile_status['seller_logo'] && $share_profile_status['buyer_logo']) {
                    $data['logo'] = true;
                    $task_completed = $task_completed+config('constants.COUNT_ONE');
                }

                if($share_profile_status['background_image']) {
                    $data['background_image'] = true;
                    $task_completed = $task_completed+config('constants.COUNT_ONE');
                }
                 
                if(sizeOfCustom($share_profile_status['category_tags']) >= 6) {
                    $data['category'] = true;
                    $task_completed = $task_completed+config('constants.COUNT_ONE');
                }
                
                if($share_profile_status['executive_summary']) {
                    $data['executive_summary'] = $data['category_flag'] = true;
                    $task_completed = $task_completed+config('constants.COUNT_ONE');
                }

                if($share_profile_status['quick_links_count'] >= 2) { 
                    $data['quick_links'] = $data['category_flag'] = true;
                    $task_completed = $task_completed+config('constants.COUNT_ONE');
                }

                if(strlen($share_profile_status['twitter_handles']) > 0 && $share_profile_status['twitter_handles'] != config('constants.EMPTY_JSON')) {
                    $data['twitter_handles'] = $data['category_flag'] = true;
                    $task_completed = $task_completed+config('constants.COUNT_ONE');
                }

                if(!$share_profile_status['domain_restriction'] || (isset($share_profile_status['metadata']['rule']) && sizeOfCustom($share_profile_status['metadata']['rule']) > 0)) {
                    $data['domain'] = $data['category_flag'] = true;
                    $task_completed = $task_completed+config('constants.COUNT_ONE');
                }

                if($share_profile_status['posts_count'] >= 5) {
                    $data['posts'] = $data['category_flag'] = true;
                    $task_completed = $task_completed+config('constants.COUNT_ONE');
                }
                $data['progress'] = config('constants.TASK_'.$task_completed.'_PROGRESS');
                $data['posts_count'] = $share_profile_status['posts_count'];

                if($share_profile_status['space_member_count'] >= 1)
                    $data['space_users'] = true;

                if($share_profile_status['space_admin_count'] > 1)
                    $data['space_admin'] = true;

                return ['result'=>true,'data' => $data];
            }
            return ['result'=>false,'data' => ''];
        }
        return ['result'=>false,'data' => ''];
    }

    public function UpdateShareVersion(Request $request){
        if(!$request->has('current_version') || !$request->has('space_id'))
            return json_encode(['status'=>false]);
        
        $space = Space::find($request->space_id);
        if(!$space)
            return json_encode(['status'=>false]);

        $data['version'] = $request->current_version == 0 ? 1 : 0;

        if($space->update($data))
            return json_encode(['status'=>true]);

        return json_encode(['status'=>false]);
    }

}
