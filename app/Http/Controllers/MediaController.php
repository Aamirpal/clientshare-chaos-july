<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Media;
use Session;
use Redirect;
use FFMpeg;

class MediaController extends Controller
{
	public function delete_media($id)
	{
		$post = new Media;
		$post = Media::where('id',$id);
		$post->delete();
		Session::flash('message', "Deleted Sucessfully.");
		return Redirect::back();
	}

	public function edit_media()
	{ 

	}

	public function convert_video($source_video)
	{
		$file_name = basename($source_video);
		$pieces = explode(".", $file_name);
        $abc = exec("ffmpeg -i ".$source_video." -r 23 -c:v libx264 -strict experimental ".public_path().'/'.$pieces[0].".mp4");
        $file = public_path().'/'.$pieces[0].'.mp4';
        if( !empty($file) ) {
        	$s3 = \Storage::disk('s3');
        	$s3_bucket = env("S3_BUCKET_NAME");
        	$name = rand()."_".time().".mp4";
        	$filePath = '/pdf_files/'.$name;
        	$fullurl = "https://s3-eu-west-1.amazonaws.com/".$s3_bucket."".$filePath;
        	$s3->put($filePath, file_get_contents($file), 'public');
        	return $fullurl;
        }
    }
}
