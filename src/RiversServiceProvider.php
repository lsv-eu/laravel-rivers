<?php

namespace LsvEu\Rivers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use LsvEu\Rivers\Livewire\Synthesizers\MapSynthesizer;

class RiversServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
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
            ], 'rivers-config');

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
            $this->bootSchedule();
        }

        // Register event listeners
        Event::listen(Events\RiverPausedEvent::class, Listeners\PauseRiverTimedBridges::class);
        Event::listen(Events\RiverResumedEvent::class, Listeners\ResumeRiverTimedBridges::class);

        if (class_exists('\\Livewire\\Livewire')) {
            /** @noinspection PhpUndefinedNamespaceInspection */
            /** @noinspection PhpUndefinedClassInspection */
            \Livewire\Livewire::propertySynthesizer(MapSynthesizer::class);
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'rivers');

        // Register the main class to use with the facade
        $this->app->singleton('rivers', function () {
            return new Rivers;
        });
    }

    public function bootSchedule(): void
    {
        if (config('rivers.timed_bridges.enabled')) {
            if (($seconds = (int) config('rivers.timed_bridges.seconds')) > 0 && $seconds < 60) {
                if (60 % $seconds !== 0) {
                    throw new InvalidArgumentException("The seconds [$seconds] are not evenly divisible by 60.");
                }
                $schedule = app(Schedule::class)->command(config('rivers.timed_bridges.command'), ['--dispatch']);
                if ($seconds > 0) {
                    $schedule->repeatSeconds = $seconds;
                }
                $schedule->everyMinute();
            } elseif (($expression = config('rivers.timed_bridges.cron')) !== null) {
                $schedule = app(Schedule::class)->command(config('rivers.timed_bridges.command'), ['--dispatch']);
                $schedule->cron($expression);
            }
        }
    }
}
