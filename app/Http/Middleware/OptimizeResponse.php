<?php

namespace App\Http\Middleware;

use Closure;

/**
 * PERFORMANCE: Adds Cache-Control headers to API responses
 * and removes costly X-Powered-By header.
 */
class OptimizeResponse
{
    /**
     * Routes that should have short-lived cache headers (60s).
     * These are data that changes infrequently.
     */
    protected $cacheablePatterns = [
        'api/services',
        'api/service-types',
        'api/pdp-stops',
        'api/pdp-routes',
    ];

    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Remove unnecessary headers that add overhead
        $response->headers->remove('X-Powered-By');

        // Add Cache-Control for cacheable GET endpoints
        if ($request->isMethod('GET') && $this->isCacheable($request)) {
            $response->headers->set('Cache-Control', 'public, max-age=60, stale-while-revalidate=30');
        } elseif ($request->isMethod('GET') && !$request->is('api/login')) {
            // For other GETs: no-cache to ensure validation while allowing cookies
            $response->headers->set('Cache-Control', 'no-cache, private');
        }

        // Always add Vary for proper caching with auth
        $response->headers->set('Vary', 'Accept, Authorization');

        return $response;
    }

    protected function isCacheable($request): bool
    {
        $path = $request->path();
        foreach ($this->cacheablePatterns as $pattern) {
            if (str_contains($path, $pattern)) {
                return true;
            }
        }
        return false;
    }
}
