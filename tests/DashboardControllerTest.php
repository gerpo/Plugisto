<?php

namespace Plugisto\Tests;

use Gerpo\Plugisto\Models\Plugisto;
use Gerpo\Plugisto\PlugistoLoader;

class DashboardControllerTest extends TestCase
{
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
    public function dashboard_shows_all_packages()
    {
        $this->get('/dashboard')
            ->assertViewHasAll(['packages' => Plugisto::all()]);
    }
}
