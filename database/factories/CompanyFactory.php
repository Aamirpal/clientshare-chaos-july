<?php

use Faker\Generator as Faker;
use App\Company;


$factory->define(Company::class, function (Faker $faker) {

	return [
        'company_name' => $faker->unique()->word,
        'description' => $faker->sentence,
    ];
});