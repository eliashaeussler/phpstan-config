<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/phpstan-config".
 *
 * Copyright (C) 2023-2026 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\PHPStanConfig\Resource\Local;

use EliasHaeussler\PHPStanConfig\Enums;
use EliasHaeussler\PHPStanConfig\Exception;
use EliasHaeussler\PHPStanConfig\Resource;
use Symfony\Component\Filesystem;

use function is_array;
use function is_string;
use function str_contains;

/**
 * ComposerJson.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @phpstan-type ComposerManifest array{
 *      require?: array<string, string>,
 *      suggest?: array<string, string>,
 *      conflict?: array<string, string>,
 *      config?: array<string, mixed>,
 * }
 * @phpstan-type InstalledJson array{
 *      packages?: list<array{
 *          name?: string,
 *          type?: string,
 *          extra?: array{'typo3/cms'?: array{'extension-key': mixed}},
 *      }>,
 *  }
 */
final class ComposerJson
{
    public readonly JsonFile $manifest;
    private ?JsonFile $installedJson = null;

    public function __construct(
        private readonly string $currentWorkingDirectory,
        private readonly Resource\Remote\Packagist $packagist = new Resource\Remote\Packagist(),
    ) {
        $this->manifest = new JsonFile(
            Filesystem\Path::join($this->currentWorkingDirectory, 'composer.json'),
        );
    }

    /**
     * @return list<Resource\ComposerPackage>
     */
    public function extractPackages(Enums\PackageRelation $relation): array
    {
        /** @var ComposerManifest $composerJson */
        $composerJson = $this->manifest->parse() ?? [];
        $vendorDir = $composerJson['config']['vendor-dir'] ?? 'vendor';
        $relationKey = $relation->forComposerJson();

        if (!is_string($vendorDir) || !is_array($composerJson[$relationKey] ?? null)) {
            return [];
        }

        return $this->fetchPackagesMetadata($composerJson[$relationKey], $vendorDir, $relation);
    }

    /**
     * @param array<string, string> $relatedPackages
     *
     * @return list<Resource\ComposerPackage>
     */
    private function fetchPackagesMetadata(
        array $relatedPackages,
        string $vendorDir,
        Enums\PackageRelation $relation,
    ): array {
        /** @var InstalledJson $installedJson */
        $installedJson = $this->getInstalledJson($vendorDir)->parse() ?? [];
        $installedPackages = $installedJson['packages'] ?? null;
        $result = [];

        if (!is_array($installedPackages)) {
            $installedPackages = [];
        }

        // Add packages from installed.json file
        foreach ($installedPackages as $package) {
            $name = $package['name'] ?? null;

            if (!is_string($name) || !isset($relatedPackages[$name])) {
                continue;
            }

            try {
                $result[] = Resource\ComposerPackage::fromDeclaration($package, $relatedPackages[$name], $relation);

                unset($relatedPackages[$name]);
            } catch (Exception\ComposerPackageDeclarationIsInvalid) {
                // Use fallback handling on failure
            }
        }

        // Add remaining packages from Packagist API
        foreach ($relatedPackages as $name => $constraint) {
            // Skip platform and virtual packages
            if (!str_contains($name, '/')) {
                continue;
            }

            $versions = $this->packagist->fetchPackageVersions($name);

            if (null === $versions || [] === $versions) {
                continue;
            }

            try {
                $package = Resource\ComposerPackage::fromPackageVersions($versions, $constraint, $relation);
            } catch (Exception\ComposerPackageDeclarationIsInvalid) {
                // Use fallback handling on failure
                $package = null;
            }

            if (null !== $package) {
                $result[] = $package;

                unset($relatedPackages[$name]);
            }
        }

        // Add remaining packages without additional metadata
        foreach ($relatedPackages as $name => $constraint) {
            $result[] = new Resource\ComposerPackage($name, $constraint, $relation);
        }

        return $result;
    }

    private function getInstalledJson(string $vendorDir): JsonFile
    {
        if (null !== $this->installedJson) {
            return $this->installedJson;
        }

        $installedJsonFile = Filesystem\Path::join(
            $this->currentWorkingDirectory,
            $vendorDir,
            'composer/installed.json',
        );

        return $this->installedJson = new JsonFile($installedJsonFile);
    }
}
