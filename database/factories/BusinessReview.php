<?php

use Faker\Generator as Faker;
use App\Models\{
    Space,
    User,
    Group
};

$factory->define(App\Models\BusinessReview::class, function (Faker $faker) {
    $space = factory(Space::class)->create();
    $user = factory(User::class)->create();
    $group = factory(Group::class)->create();
    return [
        'space_id' => $space->id,
        'user_id' => $user->id,
        'title' => $faker->word,
        'description' => $faker->word,
        'review_date' => $faker->date,
        'group_id' => $group->id,
        'conducted_via' => rand(1, 4),
    ];
});
