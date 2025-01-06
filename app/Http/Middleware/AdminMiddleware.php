<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!$request->user()|| !$request->user()->isAdmin()){

            log::info('User Role:', ['role' => $request->user()->role]);
            return response()->json([
                "message"=>'UnAuthorized'
            ],403);
        }
        return $next($request);
    }

}
