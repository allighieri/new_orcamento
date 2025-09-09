<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
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
        Paginator::defaultView('pagination::bootstrap-5');
        Schema::defaultStringLength(191);
        DB::statement('SET NAMES utf8mb4');
        \DB::statement('SET CHARACTER SET utf8mb4');
        \DB::statement('SET COLLATION_CONNECTION=utf8mb4_unicode_ci');
    }
}
