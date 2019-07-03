<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostActivity extends Model
{
	protected $fillable = ['post_id', 'user_id', 'metadata'];
	protected $casts = ['metadata' => 'json'];
}
