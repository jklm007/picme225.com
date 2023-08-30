<?php

namespace App\Http\Middleware;

use Closure;
use Setting;
use Illuminate\Http\Request;

use Illuminate\Foundation\Application;
use App;
use Session;

class LandingLanguageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
         if (Setting::get('language')) {
           $language = Setting::get('language');
              \Log::info(Setting::get('language'));
           App::setLocale($language);
       }
        return $next($request);
    }
}
