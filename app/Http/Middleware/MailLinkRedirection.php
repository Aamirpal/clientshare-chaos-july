<?php

namespace App\Http\Middleware;

use Closure;
use Redirect;
use Cookie;
use Auth;
use Config;
use App\Traits\Generic;
use App\Http\Controllers\ManageShareController;

class MailLinkRedirection {

  use Generic;
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle($request, Closure $next) {
    $getData = $request->all();

    if(isset($getData['email']) && base64_decode($getData['email']) != auth::user()->email) {
      Auth::logout();
      $request->session()->flush();
      return redirect($request->url().'?'.$_SERVER['QUERY_STRING']);
    }
    if(isset($getData['like'])) {
      $request['post_id'] = $request->route('post_id');
      $request['endorse'] = $request->like;
      (new ManageShareController)->endorsePost($request);
    }
    return $next($request);
  }
}
