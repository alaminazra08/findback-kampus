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
        $views = [
            'welcome',
            'auth.login',
            'auth.register',
            'auth.forgot-password',
            'auth.reset-password',
            'user.dashboard',
            'user.layouts.campus',
            'user.partials.premium-shell',
            'admin.dashboard',
            'admin.layouts.items',
            'admin.partials.brand',
        ];

        \Illuminate\Support\Facades\View::composer($views, function ($view): void {
            $attributes = request()->attributes;
            $key = \App\Support\AdminSettings::class . '.branding';
            if (!$attributes->has($key)) {
                $attributes->set($key, \App\Support\AdminSettings::all());
            }
            $settings = $attributes->get($key);

            $view->with('fbBrand', $settings);
            $view->with('adminSettings', $settings);
        });
    }
}
