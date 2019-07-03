<?php

namespace App\Repositories\Space;

use App\Models\Space as SpaceModel;

use App\Repositories\Space\SpaceInterface;
use App\Models\RemoveCloudFile;

class SpaceRepository implements SpaceInterface
{

	protected $space;

    public function __construct(SpaceModel $space)
    {
      $this->space = $space;
    }

    public function communitySpaceInfo($space_id)
    {
    	return $this->space
			->select('id', 'share_name', 'company_seller_id', 'company_buyer_id')
			->with('seller','buyer')
			->find($space_id)->toArray();
    }

    public function allSpacesWithSelectedColoumn($columns = [])
    {
      if(count($columns) <= 0){
         return $columns;
      }
      $space_ids = csvToArraySoftLaunch();
      $space = $this->space->select($columns);
      if($space_ids){
        $space->whereIn('id', $space_ids);
      }
      return $space->get();
    }

    public function getAllSpaces()
    {
      return $this->space->select('id')->get();
    }

    public function update($space_data, $space_id)
    {
      return $this->space->where('id', $space_id)->update($space_data);
    }

    public function isBusinessReviewEnabled($space_id) {
        return $this->space->select('is_business_review_enabled')->where('id', $space_id)->first();
    }

    public function saveTwitterHandler($request)
    {
        if(empty(array_filter($request['twitter_handles'])))
            $this->space->where('id', $request['space_id'])->update(['twitter_handles' => config('constants.EMPTY_JSON')]);

        $request['twitter_handles'] = array_filter($request['twitter_handles']);
        if(!empty($request['space_id']) && !empty($request['twitter_handles'])){
            $this->space->where('id', $request['space_id'])->update(['twitter_handles' => json_encode($request['twitter_handles'])]);
        }
        return $this->getTwitterHandler($request['space_id']);
    }

    public function getTwitterHandler($space_id)
    {
        $twitter_handlers =  $this->space->whereId($space_id)->pluck('twitter_handles')->toArray();
        $twitter_handlers = is_array($twitter_handlers) ? $twitter_handlers[0] : $twitter_handlers;
        if(!$twitter_handlers){
            return [];
        }
        $twitter_handlers = json_decode($twitter_handlers, true);
        if(isset($twitter_handlers[""])){
            return [];
        }
        return $twitter_handlers;
    }

    public function shareCategorySheet()
    {
      
      $spaces = $this->allSpacesWithSelectedColoumn(['id','share_name','category_tags']);
      $data = [];
      foreach($spaces as $key => $space){
        foreach(json_decode($space->category_tags,true) as $key_cat => $category){
          $data[$key.'_'.$key_cat]['share_id']=$space->id;
          $data[$key.'_'.$key_cat]['share_name']=$space->share_name;
          $data[$key.'_'.$key_cat]['share_category']=$category;
        }
      }
      
      $file = \Excel::create('space_categories', function($excel) use($data){
        $excel->sheet('General', function($sheet) use($data){
          $sheet->fromArray($data);
        });
      });

      $file_info = [
        'folder' => '/space_category_sheet/',
        'file_name' => 'SpaceCategorySheet_'.time().'.xls',
        's3_url' => config('constants.s3.url'),
        'file_content' => $file->string('xls')
      ];

      $uploaded_file_url = uploadFileOnS3($file_info);
      return $uploaded_file_url;
    }

    public function migrateVersion($version, $space_ids){
        $space = $this->space->where('version', '!=', ($version == 'V2') ? true : false);
        if($space_ids){
          $space->whereIn('id', $space_ids);
        }
        $space->update(['version'=>($version == 'V2') ? true : false]);
        
        if(!$space_ids){
          $sql = ($version == 'V2') ? "ALTER TABLE spaces ALTER COLUMN version SET DEFAULT true" : "ALTER TABLE spaces ALTER COLUMN version SET DEFAULT false";
          \DB::statement($sql);
        }
    }

    public function getShareProfileData($space_id){
        return $this->space->withCount('QuickLinks', 'Posts', 'spaceMember', 'spaceAdmin')
                        ->where('id', $space_id)
                        ->first()->toArray();
    }

    Public function getRenameProcessLogoShares()
    {
      return $this->space->select('id', 'buyer_processed_logo', 'seller_processed_logo')
          ->where(function($q){
            $q->whereNotNull('buyer_processed_logo')
              ->orWhereNotNull('buyer_processed_logo');
          })  
          ->get();
    }

    public function renameProcessLogo($space)
    {
      $buyer_processed_logo = $space->buyer_processed_logo;
      $seller_processed_logo = $space->seller_processed_logo;
      $data['buyer_processed_logo'] = $this->s3CreateNewLogo($buyer_processed_logo);
      $data['seller_processed_logo'] = $this->s3CreateNewLogo($seller_processed_logo);
      $this->space->whereId($space->id)->update($data);
    }

    private function s3CreateNewLogo($logo)
    {
      $old_file_info = $this->getFilePath($logo);
      if(!$old_file_info['path']){
        return $logo;
      }
      $s3 = \Storage::disk('s3');
      if($s3->exists($old_file_info['path'])) {
        $new_file_path = $old_file_info['folder'].time().'_'.rand().'.'.$old_file_info['extension'];
        $new_file_url = $old_file_info['host'].'/'.env("S3_BUCKET_NAME").'/'.$new_file_path;
        
        if($s3->copy($old_file_info['path'], $new_file_path)) {
          $this->oldFileDelete($logo, $old_file_info);
          return $new_file_url;
        }
        return $new_file_path;
      }
    }

    private function oldFileDelete($logo, $file_info)
    {
      $cloud_path = [
        'path' => [$file_info['folder']],
        'file' => $file_info['name'] 
      ];
      $file_delete_info = [
        'tag' => 's3',
        'file_url' => $logo,
        'file_cloud_path' => json_encode($cloud_path),
      ];
      RemoveCloudFile::create($file_delete_info);
    }

    private function getFilePath($logo){
      $url = parse_url($logo);
      if(!isset($url['path'])){
        return false;
      }
    
      $path_array = explode('/', ltrim($url['path'], '/'));
      if(!isset($path_array[0])){
        return false;
      }
      $folder = '';
      for($i = 1; $i < (count($path_array)-1); $i++){
        $folder .= $path_array[$i] . '/';
      }
      $output['host'] = $url['host'];
      $output['folder'] = $folder;
      $output['path'] = $folder . $path_array[$i];
      $output['name'] = $path_array[$i];
      $output['extension'] = pathinfo($path_array[$i], PATHINFO_EXTENSION);
      return $output;
    }
}