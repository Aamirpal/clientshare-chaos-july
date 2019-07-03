<?php

namespace App\Http\Middleware;

use Closure;

use App\Space;
use App\Traits\Generic;

class IpRestriction
{
    use Generic;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(isset($request->space_id)){
            $space_info = (new Space)->spaceInfo(['ip_restriction', 'allowed_ip'], $request->space_id);
            if($space_info->ip_restriction && !$this->ipChecker($space_info, getRealIpAddr())) {
                abort(511);
            }
        }

        return $next($request);
    }
}
