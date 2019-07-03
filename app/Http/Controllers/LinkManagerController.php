<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Traits\OneTimePassport;

class LinkManagerController extends Controller
{

    use OneTimePassport;

    public function protectedEmailAttachment($id)
    {
        $otp = $this->otpGetUrl($id)->toArray();

        if(isset($otp['metadata']['user_id']) && $otp['metadata']['user_id'] == \Auth::user()->id) {
            $url = getAwsSignedURL(composeUrl($otp['app_url'], false));
            if(!$url) 
                abort(404);

            header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            header('Expires: Sat, 26 Jul 1970 05:00:00 GMT');
            header('Location: '.$url.'', true, 307);
            exit;
        }
        abort(404);
    }

    public function emailAttachment($id) 
    {
        $otp = $this->otpGetUrl($id);
        if (!$otp)
            abort(404);
        $this->headerLocation(getAwsSignedURL(composeUrl($otp['metadata']['file_path'], false)));
        exit();
    }


    public function displayAssert(Request $request)
    {
        if (stripos($request->file_path, '../') !== false || stripos($request->file_path, '..\\') !== false) {
            abort(404);
        }
        if (is_numeric(stripos($request->file_path, config('constants.s3.url')))) {
            $this->headerLocation(getAwsSignedURL($request->file_path));
        }
        else if (stripos($request->file_path, 'http') === false) {
            $this->headerLocation(getAwsSignedURL($request->file_path, false));
        }
        else {
            $this->headerLocation($request->file_path);
        }
        exit();
    }

    public function headerLocation($file_url)
    {
        header("Location: ".$file_url);
        header('Cache-Control: no-cache');
        header('Pragma: no-cache');
    }
}