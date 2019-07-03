<?php

use Faker\Generator as Faker;
use App\Models\User;
use App\Company;


$factory->define(App\Models\Space::class, function (Faker $faker) {
	$user = factory(User::class)->create();

	return [
        'share_name' => $faker->firstName,
        'user_id' => $user->id,
        'company_id' => $faker->UUID,
        'company_seller_id' => factory(Company::class)->create()->id,
        'company_buyer_id' => factory(Company::class)->create()->id
    ];
});
