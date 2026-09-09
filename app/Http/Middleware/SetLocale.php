<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;
        $user = $request->user();

        if ($user) {
            if ($user->locale) {
                $locale = $user->locale;
            } elseif ($cookie = $request->cookie('locale')) {
                if (in_array($cookie, ['en', 'zh'])) {
                    $user->update(['locale' => $cookie]);
                    $locale = $cookie;
                }
            }
        } else {
            $locale = $request->cookie('locale');
        }

        if ($locale && in_array($locale, ['en', 'zh'])) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
