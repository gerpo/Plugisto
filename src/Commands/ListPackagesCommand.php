<?php

namespace Gerpo\Plugisto\Commands;

use Gerpo\Plugisto\Models\Plugisto;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ListPackagesCommand extends Command
{
    protected $signature = 'plugisto:list ';

    protected $description = 'Lists all plugisto packages that currently installed.';

    public function handle(): void
    {
        $packages = Plugisto::all();

        if ($packages->isEmpty()) {
            $this->info('No plugisto packages installed.');
        } else {
            $this->line('Installed plugisto packages:');

            $headers = ['Name', 'Description', 'Route', 'Status'];
            $this->table($headers, $this->transformArray($packages));
        }
    }

    /**
     * @param Collection $packages
     * @return mixed
     */
    private function transformArray($packages)
    {
        return $packages->mapWithKeys(function ($package) {
            return [
                $package['namespace'] => [
                    'Name' => $package['name'],
                    'Description' => $package['description'],
                    'Route' => $package['route'],
                    'Status' => $package['is_active'] ? 'Active' : 'Inactive',
                ],
            ];
        });
    }
}
