<?php

namespace App\Http\Controllers\v2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\v2\FileViewer;

class FileViewerController extends Controller
{
    use FileViewer;

    const DOCUMENT_EXTENSIONS = ['pptx', 'ppt', 'ppsx', 'pps', 'potx', 'ppsm', 'doc', 'docx', 'dotx'];
    const PDF_EXTENSION = ['pdf'];

    public function getViewer(Request $request)
    {
    	return $this->getViewerUrl($request);
    }

    public function downloadFile(Request $request)
    {
		return $this->downloadCloudFile($request);
    }

    public function postAttachment($url)
    {   
    	$signed_url = getAwsSignedURL(base64_decode($url));
	    $ch = curl_init();
	    $ch = curl_init($signed_url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 25);
	    $content = curl_exec($ch);
	    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    curl_close($ch);
	    if($httpcode != 200 ) abort(404);

	    header('Content-Type: application/octet-stream');
	    header("Content-Transfer-Encoding: Binary");
	    header("Content-disposition: attachment; filename=filename");
	    return $content; 
	}
}