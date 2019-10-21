<?php

namespace Kaylyu\Alipay\F2fpay;

use Laravel\Lumen\Application as LumenApplication;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app instanceof LumenApplication) {
            $this->app->configure('kaylu-alipay');
        }

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/kaylu-alipay.php', 'kaylu-alipay'
        );

        $this->app->singleton('kaylu.alipay.f2fpay', function ($app) {
            return new Application(
                $app['config']['kaylu-alipay']
            );
        });
    }

    /**
     * Register the application's event listeners.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}