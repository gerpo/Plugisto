<?php


namespace Gerpo\Plugisto;

use Gerpo\Plugisto\Commands\BuildPackagesCommand;
use Gerpo\Plugisto\Commands\ListPackagesCommand;
use Illuminate\Support\ServiceProvider;

class PlugistoServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/plugisto.php' => config_path('plugisto.php'),
        ]);

        $this->loadMigrationsFrom(__DIR__ . '/migrations');

        $this->loadViewsFrom(__DIR__ . '/views', 'plugisto');

        $this->commands([
            BuildPackagesCommand::class,
            ListPackagesCommand::class
        ]);

        if (config('plugisto.load_routes', true)) {

            $this->loadRoutesFrom(__DIR__ . '/routes/routes.php');

        }
    }

    public function register()
    {
        $this->app->singleton(PlugistoLoader::class, PlugistoLoader::class);
    }
}