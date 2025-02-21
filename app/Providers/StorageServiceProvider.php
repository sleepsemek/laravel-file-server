<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class StorageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        view()->composer('partials.footer', function ($view) {
            $storagePath = storage_path('app');
            $freeSpace = round(disk_free_space($storagePath) / 1024 / 1024 / 1024, 2) . 'GB';
            $totalSpace = round((disk_total_space($storagePath) - disk_free_space($storagePath)) / 1024 / 1024 / 1024, 2) . 'GB';

            $view->with([
                'freeSpace' => $freeSpace,
                'totalSpace' => $totalSpace,
            ]);
        });
    }
}
