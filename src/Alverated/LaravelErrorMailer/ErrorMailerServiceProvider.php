<?php

namespace Alverated\LaravelErrorMailer;

use Illuminate\Support\ServiceProvider;

class ErrorMailerServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;
    /**
     * Bootstrap the application events.
     *
     * @return void
     */

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/laravel-error-mailer.php' => config_path('laravel-error-mailer.php'),
        ]);

        $this->loadViewsFrom(__DIR__.'/../../views', 'mailer');

        $this->publishes([
            __DIR__.'/../../views/mailer.blade.php' => base_path('resources/views/vendor/mailer.blade.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

    }
}