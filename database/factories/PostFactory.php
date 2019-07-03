<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;
use App\Models\Space;

$factory->define(App\Models\Post::class, function (Faker $faker) {
	$space = factory(Space::class)->create();
	return [
	    'post_description'=> $faker->title,
	    'user_id'=> $faker->uuid,
	    'space_id'=> $space->id,
	    'space_category_id'=> rand(1, 999),
	    'group_id'=> rand(1, 999),
	    'post_subject'=>$faker->paragraph
    ];
});