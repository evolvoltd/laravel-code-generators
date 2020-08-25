<?php

namespace Evolvo\LaravelCodeGenerators;


use Illuminate\Support\ServiceProvider;

class LaravelCodeGeneratorsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                AutoGenerateModelCode::class,
                AutoGenerateTest::class,
                AutoGenerateTestResponse::class,
                AutoGenerateSingleTest::class,
                AutoGenerateSimpleCrudTest::class,
                AutoGenerateSwaggerDoc::class,
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
