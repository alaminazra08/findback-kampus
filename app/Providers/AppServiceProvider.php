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
        \App\Models\LostFoundItem::observe(\App\Observers\LostFoundItemObserver::class);
        \App\Models\ItemClaim::observe(\App\Observers\ItemClaimObserver::class);
        // Share current database-backed branding with public, user and admin views.
        \Illuminate\Support\Facades\View::composer('*', function ($view): void {
            $view->with('fbBrand', \App\Support\AdminSettings::all());
        });
        \Illuminate\Support\Facades\View::composer(
            ['admin.dashboard', 'admin.layouts.items'],
            fn ($view) => $view->with('adminSettings', \App\Support\AdminSettings::all()),
        );
    }
}
