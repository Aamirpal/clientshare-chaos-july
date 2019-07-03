<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use App\Post;
use App\PostMedia;
use App\PostActivity;
use Session;
use Redirect;
use Auth;
use Config;
use App\Comment;
use DB;
use Carbon\Carbon;
use Mail;
use Image;
use Storage;
use Hash;
use App\{SpaceUser,UserType};
use Validator;
use App\User;
use App\Media;
use App\Space;
use App\Company;
use App\Helpers\{Aws, Generic, Logger, Post as PostHelper};
use App\Http\Controllers\{MailerController,FeedbackController, UserController};
use App\PostViews;
use App\SpaceGroups;
use App\SpaceUserGroups;
use App\BlockWords;
use App\Notification;
use App\Traits\OneTimePassport;
use App\Traits\Generic as GenericTrait;
use Aws\ElasticTranscoder\ElasticTranscoderClient;

class PostController extends Controller {

  use OneTimePassport, GenericTrait;

  public function posts(Request $request, $post_id = null) {
    $request->comment_limit = $request->comment_limit ?? 2;
    $posts = Post::getPostDataWithUserOrAll($request->space_id, $request->offset, 3, Auth::user()->id, $request->category, $post_id, $request->comment_limit);
    $space_category = Space::find($request->space_id)->category_tags;

    $shares = explode('|', env('ADD_COMMENT_ATTACHMENT', Space::ADD_COMMENT_ATTACHMENT));
    $feature_restriction = ['post' => ['add_comment_attachment'=>in_array($request->space_id, $shares)]];

    $user = Auth::user();
    return compact('feature_restriction', 'posts', 'user', 'space_category');
  }

  public function post(Request $request, $post_id) {
    $request->category = null;
    $request->offset = 0;
    $request->limit = 1;
    $post_data = $this->posts($request, $post_id);
    if(!sizeOfCustom($post_data['posts']))
      abort(404);
    $post_data['space_users'] = (new SpaceUser)->userListing($post_data['posts'][0]['space_id']);
    return $post_data;
  }

  public function postViewerList(Request $request) {
    $data = $request->all();    
    $post_id = $data['post_id']??'';

    if(!$post_id) {
      return '';
    }
    
    $post_view = (new PostViews)->postViewerList($post_id);

    if (!empty($post_view)) {
      $check_count = 1;
      foreach ($post_view as $value) {
        if ($check_count <= 5) {
          echo (ucfirst($value['user']['first_name']) . " " . ucfirst($value['user']['last_name']). "</br>");
        } else {
          echo ("and " . (sizeOfCustom($post_view) - 5) . " others");
          break;
        }
        $check_count++;
      }
    }
  }

  public function postFileView($space_id) {
    if(!SpaceUser::getSpaceUserInfo($space_id, Auth::user()->id, 'count')) abort(404);
    (new UserController)->updateSpaceSessionData($space_id);
    return view('posts.file_view.index', ['space_id'=>$space_id]);
  }

  public function postFileViewData(Request $request){
    $request['limit'] = config('constants.POST.file_view_page');
    $files_data = PostMedia::PostFiles($request->all(), Auth::user()->id);
    return ['files_data'=>$files_data, 'offset' => $request->offset+$request['limit']];
  }

  public function postUsers(Request $request) {
    $request_data = $request->all();
    if(!isset($request_data['endorseid']) || !isset($request_data['spaceid'])) return false;
    
    $post = Post::postById($request_data['endorseid']);
    $space = SpaceUser::postUsers($request_data['spaceid'], $post['user_id']);

    usort($space, function($current_user, $next_user) {
        $string_case_comparison = strcasecmp($current_user['user']['first_name'], $next_user['user']['first_name']);
        return $string_case_comparison <=> 0;
    });

    return view('pages/endorse_setting_popup_ajax', ['post_data' => $post, 'space_data' => $space, 'current_user' => Auth::user()->id, 'space_id' => $request_data['spaceid']]);
  }
  public function pinPost($post_id = null, int $pin_status = null, $space_id = null){
    if($post_id == '' && $status == '') return back(); 
    
    if($pin_status && Post::pinPostCount($space_id) >= Post::RULE['pin_post']) return back();
    Post::updatePost([
      'id' => $post_id,
      'pinned_at' => date('Y-m-d h:i:s'),
      'pin_status' => $pin_status
    ]);
    (new Logger)->mixPannelInitial(Auth::user()->id, $space_id, Logger::MIXPANEL_TAG['pin_post']);
    return back();
  }

  public function postAttachment($otp_id) {
    $otp = $this->otpGetUrl($otp_id);
    if(!$otp) abort(404);
    $file_url = explode('?', $otp['app_url'])[0];
    $file_name = explode('/', $file_url);
    
    
    $ch = curl_init();
    $ch = curl_init($otp['app_url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 25);
    $content = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if($httpcode != 200 ) abort(404);

    $file_name = $otp['metadata']['originalName']??array_pop($file_name);

    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"".$file_name."\"");
    return $content; 
  }


  /* View url embeded post user name list */
  public function viewUrlEmbeded($post_id) {
    $users = DB::select(
      "SELECT first_name||' '||last_name as user_name, count(*)from activity_logs log
      inner join users usr on usr.id = log.user_id
      where (action = 'view embedded url' or action = 'click link')
      and space_id = '".Session::get('space_info')['id']."' 
      and content_id = '".$post_id."'
      group by first_name||' '||last_name"
    );
    foreach ($users as $key => $value) {
      echo $value->user_name." "."(".$value->count.")"."<br>";
    }
  }

  public function viewEyeUserPopup(Request $request) {
      $data = $request->all();
      $post_id = $data['post_id'];
      $space_id = $data['space_id'];
        if($post_id!='' &&  $space_id != ''){
          $postviews = new PostViews;
          $post_view = PostViews::with(['User'=>function($q1) use($space_id){
              $q1->with(['SpaceUser'=>function($q2) use($space_id){
                  $q2->where('space_id',$space_id);
                  $q2->select('user_id','space_id','metadata');
              }]);
          }])->where('post_id',$post_id)->selectRaw('user_id, count(*) as user_count')->groupBy('user_id')->get()->toArray(); 
          if(!empty($post_view)) {
             return view('pages.eye_hover_view_user_popup_ajax',['post_view'=>$post_view]);       
          }
        }
  }

  /* */
  public function triggerFeedback($space_id=null) {
    ini_set('max_execution_time', -1);
    if(Carbon::now()->day != 1 && !$space_id){
      return 0;
    } 

    $space_users = SpaceUser::feedbackOpenIntimation($space_id);

    foreach ($space_users as $space_user) {
      $space = Space::find($space_user->user_id);
      if(isset($space->version)){
        if($space->version){
          continue;
        } 
      }
      $check_user =  (new FeedbackController)->checkBuyer($space_user['space_id'],$space_user['user_id']);
      if($check_user != SpaceUser::ROLES['buyer'] && UserType::userTypeNameById($space_user->user_type_id) != UserType::ROLES['admin']) continue;
      if($check_user == SpaceUser::ROLES['buyer']){
        $data = Notification::create([
          'notification_type' => 'feedback',
          'space_id' => $space_user->space_id,
          'notification_status' => false,
          'user_id' => $space_user->user_id,
          'post_id' => '00000000-0000-0000-0000-000000000000',
        ]);
      }
      //Sending mail
      $user = User::find($space_user->user_id);
      if( !$user ) continue;
      if((!empty($space_user->share->seller_processed_logo)) && (!empty($space_user->share->company_seller_logo))) {
        $company_seller_logo = $space_user->share->seller_processed_logo; 
      } elseif ((empty($space_user->share->seller_processed_logo)) && (!empty($space_user->share->company_seller_logo))) {
        $company_seller_logo = $space_user->share->company_seller_logo;
      } else {
        $company_seller_logo = env('APP_URL').'/images/login_user_icon.png';
      }
      if((!empty($space_user->share->buyer_processed_logo)) && (!empty($space_user->share->company_buyer_logo))) {
        $company_buyer_logo = $space_user->share->buyer_processed_logo;
      } elseif ((empty($space_user->share->buyer_processed_logo)) && (!empty($space_user->share->company_buyer_logo))) {
        $company_buyer_logo = $space_user->share->company_buyer_logo;
      } else {
        $company_buyer_logo = env('APP_URL').'/images/login_user_icon.png';
      }
      $data['to'] = $user->email;
      $data['subject'] = "This month's feedback is open";
      $data['user'] = $user;
      $data['space_user'] = $space_user;
      $data['link'] = url('/');
      $data['company_seller_logo'] = $company_seller_logo;
      $data['company_buyer_logo'] = $company_buyer_logo;
      $data['quater'] = Carbon::now()->subMonth(3)->format('F Y')." - ".Carbon::now()->subMonth(1)->format('F Y');
      $data['days_left'] = (Config::get('constants.feedback.feedback_opened_till') - Carbon::now()->day)+1;
      if($check_user == 'buyer'){
        $data['redirect_link'] = "";
        $data['mail_template'] = 'email.feedback_open_user';
        (new MailerController)->feedbackNotification(0 , 0, $data['mail_template'], $data);        
      }
      if($space_user->user_type_id == 2 && !$space_id){
        $data['redirect_link'] = "";
        $data['mail_template'] = 'email.feedback_open';
        (new MailerController)->feedbackNotification(0 , 0, $data['mail_template'], $data);  
      }
    }
  }


  /* Log click of embed link in post */
  public function postEventLog( Request $request ) {
    if($request->content_id && (new Generic)->check_uuid_format($request->content_id)){
      $postviews = new PostViews;
      $post_view = PostViews::where('user_id',Auth::user()->id)->where('post_id',$request->content_id)->get()->toArray();
      if(empty($post_view)) {
        $postviews = new PostViews;
        $postviews->user_id = Auth::user()->id;
        $postviews->space_id = Session::get('space_info')['id'];
        $postviews->post_id = $request->content_id;
        $postviews->save();
      } 
    }
    /* Log event */
      return (new Logger)->log([
        'user_id'     => Auth::user()->id,
        'content_type'=> 'App\PostMedia',
        'action'      => 'click link',
        'description' => 'Click on '.$request->url??'embeded'.' link',
        'content_id'  => $request->content_id??'',
      ]);
  }

  public function logPostFileEvent( Request $request ) {
      if($request->content_id && (new Generic)->check_uuid_format($request->content_id)){
        $postviews = new PostViews;
        $post_view = PostViews::where('user_id',Auth::user()->id)->where('post_id',$request->content_id)->get()->toArray();
          $postviews = new PostViews;
          $postviews->user_id = Auth::user()->id;
          $postviews->space_id = Session::get('space_info')['id'];
          $postviews->post_id = $request->content_id;
          $postviews->save();
          $view_status = 'n';
      }
      /* Log event */
      $log_data =  (new Logger)->log([
        'user_id'     => Auth::user()->id,
        'content_type'=> 'App\PostMedia',
        'action'      => 'click link',
        'description' => 'Click on '.$request->url??'embeded'.' link',
        'content_id'  => $request->content_id??''
      ]);
      return array('check_user_view_exist' =>$view_status,'log_data'=>$log_data);
  }

  /**
  * Download the file.
  *
  * @return Response
  */
  public function downloadFile($id, $modal=null){
    $modal = $modal??config('constants.MODEL.post_file');
    if( $modal == strtolower(config('constants.MODEL.executive_file'))){
      $file_data = Media::findorfail($id);
      $file_data['file_url'] = $file_data['media_path'];
      if(isset($file_data['metadata']['originalName'])){
        $file_data['originalName'] = $file_data['metadata']['originalName'];  
      } else{
        $name_temp = explode("/", $file_data['media_path']);
        $file_data['originalName'] = array_pop($name_temp);
      }
      (new PostHelper)->shareAccessable($file_data->space_id, Auth::user()->id)?:abort(404);
    } elseif($modal == strtolower(config('constants.MODEL.post_file'))) {
      $file_data = PostMedia::findorfail($id);
      (new PostHelper)->shareAccessable(Post::postById($file_data->post_id)->space_id, Auth::user()->id)?:abort(404);
      $file_data['file_url'] = $file_data['post_file_url'];
      $file_data['originalName'] = $file_data['metadata']['originalName'];
      /* Log event */
      (new Logger)->log([
        'user_id'     => Auth::user()->id,
        'content_id'  => $id,
        'content_type'=> 'App\PostMedia',
        'action'      => 'download',
        'description' => 'Download Attachment'
      ]);
    } else {
      abort(404);
    }
    if(!$file_data) abort(404);
    $file_url = $this->getAwsValidUrl($file_data['file_url'], '5');
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header('Pragma: public');
    header('Content-Length: ' . $file_data['metadata']['size']);
    header("Content-disposition: attachment; filename=\"" . $file_data['originalName'] . "\""); 
    readfile($file_url);
    exit;
  }

  /**
  * Get the url for aws
  *
  * @return Response
  */
  public function getAwsValidUrl($url='', $minutes=5) {
    return getAwsSignedURL($url, $minutes);
  }

  /**/
  public function urlValidate(Request $request, $minutes=10) {
    if (stripos($request->q, '../') !== false || stripos($request->q, '..\\') !== false) {
        abort(404);
    }
    $url_arr = explode("/", $request->q);
    $url_arr2 = array_reverse($url_arr);
    foreach ($url_arr as $key => $ind) {
      if( $ind != env("S3_BUCKET_NAME")) {
        array_pop($url_arr2);
      } else {
        array_pop($url_arr2);
        break;
      }
    }
    $url_arr2 = array_reverse($url_arr2);
    if (empty($url_arr2) || empty($url_arr2[1])) {
        abort(404);
    }
    $response = $this->getCloudUrl($url_arr2, $request->file_name, 'inline', $minutes);
    $response_attachment = $this->getCloudUrl($url_arr2, $request->file_name, 'attachment', $minutes);
    $file_data = (new PostHelper)->getAttachmentInfo($url_arr2[1]);
    $otp = $this->generate_otp(['metadata'=> $file_data['metadata']??'','app_url'=>(string)(is_object($response)?$response->getUri():''), 'method'=>'get'] );

    if(!sizeOfCustom($otp)) abort(404);
    
    return [
      'file_ext'=>explode('.', $url_arr2[1])[1],
      'file_url'=>(string)(is_object($response)?$response->getUri():''),
      'cloud'=>(string)(is_object($response_attachment)?$response_attachment->getUri():''),
      'document_viewer' => env('APP_URL').'/post_attachment/'.$otp->id
    ];
  }

  public function getCloudUrl($url_arr, $file_name, $content_disposition = 'inline', $minutes = 10) {
    $s3 = Storage::disk('s3');
    if(!$s3->exists( implode('/', $url_arr) )) return 0;
    $client = $s3->getDriver()->getAdapter()->getClient();
    $expiry = "+".$minutes." minutes";
    $command = $client->getCommand('GetObject', [
      'Bucket' => env("S3_BUCKET_NAME"),
      'Key'    => implode('/', $url_arr),
      'ResponseContentDisposition' => "$content_disposition; filename=\"" . $file_name . '"'
    ]);
    return $client->createPresignedRequest($command, $expiry);
  }

  /**/
  public function getUrlData($url_for_preview="") {
    if(Request()->q){
      $url_for_preview=Request()->q;
    }
    $url_string = $url_for_preview;
    $regex = config('constants.email.regex');
    preg_match($regex, $url_for_preview, $match, PREG_OFFSET_CAPTURE, 0);
    
    if(!isset($match[0]) || !sizeOfCustom($match[0]))
      return 0;
    if( sizeOfCustom($match[0]) ){ 
      $url_temp[$match[0][0]] = strpos($url_string, $match[0][0]);      
      $url_list = implode(", ", $match[0]);
    }
    $full_url = $url_for_preview = array_keys($url_temp, min($url_temp))[0];

    $content = $this->custom_curl([
      'url' => Config::get('constants.EMBEDLY_API_URL').'?url='.trim($url_for_preview).'&key='.env('URL_PRE'),
      'timeout_seconds' => 4,
      'request_type' => 'GET'
    ]);
    if( !isset($content) || !$content ) return 0;
    $data  = json_decode($content, true);
    if( isset($data['error_code']) ) return 0;
    if( ( !isset($data['title']) || !isset($data['description']))) return 0;

    $data['thumbnail_url'] = $data['images'][0]['url']??env('APP_URL').'/images/video-poster.jpg';
    $res['domain']        = $this->getDomain($url_for_preview);    
    $res['favicon']       = $data['thumbnail_url'];
    $res['title']         = $data['title'];
    $res['description']   = $data['description']??'';
    $res['thumbnail_img'] = isset($data['thumbnail_url'])? 1:0;
    $res['full_url']      = $data['url']??'';
    $res['url']           = $data['url']??'';
    $res['url_list']      = $url_list;
    $res['api_response']  = $data;
    $res['metatags'] = '';
    if( (is_numeric(strpos($url_for_preview, 'youtube')) && is_numeric(strpos($url_for_preview, 'watch'))) ) {
      $url_data = parse_url($url_for_preview);
      parse_str($url_data['query'],$query);

      if(isset($query['v'])){
        $res['metatags'] = array('twitter:player' => config('constants.URL.youtube_embed').$query['v'] );
      }
    }
    return $res;
  }  

  public function getDomain($url) {
    $pieces = parse_url($url);
    $domain = isset($pieces['host']) ? $pieces['host'] : '';
    if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
      return $regs['domain'];
    }
    return false;
  }

  public function addPost(Request $request) {
    (new logger)->addPostLogger($request->all());
    /* Validation for post and media files */
    $space_post = \Validator::make($request->all(),
          ['post.subject'=>'required','space.post'=>'required']
        );
    $file = \Validator::make($request->all(),
        ['file'=>'required']
      );
    if($space_post->fails() && $file->fails()){
      return back()->withErrors(['error_post'=>'Subject and Body can not be empty.'])->withInput();
    }
    $post_data = $request->all();

    $is_deleted = (new SpaceUser)->getInactiveSpaceUser($post_data['space']['id'], Auth::user()->id, 'count');
    if($is_deleted)
        return ['message'=>'user_deleted'];

    $multile_spaces=array();
    $visibleuser_without_share=[];
    $share_alert='';
    $visible_user='';

    if(isset($post_data['post_share'])){
      /* when post shared for multiple shares */
      if(isset($post_data['share_alert'])){
        $share_alert = $post_data['share_alert'];
      }
      if(isset($post_data['share'])){
        $multile_spaces = $post_data['share'];
      }
    } else {
      /* Post shared on only 1 share */
      $visibleuser = $this->countVisibleUsers($post_data);
      /* Set visibility for this particular share */
      $visibleuser_without_share = $visibleuser;
      if(isset($post_data['alert'])){
        $share_alert = $post_data['alert'];
      }   
    }
    $cate = $post_data['category'];
    if(isset($post_data['space']['id'])){
      array_push($multile_spaces, $post_data['space']['id']);
    }
    if(sizeOfCustom($visibleuser_without_share)){
      $visible_user = implode(",", $visibleuser_without_share);
    } else {
      $visible_user = 'All';
    }
    $this->resubmitPost($post_data);
    $temp_data['category'] = $cate??'';
    /* Url toggle code */
    if(isset($post_data['url_embed_toggle']) && $post_data['url_embed_toggle'] 
      && (is_numeric(strpos(strtolower($post_data['space']['post']), 'http')) || is_numeric(strpos(strtolower($post_data['space']['post']), 'www'))) ) {
      $url_preview_data_json = json_decode( $request->url_preview_data_json, true);
      if( sizeOfCustom($url_preview_data_json) )
        $temp_data['get_url_data'] = $url_preview_data_json;
    }
    $this->savePostShare($multile_spaces,$post_data,$temp_data,$visible_user,$share_alert);
    return ['message'=>'post saved'];
  }

  public function savePostShare($shares,$post_data,$meta_data,$visible_user,$share_alert){
    $alerts='';
    $media_check = 0;
    $shares = $this->sortShare($shares);          
    $old = [];
    foreach($shares as $share){
      $post_input = new Post;
      $post_input->user_id = $post_data['user']['id'];
      $post_input->space_id = $share;
      $post_input->post_description = trim($post_data['space']['post']);
      $post_input->post_subject = trim($post_data['post']['subject']);
      $post_input->comment_count = 0;
      $post_input->metadata = json_encode($meta_data);
      $post_input->visibility = $visible_user;

      /*@Reviewer please ignore this code as this temporary and will be removed after app's new version is released(very soon :) */
      $post_input->space_category_id = \App\Models\SpaceCategory::where('space_id', $share)->where('name', 'ilike', 'general updates')->first()['id']??null;
      $post_input->group_id = \App\Models\Group::where('space_id', $share)->where('name', 'ilike', 'everyone')->first()['id']??null;
      $post_input->url_preview = isset($meta_data['get_url_data'])?json_encode($meta_data['get_url_data']):null;

      unset($notify_array);
      /* Handle multipe post on DB end */
      try{
        $post_id = (new Post)->createPost($post_input);
      } catch( \Illuminate\Database\QueryException $e ) {
        continue;
      }
      if(isset($post_data['uploaded_file_aws'])){
        $this->savePostMedia($post_data['uploaded_file_aws'], $post_id);
      }
      
      if(isset($share_alert)){
        $rank_share = Space::find($share);
        if(isset($share_alert[0]) && $share_alert[0]=='multiselect-all'){         
        $active_users = SpaceUser::getSpaceActiveUsersInfo($share);
          array_walk($active_users, function(&$value, &$key) use (&$notify_array){
            $notify_array[$key] = $value['user_id'];
          });

        }else{
          $notify_array = $share_alert;
          if (!empty($notify_array) && in_array("All", $share_alert)){
           $notify_array = array_slice($share_alert,1);
          }   
        }
        if($notify_array){
          if($rank_share->rank){
            $bunch = array_diff($notify_array,$old);
            $old = array_merge($notify_array,$old);
          }else{
            $bunch = $notify_array;
          }
          $job_data = [
            'post'=>[
              'id'=>$post_id,
              'subject'=>$post_data['post']['subject'],
              'description'=>$post_data['space']['post']
            ],
            'logged_in_user_id'=>Auth::User()->id,
            'receiver_ids'=>$bunch,
            'share'=> $share,
            'current_space'=>Session::get('space_info')['id']
          ];
          dispatch(new \App\Jobs\SendEmailsOnPostSharing($job_data));
        }
      }
      //ADD ALERT NOTFICATION TO USER END
      $media_check++;
    }
    return true;
  }

  public function resubmitPost($data){
    $repeated_post = (new Post)->getPostByDescription($data, Auth::user()->id);
    if(sizeOfCustom($repeated_post) && $repeated_post['created_at'] != '') {
      $time_diff = (new Post)->getDateDiffrence($repeated_post['created_at']);
      
      if($time_diff[0]->date_part<60) {
        return redirect('/');
      }
    } 
    return true;
  } 

  public function countVisibleUsers($data){
    $visible_total_user = 1;
    $visibleuser =[];
    if(isset($data['visibility'])){
      $visible_total_user = sizeOfCustom($data['visibility'])+1;
      $visibleuser = $data['visibility'];
    }
    if(isset($data['visible_to_count'])){
      if( $visible_total_user == $data['visible_to_count']){ 
        array_push($visibleuser,'All');
      }
    }
    if (!in_array(Auth::user()->id, $visibleuser)){
      array_push($visibleuser,Auth::user()->id);
    }
    return $visibleuser;
  }

  public function awsCreateJobForVideoConvert(Request $request){
    $data = $request->all();
    if (!empty($data['image_name']) && !isBadPath($data['image_name'])) {
          $split_name_url = explode('.',$data['image_name']);
          $split_name = explode('/',$split_name_url[0]);
          if(sizeOfCustom($split_name) < 2){
            return ['result'=>false];
          }
          $elastic_transcoder = ElasticTranscoderClient::factory(array(
                  'credentials' => array(
                      'key' => env('AWS_ACCESS_KEY_ID'),
                      'secret' => env('AWS_SECRET_ACCESS_KEY'),
                  ),
                  'version' => 'latest',
                  'region' => env('AWS_REGION'),
              ));
          $job = $elastic_transcoder->createJob(array(
                  'PipelineId' => env('TRANSCODER_PIPE_LINE_ID'),
                  'OutputKeyPrefix' => 'post_file/',
                  'Input' => array(
                      'Key' => $data['image_name'],
                      'FrameRate' => 'auto',
                      'Resolution' => 'auto',
                      'AspectRatio' => 'auto',
                      'Interlaced' => 'auto',
                      'Container' => 'auto',
                  ),
                  'Outputs' => array(
                      array(
                          'Key' => $split_name[1].'.mp4',
                          'Rotate' => 'auto',
                          'PresetId' => env('TRANSCODER_PRESET_ID'),
                      ),
                  ),
          ));
          return ['result'=>true];
    }else{
         return ['result'=>false];
    }
  }

  public function savePostMedia($uploaded_file, $post_id){
    $uploaded_file_aws = json_decode($uploaded_file, true);
    $uploaded_file_aws = gettype($uploaded_file_aws) == 'array'?$uploaded_file_aws:[];
    if(!empty($uploaded_file_aws)) {
      foreach($uploaded_file_aws as $key=>$files_url1) {
        /* image_orientation */
        $files_data = $this->imageOrientation($files_url1);
        $files_url1['url'] = $files_data?$files_data:$files_url1['url'];
        /* image_orientation */
        $postmedia = new PostMedia;
        $postmedia->post_id = $post_id;
        $postmedia->s3_file_path = filePathUrlToJson($files_url1['url'], false);
        $postmedia->metadata = $files_url1;
        $postmedia->save();
      }

      PostActivity::create([
        'post_id' => $post_id,
        'user_id' => Auth::user()->id,
        'metadata' => ['action' => 'post_edit']
      ]);
    }
    return true;
  }
  /**/
  public function imageOrientation($file_data, $extension = null, $visibility = 'private') {
    if(!$extension) if(explode('/', $file_data['mimeType'])[0] != "image") return 0;
    if($extension) $file_data['url'] = composeUrl($file_data);
    $file_data['url'] = $this->getAwsValidUrl($file_data['url']);
    $image = @imagecreatefromstring(file_get_contents($file_data['url']));
      if(!$image) return 0;
    $exif = @exif_read_data($file_data['url']);
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
      $imagemed = Image::make($image);
      if(!$extension) $imagemed->encode(explode('/', $file_data['mimeType'])[1]);
      else $imagemed->encode($extension);
      $final_file = (string)$imagemed;
      if(!$extension) $name = rand()."_".time().".".explode('/', $file_data['mimeType'])[1];
      else $name = rand()."_".time().".".$extension;
      $s3 = \Storage::disk('s3');
      $s3_bucket = env("S3_BUCKET_NAME");
      $file_path = '/post_file/'.$name;
      if($extension) 
         $file_path = '/company_logo/'.$name;
      $full_url = config('constants.s3.url')."".$s3_bucket."".$file_path;
      $s3->put($file_path, $final_file, $visibility);    
      return $full_url;
    }
    return 0;
  }
  /**/

  /**
  * Delete the post.
  *
  * @return Response
  */
  public function deletePost($id){
  
    $post = Post::find($id);
    if (!is_null($post)) {
      if($id!=''){
        $post = Post::find($id);
        $post->delete();
        $postmedia = new PostMedia;
        $postmedia = PostMedia::where('post_id',$id); 
        $postmedia->delete();
        $notification = new Notification;
        $notification = Notification::where('post_id',$id); 
        $notification->delete();
        $comment = new Comment;
        $comment = Comment::deleteCommentByPost($id); 
        Session::flash('message', "Deleted Sucessfully.");
        (new Logger)->mixPannelInitial(Auth::user()->id, $post->space_id, Logger::MIXPANEL_TAG['delete_post']);
        return redirect('/clientshare/'.$post->space_id);
      }
    }  
     else{ 
      return redirect('/');
    }
  }

  
  /**
  * Update the post after edit.
  *
  * @return Response
  */
  

  public function updatePost(Request $request) {
    if($request->ajax())
        return ['code' => 403];
    $data = $request->all();
   
    if(!empty($data['space']['id']) && $data['post']['id']!=''){
        $is_deleted = (new SpaceUser)->getInactiveSpaceUser($data['space']['id'], Auth::user()->id, 'count');
        if($is_deleted)
            return ['message'=>'user_deleted'];
         
      if(isset($data['editvisibility'])){
        $visible_user = $data['editvisibility'];
      }else{
        $visible_user =array();
      }
      if (!in_array(Auth::user()->id, $visible_user)){
        array_push($visible_user,Auth::user()->id);
      }
      
      /************************************************************************/
      /*DELETE ALL NOTIFICATION EXCEPT $delid OF THIS POST*/
      
      Notification::deleteNotifications($visible_user,$data['post']['id']);
      /*add all if everyone select*/
      if(SpaceUser::getSpaceUserActiveCount($data['space']['id']) == sizeOfCustom($visible_user)){
        array_push($visible_user,'All');
      }
      $combined_visible_user = implode(",", $visible_user);
      /*--------*/
      $this->savePostMedia($request->uploaded_file_aws, $data['post']['id']);

      $meta_data['category'] = $data['editcategory'];
      if(isset($data['url_embed_toggle']) && (is_numeric(stripos($data['editspace']['post'], 'http')) || is_numeric(stripos($data['editspace']['post'], 'www'))) ) {
        if( $this->getUrlData($data['editspace']['post']) )
          $meta_data['get_url_data'] = $this->getUrlData($data['editspace']['post']);
          if(isset($meta_data['get_url_data']) && !sizeOfCustom($meta_data['get_url_data'])) unset($meta_data['get_url_data']);
      }
      $edit_data =  [
        'post_description' => trim($data['editspace']['post']),
        'metadata' => json_encode( $meta_data ),
        'visibility' => $combined_visible_user,
        'post_subject' => trim($data['editpost']['subject']),
      ];   
      if(isset($data['repost'])){
        if(isset($data['editalert'])){
          $this->postAlerts($data);
        }
        $check_pinned = Post::where('id', $data['post']['id'])->where('pin_status',true)->get()->toArray();
        if(sizeOfCustom($check_pinned)){
          $edit_data['reposted_at'] = date('Y-m-d H:i:s');
          $edit_data['pinned_at'] = date('Y-m-d h:i:s');
        }else{
          $edit_data['reposted_at'] = date('Y-m-d H:i:s'); 
        }
      }
      Post::where('id',$data['post']['id'])->update($edit_data);
      if($data['edit_deleted_files']!=''){
        $deleted_files = explode(",", $data['edit_deleted_files']);
        PostMedia::WhereIn('id',$deleted_files)->delete();
      }
      
      (new Logger)->log([
        'action' => 'edit post',
        'description' => 'edit post'
      ]);
      (new Logger)->mixPannelInitial(Auth::user()->id, $data['space']['id'], Logger::MIXPANEL_TAG['edit_post']);
      if($request->ajax())
        return ['message' => 'done', 'code' => 200];
      return back();
    }
  }

  private function postAlerts($data=''){
    $alerts = $data['editalert'];
    if(isset($alerts)){
      $notify_array = $alerts;
      if (in_array("All", $alerts)){
        $notify_array = array_slice($alerts,1);
      }  
      if($notify_array){ 
        $job_data = [
          'post'=>[
            'id'=>$data['post']['id'],
            'subject'=>$data['editpost']['subject'],
            'description'=>$data['editspace']['post']
          ],
          'logged_in_user_id'=>Auth::User()->id,
          'receiver_ids'=>$notify_array,
          'share'=> $data['space']['id'],
          'current_space'=>Session::get('space_info')['id']
        ];
        dispatch(new \App\Jobs\SendEmailsOnPostSharing($job_data));
      }
    }
    return;
  }

   /**
  *
  * ADD GROUP
  * @author JATINDER SINGH
  */
  public function AddGroup(Request $request) {
    $data     = $request->all();
    $group    = $data['group'];
    $spaceId = Session::get('space_info')['id'];

    if(!isset($data['group_visibility'])){ return 1; }
    if (!SpaceGroups::where('space_id',$spaceId)->where('group',$group)->where('created_by',Auth::user()->id)->exists()) {
          $spaceGroup = new SpaceGroups;
          $spaceGroup->space_id = $spaceId;
          $spaceGroup->group = $group;
          $spaceGroup->created_by = Auth::user()->id;   
          $spaceGroup->save(); 
          $groupId = $spaceGroup->id;  
    }else{
      return 0;
    }
    if(isset($data['group_visibility'])){
      foreach($data['group_visibility'] as $visibility){
        $space_user = SpaceUser::select('id')->where('user_id',$visibility)->where('space_id',$spaceId)->take(1)->get()->toArray();
        if(isset($space_user[0])){
          $spaceUserGroup = new SpaceUserGroups;
          $spaceUserGroup->space_user_id = $space_user[0]['id'];
          $spaceUserGroup->group_id = $groupId;  
          $spaceUserGroup->save();  
        } 
      }
    }
    $spaceGroupsAll = SpaceGroups::where('space_id',$spaceId)->where('created_by',Auth::user()->id)->orderBy('created_at','desc')->get()->toArray();
    if(!empty($spaceGroupsAll)){
      return $spaceGroupsAll;
    }
  }
  /**
  *
  * GET GROUP MEMBERS
  * @author JATINDER SINGH
  */
  public function GetGroupMembers(Request $request){
    $data     = $request->all();
    $spaceId = Session::get('space_info')['id'];
    $groupid = $data['gid'];
    $spaceMembers = SpaceUserGroups::select('space_user_id')->where('group_id',$groupid)->with(['SpaceUser'=>function($qry) { $qry->where('user_status','0')->where('metadata->invitation_code','1'); }])->get()->toArray();
    if(!empty($spaceMembers)){
      return $spaceMembers;
    }
  }
  /**
  *
  * UPDATE GROUP MEMBERS
  * @author JATINDER SINGH
  */
  public function UpdateGroup(Request $request){
    $data = $request->all();
    $groupId    = $data['groupid'];
    $spaceId = Session::get('space_info')['id'];
    if(!isset($data['group_visibility1'])){ return 1; }
    if(isset($data['group_visibility1'])){
      $guser = SpaceUserGroups::where('group_id',$groupId);
      $guser->delete();
      foreach($data['group_visibility1'] as $visibility){
        $space_user = SpaceUser::select('id')->where('user_id',$visibility)->where('space_id',$spaceId)->take(1)->get()->toArray();
          $spaceUserGroup = new SpaceUserGroups;
          $spaceUserGroup->space_user_id = $space_user[0]['id'];
          $spaceUserGroup->group_id = $groupId;  
          $spaceUserGroup->save();  
      }
    }
    return '0';
  }
  /**
  *
  *DELETE GROUP
  *
  */
  public function DeleteGroup(Request $request){
    $id = $request->all()['groupid'];
    $group = SpaceGroups::where('id',$id);
    $group->delete();
    $guser = SpaceUserGroups::where('group_id',$id);
    $guser->delete();
  }
  public function GetGroupById(Request $request){
     $uid = $request->all()['uid'];
     $spaceId = Session::get('space_info')['id'];
     $loginid = Auth::user()->id;
     $space_user = SpaceUser::select('id')->where('user_id',$uid)->where('space_id',$spaceId)->take(1)->get()->toArray();
     if(!sizeOfCustom($space_user)) return 0;
     $space_userid = $space_user[0]['id'];
      if($space_userid){
        $spaceGroupsAll = DB::select("select sug.group_id from space_groups as sg
                                join space_user_groups as sug
                                on sg.id=sug.group_id
                                where sg.space_id ='$spaceId'
                                and sg.created_by = '$loginid'
                                and sug.space_user_id = '$space_userid'");
      }
      return $spaceGroupsAll;
  }
  public function GetGroupMeversAll(Request $request){
    $data     = $request->all();
    $spaceId = Session::get('space_info')['id'];
    $groupid = $data['gid'];
    $groupid = trim($groupid,",");
    $groupids = explode(",", $groupid);
    $spaceMembers = SpaceUserGroups::select('space_user_id')->whereIn('group_id',$groupids)->with(['SpaceUser'=>function($qry) { $qry->where('user_status','0')->where('metadata->invitation_code','1'); }])->get()->toArray();
      if(!empty($spaceMembers)){
        return $spaceMembers;
      }
  }

  /* View url embeded post user name list */
  public function viewUrlEmbededUsers(Request $request) {
      $data = $request->all();
      $post_id = $data['post_id'];
      $space_id = Session::get('space_info')['id'];
      $users = DB::select(
        "SELECT distinct user_id from activity_logs  
        where (action = 'view embedded url' or action = 'click link')
        and space_id = '".Session::get('space_info')['id']."' and content_id = '".$post_id."'
      ");
      $response=array();
      foreach ($users as $user ) {
        $d = User::where('id',$user->user_id)->with(['SpaceUser'=>function($q2) use($space_id){
              $q2->where('space_id',$space_id);
              $q2->select('user_id','space_id','metadata');
        }])->get()->toArray()[0];
        array_push($response, array('user'=>$d));
      }
      if(!empty($response)) {
        return view('pages.eye_hover_view_user_popup_ajax',['post_view'=>$response]); 
      }
  }

  public function matchWordSubject(Request $request)
        {
          $data = $request->all();
          $returnMatch = array();
          $badWords = BlockWords::pluck('block_words')->toArray();
           if(isset($data['subject'])){
            $subject = [];
            $this->ValidateStopWords($data['subject'],$badWords,$subject);
            if(sizeOfCustom($subject)){
            $returnMatch['subject'] =  $subject;
            }
          }
          if(isset($data['body'])){
            $body = [];
            $this->ValidateStopWords($data['body'],$badWords,$body);
            if(sizeOfCustom($body)){
            $returnMatch['body1'] =  $body;
          } 
        }
          return $returnMatch;
        }

  private function ValidateStopWords($string,$badWords, &$returnMatch){
    $matches = array();     
    if (preg_match_all("/\b_?(" . implode($badWords,"|") . ")_?\b/i", $string,$matches)) {
      $words = array_unique($matches[1]);
      if(sizeOfCustom($words)){
        foreach($words as $word) {
          array_push($returnMatch, $word);
        }
      }
    }
    return true;
  }

  public function sortShare($data)
  {
    $shares = [];
    foreach ($data as $share_id) {
      $share = Space::find($share_id);
      $shares[] = [
        'share_id'=>$share->id,
        'rank'=>$share->rank
        ];
    }    
    usort($shares, function($first_parameter, $second_parameter) {
        if ($first_parameter['rank'] < $second_parameter['rank']) return -1;
        if ($first_parameter['rank'] == $second_parameter['rank']) return 0;
        return 1;
    });   
    $sort_share = [];
    foreach ($shares as $value) {
      array_push($sort_share, $value['share_id']);
    }
    return $sort_share;
  }
}
