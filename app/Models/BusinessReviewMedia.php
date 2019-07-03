<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessReviewMedia extends Model {

    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $hidden = ['created_at', 'updated_at'];
    protected $casts = [
        'metadata' => 'json',
        's3_file_path' => 'json'
    ];
    protected $appends = ['post_file_url'];

    public function getPostFileUrlAttribute() {
        return wrapUrl(composeUrl($this->s3_file_path));
    }

}
