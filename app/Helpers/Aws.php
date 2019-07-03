<?php
namespace App\Helpers;

use App\User;
use Storage;
use Illuminate\Http\Request;

class Aws {

	public function breakURL($url)
	{
		return preg_replace(config('constants.s3.S3_PATH_REGEX'), '', urldecode($url));
	}

	public function getAwsSignedURL($url, $full_url=true) 
	{
	    
	    if($full_url) {
	    	$url = $this->breakURL($url);
	    }
		return $this->requestSignedUrl($url);
  	}

  	public function requestSignedUrl($file_path, $expiry=5, $content_disposition ='inline', $file_name='')
  	{
  		$s3 = Storage::disk('s3');
	    if(!$s3->exists($file_path)) 
	    	return 0;
	    
	    $client = $s3->getDriver()->getAdapter()->getClient();
	    $expiry = "+{$expiry} minutes";
	    $command = $client->getCommand('GetObject', [
	      'Bucket' => env("S3_BUCKET_NAME"),
	      'Key'    => $file_path,
	      'ResponseContentDisposition' => "$content_disposition; filename=\"" . $file_name . '"'
	    ]);    
	    $response = $client->createPresignedRequest($command, $expiry);
	    return (string) $response->getUri();
  	}

	public function copyAllProfileImagesToAWS(){
		$users = (new User)->getNonAWSProfileImages();
		foreach ($users as $user)
			$this->copyProfileImage($user);
	}

	public function copyProfileImage($user){
		$current_url = composeUrl($user->profile_image);
		$extension = 'png';
	    $file_name = time().'.' .$extension;
	    $s3_bucket = env("S3_BUCKET_NAME");
	    $file_path = '/profile_images/'.$user->id.'/'.$file_name;

		$full_url = config('constants.s3.url').$s3_bucket."".$file_path;
		if($this->copyFileToAWS($current_url, $file_path))
			(new User)->updateProfileImage($user->id, $full_url);
		return $full_url;
	}

	private function copyFileToAWS($current_url, $new_url){
		try {
			$s3 = Storage::disk('s3');
			if( $s3->put($new_url, file_get_contents($current_url), 'public') ){
		    	return ['path'=>$new_url];
		    }
		} catch(\Exception $e){
			return false;
		}
	}

	public function image( $file, Request $request ){

		
	    $image = $request->file($file['name']);
	    $s3 = Storage::disk('s3');
	    $extension = $request->file($file['name'])->guessExtension();
	    $imageFileName = time() . '.' .$extension;

	    $s3_bucket = env("S3_BUCKET_NAME");
	    $filePath = '/destination_images/' . $imageFileName;

	    $fullurl = "https://s3-eu-west-1.amazonaws.com/".$s3_bucket."".$filePath;

	    if( $s3->put($filePath, file_get_contents($file['temp_name']), 'public') ){
	    	return ['path'=>$fullurl];
	    }
	    return null;
	}


	/* Change visiblity/ACL */
	public function change_visibility($url, $acl='private'){
		

		$url_arr = explode("/", urldecode($url));
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
		$s3 = Storage::disk('s3');
		$s3->setVisibility(implode('/', $url_arr2), $acl);
		return;
	}


	public function uploadClientSideSetup($acl = 'private') {

	    // Options and Settings
	    $aws_key = env('AWS_ACCESS_KEY_ID');
	    $aws_secret = env('AWS_SECRET_ACCESS_KEY');
	    $region = env('AWS_REGION');
	    $s3_bucket = env('S3_BUCKET_NAME');

	    $algorithm = "AWS4-HMAC-SHA256";
	    $service = "s3";
	    $date = gmdate("Ymd\THis\Z");
	    $short_date = gmdate("Ymd");
	    $request_type = "aws4_request";
	    $expires = "86400"; // 24 Hours
	    $success_status = "201";
	    $url = "//".env('AWS_REGION_PREFIX')."{$region}.amazonaws.com/{$s3_bucket}";

	    // Step 1: Generate the Scope
	    $scope = [
	        $aws_key,
	        $short_date,
	        $region,
	        $service,
	        $request_type
	    ];
	    $credentials = implode('/', $scope);

	    // Step 2: Making a Base64 Policy
	    $policy = [
	        'expiration' => gmdate('Y-m-d\TG:i:s\Z', strtotime('+'.$expires.' seconds')),
	        'conditions' => [
	            ['bucket' => $s3_bucket],
	            ['acl' => $acl],
	            ['starts-with', '$key', ''],
	            ['starts-with', '$Content-Type', ''],
	            ['success_action_status' => $success_status],
	            ['x-amz-credential' => $credentials],
	            ['x-amz-algorithm' => $algorithm],
	            ['x-amz-date' => $date],
	            ['x-amz-expires' => $expires],
	        ]
	    ];
	    $base_64_policy = base64_encode(json_encode($policy));

	    // Step 3: Signing your Request (Making a Signature)
	    $date_key = hash_hmac('sha256', $short_date, 'AWS4' . $aws_secret, true);
	    $date_region_key = hash_hmac('sha256', $region, $date_key, true);
	    $date_region_service_key = hash_hmac('sha256', $service, $date_region_key, true);
	    $signing_key = hash_hmac('sha256', $request_type, $date_region_service_key, true);

	    $signature = hash_hmac('sha256', $base_64_policy, $signing_key);

	    // Step 4: Build form inputs
	    // This is the data that will get sent with the form to S3
	    $inputs = [
	        'Content-Type' => '',
	        'acl' => $acl,
	        'success_action_status' => $success_status,
	        'policy' => $base_64_policy,
	        'X-amz-credential' => $credentials,
	        'X-amz-algorithm' => $algorithm,
	        'X-amz-date' => $date,
	        'X-amz-expires' => $expires,
	        'X-amz-signature' => $signature
	    ];

	    return compact('url', 'inputs');
	}
}