<?php

namespace App\Http\Middleware;

use Closure;

class AbortUnwantedRoute
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
        $abort_paths=[
            'register'
        ];
        if(in_array($request->path(), $abort_paths))
            abort(404);

        return $next($request);
    }
}
