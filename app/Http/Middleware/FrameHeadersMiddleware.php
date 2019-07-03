<?php

namespace App\Http\Middleware;

use Closure;

class FrameHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     */

    protected $except_urls = [
        'auth/linkedin'
    ];

    public function handle($request, Closure $next) {
        $regex = '#' . implode('|', $this->except_urls) . '#';

        $response = $next($request);
        if (preg_match($regex, $request->path())){
            return $response;
        }
        $response->header('X-Frame-Options', 'SAMEORIGIN');
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-XSS-Protection', '1; mode=block');
        return $response;
    }
}
