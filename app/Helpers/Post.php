<?php
namespace App\Helpers;

use Image;
use Illuminate\Http\Request;
use App\{Post as PostModel, PostMedia, SpaceUser};
use App\Http\Controllers\ManageShareController;

class Post {

	public function getAttachmentInfo($url){
		$file_data = \App\PostMedia::PostFile($url);
		if(!sizeOfCustom($file_data)) $file_data = \App\Media::attachment($url);
		if(!sizeOfCustom($file_data)) $file_data = \App\CommentAttachment::commentFile($url);
		return $file_data;
	}

	public function postFileTypeFilter($request_data){
		$file_type_filter='';
		if(isset($request_data['file_type']) && sizeOfCustom($request_data['file_type'])){
			$file_types=[];
			foreach($request_data['file_type'] as $key ) {
				array_push($file_types, PostMedia::FILE_FILTERS[$key]);
			}
			$request_data['file_type'] = implode("','",array_map(function($a) {return implode("','",$a);},$file_types));
			$file_type_filter = "and lower(file_extention) in ('".$request_data['file_type']."')";
		}
		return $file_type_filter;
	}

	public function likePost($post_id, $user_id){
		$post_data = PostModel::find($post_id)->toArray();
		$request = new Request;
		$request['endorseid'] = $post_data['id'];
		$request['userid'] = $user_id;
		$request['spaceid'] = $post_data['space_id'];
		$request['posthonor'] = $post_data['user_id'];
		$request['like_status'] = 0;
		$request['liked_from_email'] = 1;
		(new ManageShareController)->endorse($request);
		
	}

	public function shareAccessable( $space_id, $user_id ){
		return SpaceUser::getActiveSpaceUser($space_id, $user_id, 'count');
	}

	public function postUser( Request $request){

		$post = PostModel::where('id', $request['post_id'])->select('visibility', 'space_id')->first();

		$visibility = is_numeric(stripos($post['visibility'], 'all')) ? null: $this->formatPostUser($post);

		$data = PostModel::postUsers($post['space_id'], $request['term'], $visibility);

		if(!trim($request->term) || is_numeric(stripos('all', trim($request->term)))){
			$all = ['company_name'=>'Everyone included in this post', 'display_name' => 'All', 'value'=>'@All', 'uid'=>'user:all', 'user_id'=>config('constants.USER_ID_DEFAULT'), 'user_status'=>''];
			array_unshift($data, $all);
		}
		return $data;
	}

	private function formatPostUser($post_data){
		$post_data['visibility'] = "'".str_replace(",", "','", $post_data['visibility'])."'";
		return $post_data['visibility'];
	}

	public function mergeImage($base_image, $url=null, $dimensions=false){
		$base_image = Image::make(getAwsSignedURL($base_image));
		$url = $url??composeUrl("/see-more-img.png", false);
		$top_image = Image::make(getAwsSignedURL($url));
		$width = isset($dimensions['thumbnail_image_width'])?$dimensions['thumbnail_image_width']:$top_image->getWidth();
		$height = isset($dimensions['thumbnail_image_height'])?$dimensions['thumbnail_image_height']:$top_image->getHeight(); 
		$base_image->resize($width, $height);
		$base_image->insert($top_image, 'center')->encode('data-url');
		$name = rand()."_".time().".png";
		$s3 = \Storage::disk('s3');
		$s3_bucket = env("S3_BUCKET_NAME");
		$filePath = '/company_logo/' . $name;
		$full_url = composeUrl($filePath, false);
		$s3->put($filePath, file_get_contents($base_image), 'public');
		return $full_url;
	}
}