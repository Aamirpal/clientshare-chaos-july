<?php

use Faker\Generator as Faker;
use App\Models\Space;

$factory->define(App\Models\Group::class, function (Faker $faker) {
    $space = factory(Space::class)->create();

    return [
        'name' => $faker->unique()->word,
        'space_id' => $space->id
    ];
});
