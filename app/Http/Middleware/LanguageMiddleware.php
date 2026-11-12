<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class LanguageMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale');

        if (!$locale || !in_array($locale, ['sq', 'en'], true)) {
            $locale = 'sq';
            session()->put('locale', 'sq');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
