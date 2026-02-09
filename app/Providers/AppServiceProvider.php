<?php

namespace App\Providers;

use PgSql\Result;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {    

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('ja');

        if (app()->environment('production')) {
        URL::forceScheme('https');
        }

        Gate::define('manage-trainings', function ($user) {
            return in_array((int)$user->role_id, [1,2], true);
        });
    }

}
