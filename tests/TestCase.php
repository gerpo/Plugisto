<?php

namespace Plugisto\Tests;



use Plugisto\Tests\helpers\TestCommand;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['Illuminate\Contracts\Console\Kernel']->registerCommand(new TestCommand());
    }

    protected function getPackageProviders($app): array
    {
        return ['Gerpo\Plugisto\PlugistoServiceProvider'];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
