<?php

namespace Plugisto\Tests;

use Gerpo\plugisto\tests\helpers\TestCommand;
use Gerpo\plugisto\tests\helpers\TestServiceProvider;
use Mockery;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * @var Mockery\MockInterface|Gerpo\plugisto\tests\helpers\TestCommand[handle]
     */
    private $testCommand;

    protected function setUp()
    {
        parent::setUp();

        $this->app['Illuminate\Contracts\Console\Kernel']->registerCommand(new TestCommand());
    }

    protected function getPackageProviders($app)
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
