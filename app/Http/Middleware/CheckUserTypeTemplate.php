<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\Models\UserType;
use App\Models\User;
class CheckUserTypeTemplate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    { 
        if(!empty(Auth::User())){
            $loggedintype = Auth::User();
            $usertypes = UserType::all()->toArray();
        // User details in Sesison
            foreach ($usertypes as $user) { 
                $usertypeid = $user['id'];
                $usertype = $user['user_type_name'];
                if($loggedintype['user_type_id'] == $usertypeid){
                    $request->session()->put('key',[$loggedintype]);
                    $request->session()->put('usertype',$usertype);
                    $request->session()->put('layout','layouts.main'); 
                }
            }  
        }
        $response = $next($request);
        return $response;
    }
}
?>
