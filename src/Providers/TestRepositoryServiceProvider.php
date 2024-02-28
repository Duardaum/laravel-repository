<?php

namespace Duardaum\LaravelRepository\Providers;

use Illuminate\Support\ServiceProvider;

class TestRepositoryServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    public function register()
    {
        $this->app->bind(\Duardaum\LaravelRepository\Contracts\Repositories\MessageRepositoryInterface::class, \Duardaum\LaravelRepository\Repositories\MessageRepository::class);
    }

}