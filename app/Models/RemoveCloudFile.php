<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemoveCloudFile extends Model
{
    protected $fillable = ['file_url', 'file_cloud_path'];
    protected $casts = [
      'file_cloud_path' => 'json'
    ];
}
