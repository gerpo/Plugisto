<?php


namespace Plugisto\Tests;


use Gerpo\Plugisto\Exceptions\InvalidVendorPathException;
use Gerpo\Plugisto\Models\Plugisto;
use Gerpo\Plugisto\PlugistoLoader;

class LoaderTest extends TestCase
{
    /** @var PlugistoLoader */
    private $loader;
    private $packageA;
    private $packageB;

    public function setUp()
    {
        parent::setUp();

        $this->artisan('migrate', ['--database' => 'testing']);

        $this->loader = app()->make(PlugistoLoader::class);
        $this->loader->vendorPath = __DIR__ . '/fixtures';

        $this->defineTestData();
    }

    private function defineTestData()
    {
        $this->packageA = [
            'name' => 'package_a_name',
            'description' => '',
            'namespace' => 'vendor_a/package_a',
            'route' => '/vendor_a-package_a'
        ];

        $this->packageB = [
            'name' => 'package_b_name',
            'description' => 'This is a plugisto plugin',
            'namespace' => 'vendor_a/package_b',
            'route' => '/package-mail'
        ];
    }

    /** @test */
    public function new_plugin_gets_detected()
    {
        $this->loader->build();

        $packages = $this->loader->getDetectedPackages();

        $this->assertEquals(
            [
                'vendor_a/package_a' =>
                    [
                        'name' => 'package_a_name'
                    ],
                'vendor_a/package_b' =>
                    [
                        'name' => 'package_b_name',
                        'description' => 'This is a plugisto plugin',
                        'route' => '/package-mail'
                    ]
            ], $packages);
    }

    /** @test */
    public function packages_without_plugisto_info_are_not_detected()
    {
        $this->loader->build();

        $packages = $this->loader->getDetectedPackages();

        $this->assertFalse(in_array('vendor_a/package_c', $packages));
    }

    /** @test */
    public function new_plugin_is_saved_in_database()
    {
        $this->loader->build();

        $this->assertDatabaseHas('plugisto', $this->packageA);
        $this->assertDatabaseHas('plugisto', $this->packageB);
    }

    /** @test */
    public function removed_packages_are_deleted_from_the_database()
    {
        $oldPackage = [
            'name' => 'old_package_name',
            'description' => 'old description',
            'route' => '/testroute',
            'namespace' => 'old/namespace'
        ];

        Plugisto::create($oldPackage);

        $this->assertDatabaseHas('plugisto', $oldPackage);

        $this->loader->build();

        $this->assertDatabaseMissing('plugisto', $oldPackage);
    }

    /** @test */
    public function only_composer_packages_are_removed_from_database()
    {
        $oldComposerPackage = [
            'name' => 'old_composer_name',
            'description' => 'old description',
            'route' => '/testroute1',
            'namespace' => 'oldComposer/namespace'
        ];

        $oldManualPackage = [
            'name' => 'old_package_name',
            'description' => 'old description',
            'route' => '/testroute1',
            'namespace' => 'old/namespace',
            'manually_added' => true
        ];

        Plugisto::create($oldComposerPackage);
        Plugisto::create($oldManualPackage);

        $this->assertDatabaseHas('plugisto',  $oldComposerPackage);
        $this->assertDatabaseHas('plugisto',  $oldManualPackage);

        $this->loader->build();

        $this->assertDatabaseMissing('plugisto', $oldComposerPackage);
        $this->assertDatabaseHas('plugisto',  $oldManualPackage);
    }

    /** @test */
    public function removed_packages_are_not_deleted_from_the_database_when_cleanUp_false()
    {
        $oldPackage = [
            'name' => 'old_package_name',
            'description' => 'old description',
            'route' => '/testroute',
            'namespace' => 'old/namespace'
        ];

        Plugisto::create($oldPackage);

        $this->assertDatabaseHas('plugisto', $oldPackage);

        $this->loader->build(false);

        $this->assertDatabaseHas('plugisto', $oldPackage);
    }

    /** @test */
    public function loader_throws_exception_when_vendor_path_not_valid()
    {
        $loader = new PlugistoLoader();
        $loader->vendorPath = "invalid-Path";

        $this->expectException(InvalidVendorPathException::class);

        $loader->build();
    }
}