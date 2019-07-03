<?php

namespace App\Http\Controllers\v2;

use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\PostView\PostViewInterface;

class PostViewController extends Controller
{
    protected $post_view;

    public function __construct(PostViewInterface $post_view)
    {
		$this->post_view = $post_view;
    }

    public function store(Request $request)
    {
    	$request_data = $request->all();
    	$request_data['user_id'] = $request_data['user_id']??\Auth::user()->id;
    	$validator = Validator::make($request_data,[
	      'user_id'=>'required|uuid',
	      'space_id'=>'required|uuid',
	      'post_id'=>'required|uuid'
	    ], [
	      'uuid'=> 'Invalid format'
	    ]);
	    
	    if($validator->fails()){
	      return apiResponse([], 400, ['errors'=>$validator->errors()]);
	    }

		$this->post_view->create($request->all());
		return apiResponse($this->post_view->postViewUserList($request->post_id));
    }
}
