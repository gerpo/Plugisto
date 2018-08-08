<?php


namespace Gerpo\Plugisto;


use Gerpo\Plugisto\Exceptions\InvalidVendorPathException;
use Gerpo\Plugisto\Models\Plugisto;
use Gerpo\Plugisto\Scopes\ActiveScope;
use Illuminate\Support\Collection;

class PlugistoLoader
{
    public $vendorPath;
    /** @var Collection */
    private $detectedPackages;
    private $failedPackages = [];

    public function __construct()
    {
        $this->vendorPath = base_path("vendor");
    }

    /**
     * @param bool $cleanUp
     * @throws InvalidVendorPathException
     */
    public function build($cleanUp = true)
    {
        $this->detectPackages()
            ->storePackages($this->detectedPackages);

        if ($cleanUp) {
            $this->cleanUpRemovedPackages();
        }
    }

    private function storePackages($packages)
    {
        $packages->each(function ($package, $namespace) {
            $this->storePackage($this->transformRawData($package, $namespace));
        });
    }

    private function storePackage($package)
    {
        $validator = validator($package, [
            'name' => 'required|string',
            'description' => 'nullable',
            'route' => 'string',
            'namespace' => 'required|unique:plugisto'
        ]);

        if ($validator->fails()) {
            $this->failedPackages[] = $package;
            return;
        }
        
        $package['is_active'] = false;

        Plugisto::create($package);
    }

    private function transformRawData($package, $namespace)
    {
        return [
            'name' => $package['name'],
            'description' => $package['description'] ?? '',
            'route' => $package['route'] ?? '/' . str_replace('/', '-', $namespace),
            'namespace' => $namespace
        ];
    }

    /**
     * @throws InvalidVendorPathException
     */
    private function detectPackages()
    {
        if (!file_exists($path = $this->vendorPath . '/composer/installed.json')) {
            throw new InvalidVendorPathException($this->vendorPath);
        }

        $this->detectedPackages = collect(json_decode(file_get_contents($path), true))
            ->mapWithKeys(function ($package) {
                return [$package['name'] => $package['extra']['plugisto'] ?? []];
            })->filter();

        return $this;
    }

    private function cleanUpRemovedPackages()
    {
        Plugisto::withoutGlobalScope(ActiveScope::class)
            ->where('manually_added', false)
            ->whereNotIn('namespace', $this->detectedPackages->keys())
            ->delete();
    }

    public function getDetectedPackages()
    {
        return $this->detectedPackages->toArray() ?? [];
    }

}