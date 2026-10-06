<?php

namespace App\Providers;

use App\Support\ClientIp;
use App\Support\PermissionCache;
use App\Support\UserMenuPermissions;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
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

        // Les migrations modifient souvent liens et permissions : le cache des permissions doit être invalidé
        Event::listen(MigrationsEnded::class, function () {
            try {
                PermissionCache::flush();
            } catch (\Throwable) {
                // Store de cache indisponible (ex. table cache absente) : les entrées expireront d'elles-mêmes
            }
        });

        // Seul le sidebar utilise ces permissions : un composer sur layouts.*.* les recalculait pour chaque vue du layout
        View::composer('layouts.demo1.sidebar', function ($view) {
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
