<?php
namespace App\Traits;

use Storage;
use Illuminate\Http\Request;
use App\OTP;

trait OneTimePassport {

	/* */
	public function generate_otp($obj) {
		return OTP::create($obj);
	}

	/* */
	public function otpGetUrl($id) {
		$otp = OTP::findorfail($id);
		OTP::where('id', $id)->update(['called'=>true]);
		return $otp;
	}

	/*Get attachment*/
	public function getAttachmentUrlByOtp($otp) {
		$attachment = OTP::findorfail($otp)->toArray();
		$attachment_url = '';
		$s3_region = env("S3_REGION_NAME");
        $s3_bucket = env("S3_BUCKET_NAME");
		if(!empty($attachment['metadata']['s3_name'])){
			$attachment_url = "https://".$s3_region.".amazonaws.com/".$s3_bucket."/".$attachment['metadata']['s3_name'];
		}
		else{
			$attachment_url = $attachment['app_url'];
		}
		return $attachment_url;
	}
}