<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production') || env('RENDER')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Fix for Render: Write Passport keys from Env to file if missing
        if (!file_exists(storage_path('oauth-private.key')) && env('PASSPORT_PRIVATE_KEY')) {
            file_put_contents(storage_path('oauth-private.key'), str_replace('\n', "\n", env('PASSPORT_PRIVATE_KEY')));
        }
        if (!file_exists(storage_path('oauth-public.key')) && env('PASSPORT_PUBLIC_KEY')) {
            file_put_contents(storage_path('oauth-public.key'), str_replace('\n', "\n", env('PASSPORT_PUBLIC_KEY')));
        }

        \Illuminate\Support\Facades\Mail::extend('brevo', function (array $config = []) {
            return (new \Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory)->create(
                new \Symfony\Component\Mailer\Transport\Dsn(
                    'brevo+api',
                    'default',
                    config('services.brevo.key')
                )
            );
        });
    }
}
