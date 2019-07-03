<?php

use Faker\Generator as Faker;

$factory->define(App\Models\User::class, function (Faker $faker) {

    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'password' => bcrypt('Secret1234'),
        'user_type_id' => rand(2,3),
        'registration_status' => 1   
    ];
});
