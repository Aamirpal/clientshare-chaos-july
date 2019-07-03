<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Redirect;
use Closure;
use Symfony\Component\HttpFoundation\Cookie;
use Session;
use App\Models\UserType;
use Auth;
use Route;

class AdminTwoWayAuthRedirect 
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
    ];


     public function handle($request, Closure $next) {
        
       if(Route::getCurrentRoute()->uri() != 'verify_code' && ($time = Session::get('verification_in_process')) && UserType::find(Auth::user()->user_type_id)->user_type_name == 'super_admin'){
                if(time() - $time > 3000){
                    Session::forget('verification_in_process');
                    return redirect('/logout');
                }
                return redirect('/verify_code');
            }
              return $next($request);
     }
}
