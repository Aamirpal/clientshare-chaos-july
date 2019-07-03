<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Repositories\User\UserInterface;

class UserController extends Controller {

    protected $user;

    public function __construct(UserInterface $user) {
        $this->user = $user;
    }

}
