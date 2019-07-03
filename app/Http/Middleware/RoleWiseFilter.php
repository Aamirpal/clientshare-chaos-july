<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use App\UserType;

class RoleWiseFilter {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $route_role, $users_route=null ) {
        $user_role = UserType::findOrFail(Auth::user()->user_type_id, ['user_type_name']);
        if($user_role['user_type_name']  != $route_role && $user_role['user_type_name']  != $users_route) {
            abort(404);
        }
        return $next($request);
    }
}
