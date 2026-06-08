<?php

namespace App\Providers;

use App\Support\ClientIp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
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
        Request::macro('clientIp', fn () => ClientIp::from($this));

        if ($this->app->environment('production')) {
            URL::forceScheme('https');

            if ($root = config('app.url')) {
                URL::forceRootUrl(rtrim($root, '/'));
            }
        }
    }
}
