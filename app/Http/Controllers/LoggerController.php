<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Session;
use App\Post;
use App\ActivityLog;
use App\Helpers\Logger;
use Illuminate\Http\Request;

class LoggerController extends Controller {
    

    public function index() {

    	if(!isset(Session::get('space_info')['id'])) 
    		abort(404);

    	$logs = (new ActivityLog)->customLogs();

		return view('logs/index', [
			'data'=>$logs,
			'space_id'=> Session::get('space_info')['id']
		]);
    }


    /* Custom log call */
    public function custom_log( Request $request){
    	/* Log event */
    	try{
    		return (new Logger)->log([
		        'user_id'     => Auth::user()->id,
		        'description' => $request->description??'',
		        'action' 	  => $request->action??'',
		        'space_id' 	  => $request->space_id??(Session::get('space_info')['id']??''),
		        'content_id'  => $request->content_id??'',
		        'content_type'=> $request->content_type??'',
		        'metadata'    => $request->metadata??''
		    ]);
    	} catch (\Exception $e) {

    	}
    }
}

