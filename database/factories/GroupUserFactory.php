<?php

use Faker\Generator as Faker;
use App\Models\User;
use App\Models\Group;

$factory->define(App\Models\GroupUser::class, function (Faker $faker) {
    return [
        'group_id'=> factory(Group::class)->create(),
        'user_id'=> factory(User::class)->create()
    ];
});
