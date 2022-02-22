<?php


namespace Roboticsexpert\BalanceManager;

use Illuminate\Support\ServiceProvider;
use Roboticsexpert\BalanceManager\Services\BalanceManager;

class BalanceManagerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        $this->publishes([
//            __DIR__ . '/Migrations', database_path('/'),
            __DIR__ . '/Configs', config_path('/')
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/Configs/balance.php', config_path('balance.php')
        );
        $this->app->singleton(BalanceManager::class, function () {
            return new BalanceManager(config('balance.currencies'));
        });

    }
}
