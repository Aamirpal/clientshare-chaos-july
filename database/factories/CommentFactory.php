<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;
use App\Models\User;
use App\Models\Post;

$factory->define(App\Models\Comment::class, function (Faker $faker) {
	$user = factory(User::class)->create();
	$post = factory(Post::class)->create();
	return [
	    'post_id'=> $post->id,
	    'user_id'=> $user->id,
	    'comment'=>$faker->paragraph
    ];
});
