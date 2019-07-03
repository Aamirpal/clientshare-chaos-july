<?php
namespace App\Helpers;

use Storage;
use App\UserType;
use Illuminate\Http\Request;

class Generic {	

	/* Validate */
	public function check_uuid_format($string) {
		$uuid_format = '/^\{?[A-Za-z0-9]{8}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{12}\}?$/';
		if (preg_match($uuid_format, $string)) {
			return true;
		}
		return false;
	}


	public static function publicConstant(){
		$constants = ['PROJECT', 'POST_EXTENSION', 'ANALYTIC','MANAGEMENT_INFORMATION',
		              'TASK_0_PROGRESS','TASK_1_PROGRESS','TASK_2_PROGRESS',
		              'TASK_3_PROGRESS','TASK_4_PROGRESS','TASK_5_PROGRESS',
		              'TASK_6_PROGRESS','TASK_7_PROGRESS','TASK_8_PROGRESS'];
		foreach ($constants as $constant_name) {
			$public_constants[$constant_name] = config('constants.'.$constant_name);
		}
		$public_constants['USER_TYPE'] = UserType::USER_TYPE;
		return $public_constants??[];
	}


	public function fileLoading(Request $request) {
		try {
		    $ch = curl_init();
		    $ch = curl_init(str_replace(' ', '%20', $request->url));
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		    curl_setopt($ch, CURLOPT_TIMEOUT ,8);
		    $content = curl_exec($ch);
		    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		    curl_close($ch);
		    if($httpcode != 200 ) abort(404);

		    header('Content-Type: application/octet-stream');
		    header("Content-Transfer-Encoding: Binary");
		    header("Content-disposition: attachment; filename='file'");
		    return $content;
	  	} catch(\Exception $e) {
	    	show($e->getMessage());
	  	}	
	}

}