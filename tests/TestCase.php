<?php

namespace Duardaum\LaravelRepository\Tests;

class TestCase extends \Orchestra\Testbench\TestCase
{

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:'
        ]);

    }

    public function getPackageProviders($app)
    {
        return [
            \Duardaum\LaravelRepository\Providers\LaravelRepositoryServiceProvider::class,
        ];
    }

}