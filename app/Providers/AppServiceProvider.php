<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Setting;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->applySiteSettings();
        Cashier::ignoreRoutes();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
    private function applySiteSettings(): void
    {
        try {
            $settings = Setting::allCached();
        } catch (\Throwable) {
            return; // the table does not exist yet (fresh install or running migrate)
        }

        $groups = [
            'contact' => ['email', 'phone', 'address'],
            'social' => ['facebook', 'instagram', 'x', 'youtube', 'telegram'],
        ];

        foreach ($groups as $group => $keys) {
            foreach ($keys as $key) {
                if (array_key_exists("{$group}.{$key}", $settings)) {
                    config(["betgo.{$group}.{$key}" => $settings["{$group}.{$key}"]]);
                }
            }
        }
    }
}
