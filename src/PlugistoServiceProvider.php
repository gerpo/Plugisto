<?php


namespace Gerpo\Plugisto;

use Gerpo\Plugisto\Commands\BuildPackagesCommand;
use Gerpo\Plugisto\Commands\ListPackagesCommand;
use Illuminate\Support\ServiceProvider;
use Route;

class PlugistoServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/plugisto.php' => config_path('plugisto.php'),
        ]);

        $this->registerRouteMacro();

        $this->exportViews();
        $this->exportMigrations();

        $this->registerCommands();

        if (config('plugisto.auto_load_routes', true)) {

            $this->loadRoutesFrom(__DIR__ . '/routes/routes.php');

        }
    }

    public function registerRouteMacro()
    {
        Route::macro('plugisto', function () {
            Route::get('/plugisto', '\Gerpo\Plugisto\Controllers\PlugistoController@index');
            Route::put('/plugisto', '\Gerpo\Plugisto\Controllers\PlugistoController@update');
            Route::delete('/plugisto/{plugisto}', '\Gerpo\Plugisto\Controllers\PlugistoController@destroy');

            Route::get('/dashboard', '\Gerpo\Plugisto\Controllers\DashboardController@index');
        });
    }

    public function exportViews()
    {
        $this->loadViewsFrom(__DIR__ . '/views', 'plugisto');
    }

    public function exportMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }

    public function registerCommands()
    {
        $this->commands([
            BuildPackagesCommand::class,
            ListPackagesCommand::class
        ]);
    }

    public function register()
    {
        $this->app->singleton(PlugistoLoader::class, PlugistoLoader::class);
    }
}