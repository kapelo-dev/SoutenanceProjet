<?php

namespace App\Providers;

use App\Support\ClientIp;
use App\Support\UserMenuPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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

        View::composer(['layouts.*.*', 'partials.sidebar-menu'], function ($view) {
            if (Auth::check()) {
                $view->with('userMenuPermissions', UserMenuPermissions::forUser(Auth::user()));
            }
        });

        $root = rtrim((string) config('app.url'), '/');
        $isLocalHost = $root === ''
            || str_contains($root, 'localhost')
            || str_contains($root, '127.0.0.1');

        if ($this->app->environment('production') && ! $isLocalHost && $root !== '') {
            URL::forceScheme('https');
            URL::forceRootUrl($root);
        }
    }
}
