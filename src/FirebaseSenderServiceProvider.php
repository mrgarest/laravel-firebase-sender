<?php

namespace MrGarest\FirebaseSender;

use Illuminate\Support\ServiceProvider;

class FirebaseSenderServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__  . '/../config/firebase-sender.php', 'firebase-sender');
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configurePublishing();
    }

    /**
     * Configure publishing for the package.
     *
     * @return void
     */
    private function configurePublishing()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/firebase-sender.php' => config_path('firebase-sender.php'),
        ], 'firebase-sender-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations')
        ], 'firebase-sender-migrations');
    }
}
