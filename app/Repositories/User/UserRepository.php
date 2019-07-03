<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\User\UserInterface;

class UserRepository implements UserInterface {

    protected $user;

    public function __construct(User $user) {
        $this->user = $user;
    }
}
