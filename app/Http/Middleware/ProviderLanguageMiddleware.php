<?php

namespace App\Http\Middleware;

use Closure;

class ProviderLanguageMiddleware
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
        \Config::set('auth.providers.users.model', 'App\Provider');

       if (\Auth::check()) {
            $language = "en";
            if(\Auth::user()->language){
               $language = \Auth::user()->language;
           }
          
           \App::setLocale($language);
         
       }
        return $next($request);
    }
}
