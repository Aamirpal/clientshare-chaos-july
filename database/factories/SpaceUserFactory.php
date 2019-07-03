<?php

use Faker\Generator as Faker;
use App\Models\User;
use App\Models\Space;

$factory->define(App\Models\SpaceUser::class, function (Faker $faker) {
    
    $user = factory(User::class)->create();
    $space = factory(Space::class)->create();
    return [
        'space_id' => $space->id,
        'user_id' => $user->id,
        'metadata'=>json_encode(array('invitation_status' => 'member', 'invitation_code' => 1, 'user_profile' => [])),
        'sub_company_id' => $space->company_seller_id,
        'user_status'=>0,
        'user_type_id'=>2
    ];
});
