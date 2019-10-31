<?php

namespace Plugisto\Tests;

use Mockery;
use Gerpo\Plugisto\PlugistoLoader;
use Gerpo\Plugisto\Models\Plugisto;
use Plugisto\Tests\helpers\TestCommand;
use Gerpo\Plugisto\Exceptions\InvalidVendorPathException;

class LoaderTest extends TestCase
{
    /** @var PlugistoLoader */
    private $loader;
    private $packageA;
    private $packageB;

    /** @test */
    public function new_plugin_gets_detected(): void
    {
        $this->loader->build();

        $packages = $this->loader->getDetectedPackages();

        $this->assertEquals(
            [
                'vendor_a/package_a' => [
                    'name' => 'package_a_name',
                    'install-command' => 'testcommand:install',
                ],
                'vendor_a/package_b' => [
                    'name' => 'package_b_name',
                    'description' => 'This is a plugisto plugin',
                    'route' => '/package-mail',
                    'install-command' => 'testcommand:install',
                ],
            ], $packages);
    }

    /** @test */
    public function packages_without_plugisto_info_are_not_detected(): void
    {
        $this->loader->build();

        $packages = $this->loader->getDetectedPackages();

        $this->assertNotContains('vendor_a/package_c', $packages);
    }

    /** @test */
    public function new_plugin_is_saved_in_database(): void
    {
        $this->loader->build();

        $this->assertDatabaseHas('plugisto', $this->packageA);
        $this->assertDatabaseHas('plugisto', $this->packageB);
    }

    /** @test */
    public function removed_packages_are_deleted_from_the_database(): void
    {
        $oldPackage = [
            'name' => 'old_package_name',
            'description' => 'old description',
            'route' => '/testroute',
            'namespace' => 'old/namespace',
        ];

        Plugisto::create($oldPackage);

        $this->assertDatabaseHas('plugisto', $oldPackage);

        $this->loader->build();

        $this->assertDatabaseMissing('plugisto', $oldPackage);
    }

    /** @test */
    public function only_composer_packages_are_removed_from_database(): void
    {
        $oldComposerPackage = [
            'name' => 'old_composer_name',
            'description' => 'old description',
            'route' => '/testroute1',
            'namespace' => 'oldComposer/namespace',
        ];

        $oldManualPackage = [
            'name' => 'old_package_name',
            'description' => 'old description',
            'route' => '/testroute1',
            'namespace' => 'old/namespace',
            'manually_added' => true,
        ];

        Plugisto::create($oldComposerPackage);
        Plugisto::create($oldManualPackage);

        $this->assertDatabaseHas('plugisto', $oldComposerPackage);
        $this->assertDatabaseHas('plugisto', $oldManualPackage);

        $this->loader->build();

        $this->assertDatabaseMissing('plugisto', $oldComposerPackage);
        $this->assertDatabaseHas('plugisto', $oldManualPackage);
    }

    /** @test */
    public function removed_packages_are_not_deleted_from_the_database_when_cleanUp_false(): void
    {
        $oldPackage = [
            'name' => 'old_package_name',
            'description' => 'old description',
            'route' => '/testroute',
            'namespace' => 'old/namespace',
        ];

        Plugisto::create($oldPackage);

        $this->assertDatabaseHas('plugisto', $oldPackage);

        $this->loader->build(false);

        $this->assertDatabaseHas('plugisto', $oldPackage);
    }

    /** @test */
    public function already_saved_packages_are_unchanged_after_rerun(): void
    {
        // The namespace is the same as in the dummy composer installed.json, only the route is different.
        $oldPackage = [
            'name' => 'package_b_name',
            'description' => 'This is a plugisto plugin',
            'route' => '/old-route',
            'namespace' => 'vendor_a/package_b',
        ];

        Plugisto::create($oldPackage);

        $this->assertDatabaseHas('plugisto', $oldPackage);

        $this->loader->build();

        $this->assertDatabaseHas('plugisto', $oldPackage);
    }

    /** @test */
    public function package_install_command_is_run_if_auto_install_is_true(): void
    {
        $testCommand = Mockery::mock(TestCommand::class.'[handle]');
        $testCommand->shouldReceive('handle')->twice();
        $this->app['Illuminate\Contracts\Console\Kernel']->registerCommand($testCommand);

        $this->loader->build();
    }

    /** @test */
    public function package_install_command_is_not_run_if_auto_install_is_false(): void
    {
        config(['plugisto.auto_install' => false]);
        $testCommand = Mockery::mock(TestCommand::class.'[handle]');
        $testCommand->shouldNotReceive('handle');
        $this->app['Illuminate\Contracts\Console\Kernel']->registerCommand($testCommand);

        $this->loader->build();
    }

    /** @test */
    public function package_install_command_is_not_run_for_old_packages(): void
    {
        Plugisto::create($this->packageA);
        $this->assertDatabaseHas('plugisto', $this->packageA);

        $testCommand = Mockery::mock(TestCommand::class.'[handle]');
        $testCommand->shouldReceive('handle')->once();
        $this->app['Illuminate\Contracts\Console\Kernel']->registerCommand($testCommand);

        $this->loader->build();
    }

    /** @test */
    public function loader_throws_exception_when_vendor_path_not_valid(): void
    {
        $loader = new PlugistoLoader();
        $loader->vendorPath = 'invalid-Path';

        $this->expectException(InvalidVendorPathException::class);

        $loader->build();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--database' => 'testing']);

        $this->loader = app()->make(PlugistoLoader::class);
        $this->loader->vendorPath = __DIR__.'/fixtures';

        $this->defineTestData();
    }

    private function defineTestData(): void
    {
        $this->packageA = [
            'name' => 'package_a_name',
            'description' => '',
            'namespace' => 'vendor_a/package_a',
            'route' => '/vendor_a-package_a',
        ];

        $this->packageB = [
            'name' => 'package_b_name',
            'description' => 'This is a plugisto plugin',
            'namespace' => 'vendor_a/package_b',
            'route' => '/package-mail',
        ];
    }
}
