<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Access denied. Admin privileges required.',
            ], 403);
        }

        if (!$request->user()->is_active) {
            return response()->json([
                'message' => 'Account is deactivated.',
            ], 403);
        }

        return $next($request);
    }
}
