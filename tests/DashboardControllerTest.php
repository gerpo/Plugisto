<?php

namespace Plugisto\Tests;

use Gerpo\Plugisto\PlugistoLoader;
use Gerpo\Plugisto\Models\Plugisto;

class DashboardControllerTest extends TestCase
{
    private $loader;

    public function setUp()
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
