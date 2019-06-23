<?php

namespace Plugisto\Tests;

use Gerpo\Plugisto\PlugistoLoader;
use Gerpo\Plugisto\Models\Plugisto;
use Gerpo\Plugisto\Scopes\ActiveScope;

class ControllerTest extends TestCase
{
    /** @var PlugistoLoader */
    private $loader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--database' => 'testing']);
        $this->artisan('vendor:publish', ['--provider' => 'Gerpo\Plugisto\PlugistoServiceProvider']);

        $this->loader = app()->make(PlugistoLoader::class);
        $this->loader->vendorPath = __DIR__.'/fixtures';

        $this->loader->build();
    }

    /** @test */
    public function all_packages_are_shown()
    {
        $this->get('/plugisto')
            ->assertViewHasAll(['packages' => Plugisto::withoutGlobalScope(ActiveScope::class)->get()]);
    }

    /** @test */
    public function multiple_packages_can_be_activated()
    {
        $this->withExceptionHandling();

        $packages = Plugisto::withoutGlobalScope(ActiveScope::class)->get();

        $packages->each(function ($package) {
            $this->assertFalse($package->is_active);

            $package->is_active = true;
        });

        $this->put('/plugisto', ['data' => $packages->toArray()]);

        $packages->each(function ($package) {
            $this->assertTrue($package->fresh()->is_active);
        });
    }

    /** @test */
    public function multiple_packages_can_be_deactivated()
    {
        $this->withExceptionHandling();

        $packages = Plugisto::withoutGlobalScope(ActiveScope::class)->get();

        $packages->each(function ($package) {
            $package->activate();
            $this->assertTrue($package->fresh()->is_active);

            $package->is_active = false;
        });

        $this->put('/plugisto', ['data' => $packages->toArray()]);

        $packages->each(function ($package) {
            $this->assertFalse($package->fresh()->is_active);
        });
    }

    /** @test */
    public function manually_added_package_can_be_deleted()
    {
        $package = Plugisto::create([
            'name' => 'package',
            'namespace' => 'namespace/manual',
            'route' => '/route',
            'manually_added' => true,
        ])->toArray();

        $this->assertDatabaseHas('plugisto', $package);

        $this->delete('/plugisto/'.$package['id'])
            ->assertSuccessful();

        $this->assertDatabaseMissing('plugisto', $package);
    }

    /** @test */
    public function composer_added_package_cannot_be_deleted()
    {
        $package = Plugisto::create([
            'name' => 'package',
            'namespace' => 'namespace/manual',
            'route' => '/route',
        ])->toArray();

        $this->assertDatabaseHas('plugisto', $package);

        $this->delete('/plugisto/'.$package['id'])
            ->assertSuccessful();

        $this->assertDatabaseHas('plugisto', $package);
    }
}
