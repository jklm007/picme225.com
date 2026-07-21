<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class LogRequests
{
    public function handle($request, Closure $next)
    {
        Log::info("API_REQUEST: " . $request->method() . " " . $request->fullUrl(), $request->all());
        return $next($request);
    }
}
