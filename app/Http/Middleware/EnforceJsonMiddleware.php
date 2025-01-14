<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceJsonMiddleware
{
     /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the 'Accept' header contains 'application/json'
        if (!$request->hasHeader('Accept') || $request->header('Accept') !== 'application/json') {
            return response()->json(['message' => 'Accept header must be application/json'], 406);
        }

        return $next($request);
    }
}
