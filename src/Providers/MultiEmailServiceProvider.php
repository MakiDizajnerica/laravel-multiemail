<?php

namespace MakiDizajnerica\MultiEmail\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use MakiDizajnerica\MultiEmail\EloquentEmailProvider;

class MultiEmailServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/multiemail.php', 'multiemail'
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->offerPublishing();
        $this->registerEmailProvider();
    }

    /**
     * Offer config publishing.
     *
     * @return void
     */
    protected function offerPublishing()
    {
        if ($this->app->runningInConsole()) {    
            $this->publishes([
                __DIR__ . '/../../config/multiemail.php' => config_path('multiemail.php')
            ], 'multiemail-config');

            $this->publishes([
                __DIR__.'/../../database/migrations' => database_path('migrations')
            ], 'multiemail-migrations');
        }
    }

    /**
     * Register custom user provider.
     *
     * @return void
     */
    protected function registerEmailProvider()
    {
        Auth::provider('eloquent.email', function ($app, array $config) {
            return new EloquentEmailProvider(
                $app['hash'],
                $config['models']
            );
        });
    }
}
