<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
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
        Paginator::defaultView('partials.pagination');
        View::composer(['layouts.app', 'partials.period-filter'], function ($view) {
            $view->with('periods', DB::table('periode')->orderByDesc('tahun_mulai')->orderByDesc('id')->get());
        });
    }
}
