<?php
namespace App\Traits\v2;

use Validator;

trait FileViewer 
{

	protected function getViewerUrl($request)
	{
		if(in_array(strtolower($request->extension), $this::DOCUMENT_EXTENSIONS))
    	{
            $url = config('constants.DOCUMENT_VIEWER');
        }
		else if(in_array(strtolower($request->extension), $this::PDF_EXTENSION))
        {
            $url = config('constants.PDF_VIEWER');
        }
        else
        {
        	return apiResponse([], 400);
        }

        $signed_url = getAwsSignedURL($request->url);

		return apiResponse([
        	'pdf' => $url.env('APP_URL').'/post-attachment/'.base64_encode($request->url),
            'doc' => $url.urlencode($signed_url)
        ]);
	}

    protected function downloadCloudFile($request)
    {
        $validator = Validator::make($request->all(),[
          'url' => 'required|url',
          'file_name' => 'required'
        ]);

        if($validator->fails()){
          return apiResponseComposer(400,['errors'=>$validator->errors()]);
        }
        
        $url = getDownloadableAwsSignedURL($request->url, $request->file_name);
        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Expires: Sat, 26 Jul 1970 05:00:00 GMT');
        header('Location: '.$url.'', true, 307);
        exit;
    }
}