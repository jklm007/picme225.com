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
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        } elseif (Setting::get('language')) {
            App::setLocale(Setting::get('language'));
        } else {
            App::setLocale('fr');
        }
        return $next($request);
    }
}
