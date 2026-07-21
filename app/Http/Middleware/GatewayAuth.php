<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GatewayAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('X-Gateway-Token');
        $secretKey = env('GATEWAY_SECRET_KEY', 'default_secret_key');

        if (!$token || $token !== $secretKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized Gateway Access. Invalid or missing X-Gateway-Token.'
            ], 401);
        }

        return $next($request);
    }
}
