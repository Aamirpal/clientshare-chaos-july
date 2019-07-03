<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Media extends Model {
    protected $casts = ['metadata' => 'json'];
    protected $appends = ['media_path'];
    protected $hidden = ['media_path_old'];
    public static function bySpaceId($space_id, $selection_method){
     	return static::where('space_id', $space_id)->$selection_method();
    }

    public static function attachment($file_name, $selection_method='first'){
		return static::whereRaw("s3_file_path->>'file' ilike '%".$file_name."'")->$selection_method();
	}

    public function getMediaPathAttribute(){
        return filePathJsonToUrl($this->s3_file_path);
    }

    public function deleteExecutiveFiles($user_id, $space_id, $value = NULL){
        $query = $this->where('user_id', $user_id)->where('space_id', $space_id);
        if(!empty($value))
            $query->whereRaw("metadata->>'originalName' ilike '%".$value['originalName']."'")->delete();
        else
            $query->delete();
    }

    public function createExecutiveFiles($user_id, $space_id, $value, $extension){
        $media = new Media;
        $media->user_id = $user_id;
        $media->space_id = $space_id;
        $media->s3_file_path = filePathUrlToJson($value['url']);
        $media->media_type = array_pop($extension);
        $media->metadata = $value;
        $media->save();
    }
}
