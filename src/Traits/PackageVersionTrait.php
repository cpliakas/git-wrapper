<?php declare(strict_types=1);

namespace GitWrapper\Traits;

use Composer\Semver\Comparator;
use GitWrapper\GitException;

trait PackageVersionTrait
{
    /**
     * @var bool
     */
    public $legacyMode = false;

    /**
     * Set the $legacyMode flag
     *
     * @param  string $package
     * @param  string $changePoint
     */
    public function setLegacyModeFlag(string $package, string $changePoint): void
    {
        $installed = $this->findPackageVersion('symfony/event-dispatcher');
        $this->legacyMode = Comparator::lessThan($installed, $changePoint);
    }

    /**
     * Get the installed version of a package
     *
     * @param  string $name
     * @return string
     */
    public function findPackageVersion(string $name): string
    {
        $installed = file_get_contents(__DIR__ . '/../../vendor/composer/installed.json') ?: '[]';
        $packages = json_decode($installed);

        $key = array_search($name, array_column($packages, 'name'), true);

        if (! $key) {
            throw new GitException('Unable to determine the package version for ' . $name);
        }

        return $packages[$key]->version;
    }
}
