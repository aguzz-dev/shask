<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Cache-Control', 'no-store');
        return $response;
    }
}
