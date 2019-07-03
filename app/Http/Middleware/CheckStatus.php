<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\SpaceUser;
use Session;
use Cookie;
use Redirect;

class CheckStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
     $user = SpaceUser::with('Share')
     ->where('user_id', Auth::user()->id)
     ->where('user_status', '0')->whereRaw("metadata->>'user_profile' !=''")
     ->first();
     if(Auth::User()->user_type_id == 1){
       $response = $next($request);
       return $response;
     } else {
       if( !sizeOfCustom($user) ){ 
         if(Auth::check() && \Session::get('space_info') !== null){ 
           $user_inactive = SpaceUser::with('Share')
           ->where('user_id', Auth::user()->id)
           ->where('space_id', \Session::get('space_info')['id'])
           ->where('user_status', '0')->whereRaw("metadata->>'user_profile' is null ")
           ->get();
           if( !sizeOfCustom($user_inactive) ){
             if(isset($request->shareid) || isset($request->_shareToken) || \Session::get('user_space_by_email') !== null ){ 
              $response = $next($request);
              return $response; 

                           } else{ 
                           Auth::logout();
                           return redirect('/login');
                         } 
                       }
               } else { 
               if(\Session::get('user_space_by_email') !== null){ 
                $space_id_by_email = Session::get('user_space_by_email')[0]['space_id'];
                if(!empty($space_id_by_email)){
                  $response = $next($request);
                  return $response;
                }

              }
            }
          } 
          $response = $next($request);
          return $response;
        }
    }
}