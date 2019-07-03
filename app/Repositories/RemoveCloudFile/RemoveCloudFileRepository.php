<?php

namespace App\Repositories\RemoveCloudFile;

use App\Repositories\RemoveCloudFile\RemoveCloudFilenterface;
use App\Models\RemoveCloudFile;

class RemoveCloudFileRepository implements RemoveCloudFileInterface
{
  protected $remove_cloud_files;
  
  public function __construct(RemoveCloudFile $remove_cloud_files) 
  {
    $this->remove_cloud_files = $remove_cloud_files;
  }

  public function logFiles($files)
  {
    foreach ($files as $key => $file) {
      $file = array_merge($file, 
      [
        'tag' => 's3',
        'file_url' => $file['metadata']['url'],
        'file_cloud_path' => $file['s3_file_path']
      ]);
      $this->remove_cloud_files->create($file);
    }
  }
}