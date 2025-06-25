<?php

namespace LsvEu\Rivers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\ServiceProvider;

class RiversServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'rivers');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'rivers');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Registering package commands.
        $this->commands([
            Console\Commands\CheckTimedBridges::class,
        ]);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('rivers.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/rivers'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/rivers'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/rivers'),
            ], 'lang');*/

            // Registering schedules
            if (config('rivers.use_timed_bridges')) {
                Schedule::command('rivers:check-timed-bridges')->everyMinute();
            }
        }

        // Register event listeners
        Event::listen(Events\RiverPausedEvent::class, Listeners\PauseRiverTimedBridges::class);
        Event::listen(Events\RiverResumedEvent::class, Listeners\ResumeRiverTimedBridges::class);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'rivers');

        // Register the main class to use with the facade
        $this->app->singleton('rivers', function () {
            return new Rivers;
        });
    }
}
