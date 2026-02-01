<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Get the token from the Authorization header (Bearer <token>)
        $token = $request->bearerToken();

        // 2. Get the expected token from .env configuration
        $validToken = env('EMPLOYEE_SYNC_TOKEN');

        // 3. Check if token is present and matches
        if (!$token || $token !== $validToken) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid or missing Bearer Token.'
            ], 401);
        }

        return $next($request);
    }
}
