<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use App\Post;
use App\PostMedia;
use Session;
use Redirect;
use Auth;
use App\Comment;
use App\Notification;
use DB;
use Storage;
use Hash;
use App\SpaceUser;
use Validator;
use App\User;
use App\Helpers\Aws;
use App\Http\Controllers\MailerController;


class SearchController extends Controller
{

  public function executeSearch(Request $request){
    $keywords = Input::get('keywords');  
    $counter = Input::get('counter'); 
    $space_id =  Input::get('spaceId'); 
    $user_id =  Input::get('userId'); 
    if(!is_numeric($counter)) abort(404);
    
    $count=$counter*5;
    $users = User::executeSearch($user_id,$space_id,$keywords,$count+1);
    $data = Post::searchPosts($space_id,$user_id,$keywords,$count+1);
    $result = array_merge($users,$data); 
    $total_count=sizeOfCustom($result);
    $result = array_slice($result, 0,$count);
    return View('pages/searchUsers')->with(array('result'=>$result,'totalcount'=>$total_count,'count'=>$count,'keywords' =>$keywords, 'spaceId'=>$space_id,'userId'=>$user_id ));
  }
}