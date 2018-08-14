<?php

namespace Gerpo\Plugisto\Commands;

use Illuminate\Console\Command;
use Gerpo\Plugisto\PlugistoLoader;
use Gerpo\Plugisto\Exceptions\InvalidVendorPathException;

class BuildPackagesCommand extends Command
{
    protected $signature = 'plugisto:build 
                            {--no-cleanup=false : Removed packages will not be removes}';

    protected $description = 'Detects all plugisto packages that are loaded with composer.';

    private $loader;

    public function __construct(PlugistoLoader $loader)
    {
        parent::__construct();

        $this->loader = $loader;
    }

    public function handle()
    {
        try {
            $this->loader->build(! $this->option('no-cleanup'));

            if (empty($packages = $this->loader->getDetectedPackages())) {
                $this->info('No plugisto packages found.');
            } else {
                $this->line('Detected plugisto packages:');

                $headers = ['Name', 'Description', 'Route'];
                $this->table($headers, $this->loader->getDetectedPackages());
            }
        } catch (InvalidVendorPathException $e) {
            $this->error('Vendor path is invalid: '.$e->getMessage());
        }
    }
}
