<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

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
        Schema::defaultStringLength(191);
        
        // Ensure view paths exist before Laravel tries to compile them
        $this->ensureViewPathsExist();
    }

    /**
     * Ensure all required view paths and storage directories exist
     */
    protected function ensureViewPathsExist(): void
    {
        // Ensure resources/views exists
        $viewsPath = resource_path('views');
        if (!File::exists($viewsPath)) {
            File::makeDirectory($viewsPath, 0755, true);
        }

        // Ensure storage/framework/views exists
        $compiledViewsPath = storage_path('framework/views');
        if (!File::exists($compiledViewsPath)) {
            File::makeDirectory($compiledViewsPath, 0755, true);
        }

        // Ensure storage/framework/cache/data exists
        $cacheDataPath = storage_path('framework/cache/data');
        if (!File::exists($cacheDataPath)) {
            File::makeDirectory($cacheDataPath, 0755, true);
        }

        // Ensure storage/framework/sessions exists
        $sessionsPath = storage_path('framework/sessions');
        if (!File::exists($sessionsPath)) {
            File::makeDirectory($sessionsPath, 0755, true);
        }

        // Ensure directories are writable
        if (File::exists($compiledViewsPath) && !is_writable($compiledViewsPath)) {
            @chmod($compiledViewsPath, 0755);
        }
        if (File::exists($cacheDataPath) && !is_writable($cacheDataPath)) {
            @chmod($cacheDataPath, 0755);
        }
        if (File::exists($sessionsPath) && !is_writable($sessionsPath)) {
            @chmod($sessionsPath, 0755);
        }
    }
}
