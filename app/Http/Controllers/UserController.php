<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use Auth;
use Hash;
use App\User;
use App\UserType;
use App\Invitation;
use App\SpaceUser;
use App\Space;
use App\Helpers\Aws;
use App\SubCompany;
use DB;
use Session;
use Cookie;
use Storage;
use Image;
use App\ActivityLog;
use App\Company;
use App\Http\Controllers\MailerController;
use App\Helpers\Logger;
use App\Http\Controllers\ManageShareController;
use App\Jobs\{CopyProfileImagesToAWS, PostProfileUpdation, CreateCircularProfileImage};
use Validator;
use Redirect;
use App\BlockWords;
use Config;
use App\Traits\Generic;

class UserController extends Controller {
  use Generic;

  public function __construct() {
  }

  /**/
  public function update_share_user(Request $request) {
    $this->validate($request, [
      'user.first_name'               =>'required|max:25',
      'user.last_name'                =>'required|max:25',
      'job_title'                     =>'required',
      'sub_comp'=> 'sometimes|required',
      'user.contact.contact_number'   =>'max:24'
      ],[
      'required'=>'This field is required.',
      'user.first_name.max'=> 'First name cannot be greater than 25 characters',
      'user.last_name.max'=> 'Last name cannot be greater than 25 characters',
      'user.contact.contact_number.max' => 'Phone number cannot be greater than 24 characters'
      ]);
     $this->forget_linkedin_session($request); 
   
    $data = $request->all();
    $logged_in_user = Auth::user();
    (new Logger)->log([
      'action' => 'update profile logging',
      'description' => 'update profile data logging',
      'metadata' => $data
    ]);

    if(isset($data['sub_comp']) && $data['sub_comp']!='') { 
      $sub_comp = Company::whereRaw("lower(company_name) = lower('".str_replace("'", "",$data['sub_comp'])."')")->first(); 
      if( !sizeOfCustom($sub_comp) ) { 
        $sub_comp[0] = Company::create(['company_name'=>$data['sub_comp']]);   
        $post['sub_company_id'] = $sub_comp[0]['id'];   
      } else{    
        $post['sub_company_id'] = $sub_comp['id'];    
      }   
    } else{   
      $post['sub_company_id'] = config('constants.DUMMY_UUID')[0];
    }

    $space_user = SpaceUser::getOneSpaceUserInfo($request->space_id, $logged_in_user->id);
    if ( !sizeOfCustom($space_user) ) return 0;
    $metadata = $space_user['metadata'];
    $metadata['user_profile'] = $request->all();
    if( !empty($_FILES['file']['tmp_name']) ) {
      $mime = mime_content_type($_FILES['file']['tmp_name']);
      $mime = (array_filter(explode('/', $mime)));
      if (in_array(array_pop($mime), config('constants.IMAGE_EXTENSIONS'))) {
        $image = imagecreatefromstring(file_get_contents($_FILES['file']['tmp_name']));
        $exif = @exif_read_data($_FILES['file']['tmp_name']);
        if(!empty($exif['Orientation'])) {
          switch($exif['Orientation']) {
            case 8:
            $image = imagerotate($image,90,0);
            break;
            case 3:
            $image = imagerotate($image,180,0);
            break;
            case 6:
            $image = imagerotate($image,-90,0);
            break;
          }
        }
        $file = Input::file('file');
        $s3            = \Storage::disk('s3');
        $extension     = $file->guessExtension();
        $imageFileName = time() . '.' .$extension;
        $s3_bucket = env("S3_BUCKET_NAME");
        $filePath = '/' . $imageFileName;
        $fullurl = config("constants.s3.url").$s3_bucket."".$filePath;
        $imagemed = Image::make($image);
        $imagemed->encode($extension);
        $s3->put($filePath,(string) $imagemed, 'public');
        $data['user']['profile_image'] = filePathUrlToJson($fullurl);
      }
    } else {
      $user_exist = User::getUserInfo($logged_in_user->id, 'first');
      if($user_exist->profile_image=='' || $user_exist->profile_image_url=='') {
          if(isset($data['linkedin_image']) && $data['linkedin_image']!='')
              $data['user']['profile_image'] = filePathUrlToJson($data['linkedin_image']);
      }
     
    }
    if(!empty($metadata['user_profile']['bio'])) {
      $metadata['user_profile']['bio'] = trim($metadata['user_profile']['bio']);
    }
    
    SpaceUser::updateUserDataInSpaceUser(
      $request->space_id,
      $logged_in_user->id,
      ['metadata'=>json_encode($metadata),'sub_company_id'=>$post['sub_company_id']]
    );

    $data['user']['contact'] = json_encode($data['user']['contact']);

    if(isset($data['user']['profile_image']))
       $data['user']['profile_thumbnail'] = null; 
      
    User::updateUser($logged_in_user->id, $data['user']);

    $data_space = Space::getSpaceBuyerSeller($request->space_id);

    $active_space_user = SpaceUser::getActiveSpaceUser($request->space_id, $logged_in_user->id);

    $data_space['space_user'] = $active_space_user;      
    Session::put('space_info', $data_space);
    
    (new Logger)->log([
      'action' => 'update profile',
      'description' => 'update profile'
    ]);
    (new Logger)->mixPannelInitial($logged_in_user->id, $request->space_id, Logger::MIXPANEL_TAG['update_profile']);
    dispatch(new PostProfileUpdation()); 
    dispatch(new CopyProfileImagesToAWS()); 
    dispatch(new CreateCircularProfileImage($logged_in_user->id)); 
    return back();
  }

  /**/
  public function updateSpaceSessionData( $space_id,$user_id = null ){
    $spaceBuyerSeller = Space::getSpaceBuyerSeller($space_id);
    if(!sizeOfCustom($spaceBuyerSeller)){
      abort(404);
    }
    if(!empty($user_id)){
      $user_id = $user_id;
    }else{
      $user_id = Auth::user()->id;
    }
    $space_user = SpaceUser::getSpaceUserRole($space_id, $user_id);    
    $spaceBuyerSeller['space_user'] = $space_user;
    Session::put('space_info', $spaceBuyerSeller);
    if (isset(Session::get('space_info')['space_user'][0]) && isset(Session::get('space_info')['space_user'][0]['metadata']['user_profile']) && Session::get('space_info')['space_user'][0]['metadata']['user_profile']['company']) {
            $space_company = Company::where('id', Session::get('space_info')['space_user'][0]['metadata']['user_profile']['company'])->get();
            Session::put('space_company', $space_company[0]);
            $user = User::find($user_id);
            return $user->updateActiveSpace($space_id); 
    }
    return 0;
    }
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index() {
      //
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
      //
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
      //
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
      //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
      //
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
      //
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id) {
      
  }

  public function registerUser(Request $request, $user_id = null, $share_id= null) {
      $data = $request->all();

      if(isset($data['invite_id']) && !empty($data['invite_id'])){
        $invite =  $this->createInviteUser($data['invite_id']);
        if( !sizeOfCustom($invite) ){
          abort(404);
        }
        $data['email'] = $invite['email'];
        $data['invite'] = true;
        $user_id      = $invite['user_id'];
        $share_id = $invite['share_id'];
      }
      $user_info= User::find($user_id);
      $space_user_info = SpaceUser::getSpaceUserInfo($share_id,$user_id);
      if( !sizeOfCustom($space_user_info) ){
        abort(404);
      }
      if($space_user_info[0]['metadata']['invitation_code'] == -1  ) {
        return view('errors.404',['login_btn'=>false, 'message'=>'This invitation has expired or been cancelled,<br> please use your most recent invitation or contact <a href="mailto:support@myclientshare.com" class="big-bluelinks">support@myclientshare.com</a> for assistance.']);
      }
      if(Auth::check() && Auth::user()->id != $user_id) {
        if(isset($data['email'])){
          $data['email'] = $data['email'];
        }else{
          $data['email'] = '';
        }
        return redirect('logout?_userToken='.$user_id.'&_shareToken='.$share_id.'&email='.$data['email'].'&invite=true&status=logout&from=invite');
      } elseif (Auth::check() && Auth::user()->id == $user_id) {
        return redirect('addprofile?_shareToken='.$share_id);
      }
      $user_info =User::getUserInfo($user_id);
      $check_id = sizeOfCustom($user_info);
      $registration_status = $user_info[0]['registration_status'];        
      if ($registration_status == 0 && $check_id == 1) {
            return view('auth.userregister', ['user' => $user_info, 'shareid' => $share_id]);
        } else {
            if (isset($data['email']) && $data['email'] != '') {
                if (isset($data['invite']) && $data['invite']) {
                    return redirect(url('/clientshare/' . $share_id . '?_userToken=' . $user_id . '&_shareToken=' . $share_id . '&email=' . $data['email'] . '&intended=true'));
                }
                return redirect(env('APPURL') . '?_userToken=' . $user_id . '&_shareToken=' . $share_id . '&email=' . $data['email'] . '&intended=true');
            } else {
               return redirect('login?_userToken=' . $user_id . '&_shareToken=' . $share_id);
            }
      }
  }

  
  public function updateRegisterUser(Request $request) {
      if(!SpaceUser::verifyRegistration($request->all())) 
        return back()->withErrors(['verify_code'=> trans('messages.validation.registration_verification')]);

      $post = $request->all(); 
      if(isset($post['id'])){
        $id = $post['id'];
      }else{
        abort(404);
      } 
      if(isset($post['shareid'])) {
        $shareid = $post['shareid'];
      }else{
        abort(404);
      }
      $password = $post['password'];
      $first_name = $post['firstname'];
      $last_name = $post['lastname'];
      $v = \Validator::make($request->all(),
        [
        'firstname'=>'required|max:25',
        'lastname' =>'required|max:25',
        'password' =>'required|confirmed|min:8|max:60',
        'password_confirmation'=>''
        ],[
        'required' => 'This field is required'
        ] 
        );     
      if($v->fails()){         
        return redirect('registeruser/'.$id.'/'.$shareid)->withInput()->withErrors($v);
      } else {
        $updateQuery = User::where('id',  $id)->limit(1)->update(array('password' => bcrypt($password),'registration_status' => '1','first_name'=>$first_name,'last_name'=>$last_name));
        if(Auth::loginUsingId($id)){
          $username = $post['email'];
          if(Auth::attempt(['email' => $username, 'password' => $password])){
            (new Logger)->mixPannelInitial(Auth::user()->id, null, Logger::MIXPANEL_TAG['register']);
            return redirect('/clientshare/'.$shareid);
          }else{
            echo trans('messages.validation.user_login'); 
          }
        }else{ 
         return redirect('/login?_shareToken='.$shareid);  
        }  
     }    
  }

  public function update_admin_space_profile(Request $request) { 
      $post = $request->all(); 
      if(!isset($post['space_id']) || !$post['space_id']) abort(404);
      $this->forget_linkedin_session($request);    
      $space_id = $post['space_id'];
      $jobtitle = $post['jobtitle'];
      $company = $post['company'];
      $bio = $post['bio'];
      $linkedin = $post['linkedin'];
      $phone = $post['phone'];
      $first_name = $post['first_name'];
      $last_name = $post['last_name'];
      $token = $post['_token'];
      $v = \Validator::make($request->all(),
        [
        'first_name' =>'required|max:25',
        'last_name' =>'required|max:25',
        'jobtitle'=>'required',
        'company' =>'required',
        'phone' =>'max:24',
        'sub_comp'=> 'sometimes|required'

        ],[
        'required' => 'This field is required',
        'first_name.max'=> 'First name cannot be greater than 25 characters',
        'last_name.max'=> 'Last name cannot be greater than 25 characters',
        'phone.max' => 'Phone number cannot be greater than 24 characters'
        ] 
        );     
      if($v->fails()){         
        return redirect('clientshare/'.$space_id)->withInput()->withErrors($v);
      } else {
        if( !empty($_FILES['file']['tmp_name']) ) {
          $file = Input::file('file');
          $file_tmp = getimagesize($file);         
          $aws = new Aws;
          $file_temp['temp_name'] = $_FILES['file']['tmp_name'];
          $file_temp['name'] = 'file';
          $user_data['user']['profile_image'] = filePathUrlToJson($aws->image($file_temp, $request)['path']);
        }else{
          $user_exist = User::where('id', Auth::user()->id)->first();
          if($user_exist->profile_image_url == ''){
            if(isset($post['linkedin_image']) && $post['linkedin_image']!=''){
              $user_data['user']['profile_image'] = filePathUrlToJson($post['linkedin_image']);
            }
          }
        }
        $data_contact['linkedin_url']=trim($linkedin);
        $data_contact['contact_number']=trim($phone);
        $user_data['user']['contact']=json_encode($data_contact);
        $user_data['user']['first_name'] = trim($first_name);
        $user_data['user']['last_name'] = trim($last_name);
        User::where('id',Auth::user()->id)->update($user_data['user']);
        $space_users = SpaceUser::where('space_id', $space_id)->where('user_id', Auth::user()->id)->get()->toArray();
      if(isset($space_users[0]['metadata'])){
          $old_values = array('invitation_status'=>'member','invitation_code'=>1);
          $new_values = array(
          'user_profile' => array(
          "space_id"=>$space_id,
          "user"=>array(
          'first_tname'=>'',
          'last_name'=>'',
          'contact'=>array(
          'linkedin_url' => $linkedin,
          'contact_number' => $phone,
          )
          ),
          'job_title' => $jobtitle,
          'bio' => $bio,
          'company'=>$company,
          '_token'=>$token
          ));
          $meta_data = array_merge($old_values,$new_values);
          $data['metadata'] = json_encode($meta_data);
          $data['user_company_id'] = $company;
          $data['doj'] = 'now()';
          if(isset($post['sub_comp'])){ 
            $sub_comp = Company::whereRaw("lower(company_name) = lower('".str_replace("'", "",$post['sub_comp'])."')")->first();
            if( !sizeOfCustom($sub_comp) ) { 
              $sub_comp[0] = Company::create(['company_name'=>$post['sub_comp']]);     
              $data['sub_company_id'] = $sub_comp[0]['id'];   
            } else{    
              $data['sub_company_id'] = $sub_comp['id'];    
            }   
          } else{   
            $data['sub_company_id'] = '00000000-0000-0000-0000-000000000000';   
          }
          /*******************Send mail from james when he first time join share*************************/
          $space_user_count = SpaceUser::where('user_id', Auth::user()->id)->count();
          if(isset($space_user_count) && $space_user_count==1){
            if(isset($space_users) && $space_users[0]['doj']=="")
            (new MailerController)->shareJamesInvitation(Auth::user()->id, $space_id);
          }
          SpaceUser::where('space_id', $space_id)->where('user_id', Auth::user()->id)->update($data);
          $space_user_with_share = SpaceUser::where('space_id', $space_id)->where('user_id', Auth::user()->id)->with('Share')->with('User')->get(); 
          if((!empty($space_user_with_share[0]['Share']['seller_processed_logo'])) && (!empty($space_user_with_share[0]['Share']['company_seller_logo']))) {
            $company_seller_logo = $space_user_with_share[0]['Share']['seller_processed_logo']; 
          }elseif ((empty($space_user_with_share[0]['Share']['seller_processed_logo'])) && (!empty($space_user_with_share[0]['Share']['company_seller_logo']))) {
           $company_seller_logo = $space_user_with_share[0]['Share']['company_seller_logo'];
          }else {
          $company_seller_logo = env('APP_URL').'/images/login_user_icon.png';
          }
          if((!empty($space_user_with_share[0]['Share']['buyer_processed_logo'])) && (!empty($space_user_with_share[0]['Share']['company_buyer_logo']))) {
          $company_buyer_logo = $space_user_with_share[0]['Share']['buyer_processed_logo'];
          }elseif ((empty($space_user_with_share[0]['Share']['buyer_processed_logo'])) && (!empty($space_user_with_share[0]['Share']['company_buyer_logo']))) {
         $company_buyer_logo = $space_user_with_share[0]['Share']['company_buyer_logo'];
          }else {
         $company_buyer_logo = env('APP_URL').'/images/login_user_icon.png';
          }
        $data = Space::where('id',$space_id)->with('BuyerName','SellerName')->get()[0];
        $space_user = SpaceUser::with('user_role')->where('user_id', Auth::user()->id)->where('space_id', $space_id)->with('sub_comp')->get();
        $data['space_user'] = $space_user;
        Session::put('space_info', $data);
      if($space_user_with_share[0]['user_type_id'] != 2){ 
        /* Send Sucessfully accepted invitation Email */
        $invited_user = ActivityLog::where('description','Send Invitation')
        ->whereRaw("metadata #>> '{invited_to}' = '".Auth::User()->id."' and space_id = '".$space_id."'")
        ->orderBy('created_at', 'desc')->first();
        if( $invited_user ) {
         $sender_user = json_decode($invited_user["metadata"])->invited_by;
        } else {
          $sender_user = $space_user_with_share[0]['created_by'];
        }
        $space_user_info = SpaceUser::getSpaceUserInfo($space_id,$space_user_with_share[0]['created_by']);
        if (isset($space_user_info[0]['invite_alert']) && $space_user_info[0]['invite_alert'] && $space_user_info[0]['user_status'] == 0) {
          $mail_data['space_id'] = $space_id;
          $mail_data['sender_user'] = $sender_user;
          $mail_data['user'] = $space_user_with_share[0]['User'];
          $mail_data['share'] = $space_user_with_share[0]['Share'];
          $mail_data['view'] = 'email.member_accept';
          (new MailerController)->memberAccept($mail_data);
        }
      }
        (new Logger)->mixPannelInitial(Auth::user()->id, $space_id, Logger::MIXPANEL_TAG['complete_profile']);
        if(SpaceUser::UserSpaces(Auth::user()->id, 'count')>config('constants.COUNT_ONE')){
          (new Logger)->mixPannelInitial(Auth::user()->id, $space_id, Logger::MIXPANEL_TAG['join_new_share']);          
        }
        dispatch(new CopyProfileImagesToAWS()); 
        return back();
      }
      }     
  }

  public function update_member_space_profile(Request $request) {
    $post = $request->all();
    $space_id = $post['space_id'];
    $jobtitle = $post['jobtitle'];
    $company = '';
    $bio = $post['bio'];
    $linkedin = $post['linkedin'];
    $phone = $post['phone'];
    $token = $post['_token'];
    $validations = \Validator::make($request->all(),
      [
      'jobtitle'=>'required|max:25',

      ],[
      'required' => 'This field is required'
      ] 
      );     
    if($validations->fails()){         
      return redirect('clientshare/'.$space_id)->withInput()->withErrors($validations);
    } else {
      if( !empty($_FILES['file']['tmp_name']) ) {
        $file = Input::file('file');
        $file_tmp = getimagesize($file);         
        $aws = new Aws;
        $file_temp['temp_name'] = $_FILES['file']['tmp_name'];
        $file_temp['name'] = 'file';
        $user_data['user']['profile_image_url'] = $aws->image($file_temp, $request)['path'];
      }
      $data_contact['linkedin_url']=trim($linkedin);
      $data_contact['contact_number']=trim($phone);
      $user_data['user']['contact']=json_encode($data_contact);
      User::where('id',Auth::user()->id)->update($user_data['user']);
      $space_users = SpaceUser::where('space_id', $space_id)->where('user_id', Auth::user()->id)->get()->toArray();
      if(isset($space_users[0]['metadata'])){
        $old_values = $space_users[0]['metadata'];
        $new_values = array(
        'user_profile' => array(
        "space_id"=>$space_id,
        "user"=>array(
        'first_tname'=>'',
        'last_name'=>'',
        'contact'=>array(
        'linkedin_url' => $linkedin,
        'contact_number' => $phone,
        )
        ),
        'job_title' => $jobtitle,
        'bio' => $bio,
        'company'=>$company,
        '_token'=>$token
        ));
        $meta_data = array_merge($old_values,$new_values);
        $data['metadata'] = json_encode($meta_data);
        SpaceUser::updateUserDataInSpaceUser($space_id, Auth::user()->id, $data);
        dispatch(new CopyProfileImagesToAWS()); 
        return back();
      }
    }         
  }
  public function testing($url){
    $mime_t = array("application/pdf");
    header("content-type: application/pdf");
    readfile("https://s3-eu-west-1.amazonaws.com/clientshare-docs/test-pdf.pdf");
    die();
  }
   /**
  *
  *User Profile
  *
  */
  public function profile() {
    if(empty(Auth::check())) {
        return redirect('/');exit;
    }
    $a = Auth::User();
    $users =DB::table('users')->where('id',$a->id)->first(); 
    return view('share.profile', ['users' => $users]);   
  }
    /**/
  public function searchBlockWords(Request $request) {
      $input = $request->all();
      $words = $input['word'];
      return $data = BlockWords::blockWords($words);
  }
  public function setClientShareList() {
      $user_spaces = Space::where('user_id', Auth::user()->id)->get();
      Session::put('user_spaces', $user_spaces);
      return Session::get('user_spaces');
  }

  public function addprofile(Request $request) { 
      $share_token="";
      (new ManageShareController)->setClientShareList();
      $this->setClientShareList();
      $share_token = $request->_shareToken;
      if(isset($share_token) && $share_token!='') {
        $data = Space::spaceById($share_token, 'get');
        $header_class = 'admin_onboarding';
        $space_user = SpaceUser::with('user_role')->where('user_id', Auth::user()->id)->where('space_id', $share_token)->get();
        Session::put('user_space_by_email', $space_user);
      }
      return \Redirect::to('/clientshare/'.$share_token);
  }    

  public function updateprofile(Request $request) {
      $post = $request->all();
      if(!empty($post['password'])){
        $first_name= $request['firstname'];
        $last_name= $request['lastname'];
        $password= $request['password'];
        $id = $request['id'];
        $v = \Validator::make($request->all(),[ 
          'password'=>'confirmed|min:8|max:60','password_confirmation'=>' ',
          'firstname'=>'required|max:25','lastname'=>'required|max:25']);
        if($v->fails()){         
          return redirect('profile')->withInput()->withErrors($v);      
        } else {
          $updateQuery = User::where('id',  $id)->limit(1)->update(array('password' => bcrypt($password),'last_name' => $last_name,'first_name' => $first_name));
          return back()->with('message', 'Password Updated Sucessfully.');
        }
      } else{
        $first_name= $request['firstname'];
        $last_name= $request['lastname'];
        $id = $request['id'];
        $v = \Validator::make($request->all(),[ 
          'firstname'=>'required',]);
        if($v->fails()){         
          return redirect('profile')->withInput()->withErrors($v);
        }else
        {
          $updateQuery = User::where('id',  $id)->limit(1)->update(array('last_name' => $last_name,'first_name'=>$first_name));
          return redirect('profile');
        }
      }
  }

  public function settings() {    
      if(empty(Auth::check())){   
       return redirect('/');exit;   
     }        
     $a = Auth::User();   
     $users =DB::table('users')->where('id',$a->id)->first();   
     $blocked_words = DB::table('blocked_words')->get();    
     return view('share.settings', ['users' => $users], ['blocked_words' => $blocked_words]); 
  }   

  public function add_words(Request $request){    
    BlockWords::truncate();   
    $input = $request->all();   
    if(isset($input['block_words'])){   
      $block_words = array_filter($input['block_words']);     
    }   
    foreach ($block_words as $value) {    
      $block_words = new BlockWords;    
      $block_words->block_words = $value;   
      $block_words->save();   
    }   
    return 'Blocked Word Added Successfully';    
  }     

  public function deleteword($id){    
    if(isset($id)){   
      BlockWords::find($id)->delete();      
      return 'Deleted Successfully';    
    }  
  }

  public function checkSpaceDeleted(Request $request){
    $input = $request->all();
    if(!empty(Auth::check())){
      $user_id = Auth::user()->id;
      if(isset($input['space_id'])) {
        $url = '';
        $space = Space::checkSpaceDeleted($input['space_id']);
        $is_deleted = (new SpaceUser)->getInactiveSpaceUser($input['space_id'], Auth::user()->id, 'count');
        if($is_deleted){
            $url = '/logout';
            return $url;
        }

        if(!empty($space)){
            $url = '/login';
            $share = SpaceUser::getShareIfUserHaveAnyShare($user_id);
            if(!empty($share))
                $url = '/logout';
        }
        return $url;    
      }
    }
  }

  public function forget_linkedin_session($request){
    $request->session()->forget('linkedin_company'); 
    $request->session()->forget('linkedin_sub_company'); 
    $request->session()->forget('linkedin_job_title'); 
    $request->session()->forget('linkedin_link');
    $request->session()->forget('linkedin_phoneno');
    $request->session()->forget('linkedin_bio');
    
  }

  public function createInviteUser($id) {
    $invite_info = Invitation::find($id);
    if( !sizeOfCustom($invite_info) ){
      abort(404);
    }
    $data = [
      "share_id" => $invite_info->share_id,
      "user" =>[
        "first_name" => $invite_info->first_name,
        "last_name" => $invite_info->last_name,
        "email" => $invite_info->email,
        "subject" =>'',
        "invite"=>true
      ],
      'user_type' => array_search($invite_info->user_type_id, UserType::USER_TYPE)??UserType::USER_TYPE['user'],
      "call_via_bulk_invitation"=>true,
      "mail"=>[]
    ];
    $space_info = Space::getSpaceBuyerSeller($invite_info->share_id);
    $space_user = SpaceUser::getSpaceUserRole($invite_info->share_id,$invite_info->user_id);
    if(!$space_user || !$space_info){
      abort(404);
    }
    $space_info['space_user'] = $space_user;
    Session::put('space_info', $space_info);
    $sender_user= User::find($invite_info->user_id);  
    $session_space_info = Session::get('space_info')->toArray();
    $this->updateSpaceSessionData( $invite_info->share_id,$invite_info->user_id);
    $session_space_info['sender_user']['id'] = $sender_user->id;
    $session_space_info['sender_user']['email'] = $sender_user->email;
    $session_space_info['sender_user']['first_name'] = $sender_user->first_name;
    $session_space_info['sender_user']['last_name'] = $sender_user->last_name;
    $data = (new ManageShareController)->processInvitation($data,$session_space_info);
    $user = User::getUserIdFromEmail($invite_info->email); 
    $user_id = '';
    if(isset($user->id)){ $user_id = $user->id; }
    $data = [
      'user_id'=>$user_id,
      'share_id'=>$invite_info->share_id,
      'email'=>base64_encode($invite_info->email)
    ];
    return $data;
  }

  function getSpaceUsers($space_id){
    $space_users = User::getApprovedUsers($space_id);
    return response()->json(['result'=> !!($space_users), 'space_users'=>$space_users]);
  }
}
