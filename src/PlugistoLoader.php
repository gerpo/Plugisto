<?php

namespace Gerpo\Plugisto;

use Illuminate\Support\Collection;
use Gerpo\Plugisto\Models\Plugisto;
use Gerpo\Plugisto\Scopes\ActiveScope;
use Illuminate\Support\Facades\Artisan;
use Gerpo\Plugisto\Exceptions\InvalidVendorPathException;

class PlugistoLoader
{
    public $vendorPath;
    /** @var Collection */
    private $detectedPackages;
    private $failedPackages = [];
    private $installedPackages;

    public function __construct()
    {
        $this->vendorPath = base_path('vendor');
        $this->installedPackages = collect();
    }

    /**
     * @param bool $cleanUp
     * @throws InvalidVendorPathException
     */
    public function build($cleanUp = true): void
    {
        $this->detectPackages()
            ->storePackages($this->detectedPackages);

        if ($cleanUp) {
            $this->cleanUpRemovedPackages();
        }

        if (config('plugisto.auto_install', true)) {
            $this->installPackages();
        }
    }

    private function storePackages($packages): void
    {
        $packages->each(function ($package, $namespace) {
            $this->storePackage($this->transformRawData($package, $namespace));
        });
    }

    private function storePackage($package): void
    {
        $validator = validator($package, [
            'name' => 'required|string',
            'description' => 'nullable',
            'route' => 'string',
            'needed_permission' => 'string',
            'namespace' => 'required|unique:plugisto',
        ]);

        if ($validator->fails()) {
            $this->failedPackages[] = $package;

            return;
        }

        $package['is_active'] = false;

        $this->installedPackages->push(Plugisto::create($package));
    }

    private function transformRawData($package, $namespace): array
    {
        return [
            'name' => $package['name'],
            'description' => $package['description'] ?? '',
            'route' => $package['route'] ?? '/'.str_replace('/', '-', $namespace),
            'needed_permission' => $package['needed_permission'] ?? '',
            'namespace' => $namespace,
        ];
    }

    /**
     * @throws InvalidVendorPathException
     */
    private function detectPackages()
    {
        if (! file_exists($path = $this->vendorPath.'/composer/installed.json')) {
            throw new InvalidVendorPathException($this->vendorPath);
        }

        $this->detectedPackages = collect(json_decode(file_get_contents($path), true))
            ->mapWithKeys(function ($package) {
                return [$package['name'] => $package['extra']['plugisto'] ?? []];
            })->filter();

        return $this;
    }

    private function cleanUpRemovedPackages(): void
    {
        Plugisto::withoutGlobalScope(ActiveScope::class)
            ->where('manually_added', false)
            ->whereNotIn('namespace', $this->detectedPackages->keys()->toArray())
            ->delete();
    }

    private function installPackages(): void
    {
        $this->newPackages()->each(function ($package) {
            $this->installPackage($package->namespace);
        });
    }

    private function newPackages()
    {
        return $this->installedPackages->filter(function ($package) {
            return $package->wasRecentlyCreated;
        });
    }

    private function installPackage($namespace): void
    {
        if (array_key_exists('install-command', $package = $this->detectedPackages->get($namespace))) {
            Artisan::call($package['install-command']);
        }
    }

    public function getDetectedPackages(): array
    {
        return $this->detectedPackages->toArray() ?? [];
    }
}
