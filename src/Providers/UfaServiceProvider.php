<?php

namespace App\Ufa\Providers;

use App\Ufa\Ufa;
use Illuminate\Support\ServiceProvider;

class UfaServiceProvider extends ServiceProvider
{

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        view()->creator('pages.*', 'App\Ufa\Composers\UfaComposer');
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('UfaService', function ($app) {
            return new Ufa();
        });
    }

}