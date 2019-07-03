<?php

namespace App\Http\Controllers\v2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use  App\Repositories\SpaceUser\SpaceUserInterface;
use App\Repositories\Space\SpaceInterface;
use App\Repositories\PostMedia\PostMediaInterface;
use App\PostMedia;

class PostMediaController extends Controller
{
    protected $space_user;
    protected $space;
    protected $post_media;

    public function __construct(SpaceUserInterface $space_user, SpaceInterface $space, PostMediaInterface $post_media) {
        $this->space_user = $space_user;
        $this->space = $space;
        $this->post_media = $post_media;
    }

    public function index($space_id){
        if(!$this->space_user->spaceUserExists($space_id, \Auth::user()->id)) abort(404);
        (new UserController)->updateSpaceSessionData($space_id);
        $data['is_logged_in_user_admin'] = $this->space_user->isAdmin($space_id, \Auth::user()->id);
        $data['space_twitter'] = $this->space->getTwitterHandler($space_id);
        $data['space_id'] = $space_id;
        return view('v2-views.file_view.index', $data);
    }

    public function getFileData(Request $request){
        $request['limit'] = config('constants.POST.file_view_page');
        $files_data = $this->post_media->PostFiles($request->all(), \Auth::user()->id);
        return ['files_data'=>$files_data, 'offset' => $request->offset+$request['limit']];
    }
}
