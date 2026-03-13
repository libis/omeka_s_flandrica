<?php declare(strict_types=1);

namespace Sempia\CommonSymlink;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

/**
 * Composer plugin to create symlink for Omeka S module Common.
 *
 * Many old modules depend on Common's root-level files via require_once:
 *   require_once dirname(__DIR__) . '/Common/TraitModule.php';
 *
 * When Common is installed via Composer to composer-addons/modules/Common/,
 * this path doesn't resolve. A symlink modules/Common -> composer-addons/modules/Common
 * ensures backward compatibility.
 */
class CommonSymlinkPlugin implements PluginInterface, EventSubscriberInterface
{
    protected const COMMON_PACKAGE = 'daniel-km/omeka-s-module-common';
    protected const SYMLINK_PATH = 'modules/Common';

    /** @var Composer */
    protected $composer;

    /** @var IOInterface */
    protected $io;

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;

        // Check if Common is already installed and create symlink if needed.
        $this->createSymlinkIfCommonInstalled();
    }

    /**
     * Create symlink if Common module is already installed.
     *
     * This handles the case where the plugin is installed/updated after Common,
     * or when running `composer install` or `composer update`.
     */
    protected function createSymlinkIfCommonInstalled(): void
    {
        $localRepo = $this->composer->getRepositoryManager()->getLocalRepository();
        $package = $localRepo->findPackage(self::COMMON_PACKAGE, '*');

        if ($package) {
            $this->createSymlink($package);
        }
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL => 'onPostPackageInstall',
            PackageEvents::POST_PACKAGE_UPDATE => 'onPostPackageUpdate',
            PackageEvents::PRE_PACKAGE_UNINSTALL => 'onPrePackageUninstall',
        ];
    }

    public function onPostPackageInstall(PackageEvent $event): void
    {
        $package = $event->getOperation()->getPackage();
        if ($package->getName() === self::COMMON_PACKAGE) {
            $this->createSymlink($package);
        }
    }

    public function onPostPackageUpdate(PackageEvent $event): void
    {
        $package = $event->getOperation()->getTargetPackage();
        if ($package->getName() === self::COMMON_PACKAGE) {
            $this->createSymlink($package);
        }
    }

    public function onPrePackageUninstall(PackageEvent $event): void
    {
        $package = $event->getOperation()->getPackage();
        if ($package->getName() === self::COMMON_PACKAGE) {
            $this->removeSymlink();
        }
    }

    protected function createSymlink($package): void
    {
        $localPath = self::SYMLINK_PATH;

        // Only create symlink when running from Omeka S root (not from module directory).
        if (!is_dir('application') || !is_file('bootstrap.php')) {
            return;
        }

        // Don't create symlink if a real directory exists (local override).
        if (is_dir($localPath) && !is_link($localPath)) {
            $this->io->write(sprintf(
                '<info>%s exists as directory, skipping symlink creation.</info>',
                $localPath
            ));
            return;
        }

        $installPath = $this->composer->getInstallationManager()->getInstallPath($package);
        $relativePath = $this->computeRelativePath($localPath, $installPath);

        // Update existing symlink if target changed.
        if (is_link($localPath)) {
            $currentTarget = readlink($localPath);
            if ($currentTarget === $relativePath) {
                return;
            }
            unlink($localPath);
        }

        // Ensure modules/ directory exists.
        $modulesDir = dirname($localPath);
        if (!is_dir($modulesDir)) {
            mkdir($modulesDir, 0755, true);
        }

        // Create symlink.
        if (@symlink($relativePath, $localPath)) {
            $this->io->write(sprintf(
                '<info>Created symlink %s -> %s (backward compatibility)</info>',
                $localPath,
                $relativePath
            ));
        } else {
            $this->io->writeError(sprintf(
                '<warning>Could not create symlink %s -> %s</warning>',
                $localPath,
                $relativePath
            ));
        }
    }

    protected function removeSymlink(): void
    {
        $localPath = self::SYMLINK_PATH;

        if (is_link($localPath)) {
            if (@unlink($localPath)) {
                $this->io->write(sprintf('<info>Removed symlink %s</info>', $localPath));
            }
        }
    }

    /**
     * Compute relative path from symlink location to target.
     *
     * @param string $from Symlink path (e.g., "modules/Common")
     * @param string $to   Target path (e.g., "composer-addons/modules/Common")
     * @return string Relative path (e.g., "../composer-addons/modules/Common")
     */
    protected function computeRelativePath(string $from, string $to): string
    {
        $fromDir = dirname($from);
        $depth = $fromDir === '.' ? 0 : count(explode('/', $fromDir));
        return str_repeat('../', $depth) . $to;
    }
}
