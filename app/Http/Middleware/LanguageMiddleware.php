<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use Illuminate\Foundation\Application;
use App;
use Session;

class LanguageMiddleware
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
        if (\Auth::check() && \Auth::User()->language) {
            App::setLocale(\Auth::User()->language);
        } elseif (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        } elseif (Setting::get('language')) {
            App::setLocale(Setting::get('language'));
        }
        return $next($request);
    }
}
