<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Setting;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\FirebaseNotificationRequested::class,
            \App\Listeners\SendFirebaseNotificationListener::class
        );

        if (class_exists(\App\Models\BarberShop::class)) {
            \App\Models\BarberShop::observe(\App\Observers\BarberShopObserver::class);
        }
        if (class_exists(\App\Models\Booking::class)) {
            \App\Models\Booking::observe(\App\Observers\BookingObserver::class);
        }
        if (class_exists(\App\Models\Customer::class)) {
            \App\Models\Customer::observe(\App\Observers\CustomerObserver::class);
        }
        if (class_exists(\App\Models\Payment::class)) {
            \App\Models\Payment::observe(\App\Observers\PaymentObserver::class);
        }
        if (class_exists(\App\Models\Service::class)) {
            \App\Models\Service::observe(\App\Observers\ServiceObserver::class);
        }
        if (class_exists(\App\Models\Barber::class)) {
            \App\Models\Barber::observe(\App\Observers\BarberObserver::class);
        }
        $this->configureAuth();
        $this->configureCommands();
        $this->configureDates();
        $this->configureModels();
        $this->configurePasswordValidation();
        $this->configureHttp();
        $this->configureViews();
        $this->configureRateLimiting();
        $this->configureSanctum();
    }

    private function configureSanctum(): void
    {
        \Laravel\Sanctum\Sanctum::usePersonalAccessTokenModel(
            \App\Models\Sanctum\PersonalAccessToken::class
        );
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    private function configureAuth(): void
    {
        Gate::before(function (?User $user) {
            if (!$user) return null;

            return $user->is_global_admin ?: null;
        });
    }

    private function configureCommands(): void
    {
        DB::prohibitDestructiveCommands(Application::getInstance()->isProduction());
    }

    private function configureDates(): void
    {
        Date::use(CarbonImmutable::class);
    }

    private function configureModels(): void
    {
        Model::shouldBeStrict(! Application::getInstance()->isProduction());
    }

    private function configureHttp(): void
    {
        Http::globalOptions([
            'headers' => [
                'User-Agent' => config('app.user_agent'),
            ],
        ]);

        if (Config::string('app.env') !== 'local') {
            URL::forceScheme('https');
        }
    }

    private function configurePasswordValidation(): void
    {
        Password::defaults(fn () => Password::min(8)
            ->mixedCase()
            ->letters()
            ->numbers()
            ->uncompromised()
        );
    }

    private function configureViews(): void
    {
        view()->composer('components.layouts.app', function () {
            if (Auth::check()) {
                $settings = cache()->remember('settings', 3600, fn () => Setting::all());
                foreach ($settings as $setting) {
                    config()->set([$setting->key => $setting->value]);
                }
            }
        });
    }
}
