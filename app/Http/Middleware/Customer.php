<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Customer
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->isCustomer()) {
            return $next($request);
        }
        if(!auth()->check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        return response()->json(['message' => 'Forbidden'], 403);

    }
}
