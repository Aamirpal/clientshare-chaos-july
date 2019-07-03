<?php
namespace App\Traits\v2;
use Storage;
use App\Models\RemoveCloudFile;

trait Aws {

	private function getAwsCredentials($seconds)
	{
		$aws_credentials = [
			'aws_key' => env('AWS_ACCESS_KEY_ID'),
		    'aws_secret' => env('AWS_SECRET_ACCESS_KEY'),
		    'region' => env('AWS_REGION'),
		    's3_bucket' => env('S3_BUCKET_NAME')
		];

		$aws_credentials = array_merge($aws_credentials, [
		    'algorithm' => "AWS4-HMAC-SHA256",
		    'service' => "s3",
		    'date' => gmdate("Ymd\THis\Z"),
		    'short_date' => gmdate("Ymd"),
		    'request_type' => "aws4_request",
		    'expires' => "$seconds",
		    'success_status' => "201",
		    'url' => "//".env('AWS_REGION_PREFIX')."{$aws_credentials['region']}.amazonaws.com/{$aws_credentials['s3_bucket']}",
		]);
	    $aws_credentials['scope'] = [
	        $aws_credentials['aws_key'],
	        $aws_credentials['short_date'],
	        $aws_credentials['region'],
	        $aws_credentials['service'],
	        $aws_credentials['request_type']
	    ];
	    $aws_credentials['credentials'] = implode('/', $aws_credentials['scope']);
	    return $aws_credentials;
	}

	private function getAwsPolicy($aws_credentials, $acl)
	{
		$policy = [
	        'expiration' => gmdate('Y-m-d\TG:i:s\Z', strtotime('+'.$aws_credentials['expires'].' seconds')),
	        'conditions' => [
	            ['bucket' => $aws_credentials['s3_bucket']],
	            ['acl' => $acl],
	            ['starts-with', '$key', ''],
	            ['starts-with', '$Content-Type', ''],
	            ['success_action_status' => $aws_credentials['success_status']],
	            ['x-amz-credential' => $aws_credentials['credentials']],
	            ['x-amz-algorithm' => $aws_credentials['algorithm']],
	            ['x-amz-date' => $aws_credentials['date']],
	            ['x-amz-expires' => $aws_credentials['expires']],
	        ]
	    ];
	    return base64_encode(json_encode($policy));
	}

	protected function getAwsToken($acl, $seconds)
    {
    	$aws_credentials = $this->getAwsCredentials($seconds);
	    $base_64_policy = $this->getAwsPolicy($aws_credentials, $acl);

	    $date_key = hash_hmac('sha256', $aws_credentials['short_date'], 'AWS4' . $aws_credentials['aws_secret'], true);
	    $date_region_key = hash_hmac('sha256', $aws_credentials['region'], $date_key, true);
	    $date_region_service_key = hash_hmac('sha256', $aws_credentials['service'], $date_region_key, true);
	    $signing_key = hash_hmac('sha256', $aws_credentials['request_type'], $date_region_service_key, true);

	    $signature = hash_hmac('sha256', $base_64_policy, $signing_key);

	    $inputs = [
	        'Content-Type' => '',
	        'acl' => $acl,
	        'success_action_status' => $aws_credentials['success_status'],
	        'policy' => $base_64_policy,
	        'X-amz-credential' => $aws_credentials['credentials'],
	        'X-amz-algorithm' => $aws_credentials['algorithm'],
	        'X-amz-date' => $aws_credentials['date'],
	        'X-amz-expires' => $aws_credentials['expires'],
	        'X-amz-signature' => $signature
	    ];

	    return ['url'=>$aws_credentials['url'], 'inputs'=>$inputs];
    }

    protected function removeAwsFile($request)
    {
    	return RemoveCloudFile::create([
			'file_url' => $request->url,
			'file_cloud_path' => filePathUrlToJson($request->url, false)
    	]);
    }
}