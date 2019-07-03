<?php

namespace App\Http\Controllers\v2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\v2\Aws;

class AwsController extends Controller
{
	use Aws;
    
    const ACL = 'private';
    const AWS_TOKEN_VALIDATION_SECONDS = 60*10;

    public function getToken()
    {
    	return apiResponseComposer(200, [], $this->getAwsToken(static::ACL, static::AWS_TOKEN_VALIDATION_SECONDS));
    }

    public function removeFile(Request $request)
    {
    	return apiResponseComposer(200, [], $this->removeAwsFile($request));
    }
}
