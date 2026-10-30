<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $tz = config('app.timezone', env('APP_TIMEZONE', 'Europe/Tirane'));
        date_default_timezone_set($tz);
        config(['app.timezone' => $tz]);

        // Explicitly register Event Listeners for Push & SMS Gateway
        Event::listen(
            \App\Events\FirebaseNotificationRequested::class,
            \App\Listeners\SendFirebaseNotificationListener::class
        );
        Event::listen(
            \App\Events\BookingChanged::class,
            \App\Listeners\SendFirebaseNotificationListener::class
        );

        if ($this->app->environment('production') || env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        Password::defaults(function () {
            return Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised();
        });
    }
}
