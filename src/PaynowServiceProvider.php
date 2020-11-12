<?php

namespace Chriswest101\Paynow;

use Illuminate\Support\ServiceProvider;

class PaynowServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('paynow.php'),
            ], 'config');

            // Publishing assets.
            $this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/paynow'),
            ], 'public');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'paynow');

        // Register the main class to use with the facade
        $this->app->singleton('paynow', function () {
            return new Paynow;
        });
    }
}
