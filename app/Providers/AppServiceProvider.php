<?php

namespace App\Providers;

use PgSql\Result;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {    
        if ($this->app->environment('local') && class_exists(\Laravel\Pail\PailServiceProvider::class)) {
            $this->app->register(\Laravel\Pail\PailServiceProvider::class);
        }  
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}
