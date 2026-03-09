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

namespace EliasHaeussler\PHPStanConfig\Tests\Resource\Local;

use EliasHaeussler\PHPStanConfig as Src;
use PHPUnit\Framework;

use function dirname;
use function json_encode;

/**
 * ComposerJsonTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Resource\Local\ComposerJson::class)]
final class ComposerJsonTest extends Framework\TestCase
{
    private string $rootPath;
    private Src\Resource\Local\ComposerJson $subject;

    private ?string $nextResponse = null;

    public function setUp(): void
    {
        $this->rootPath = dirname(__DIR__, 2).'/Fixtures/Files';
        $this->subject = new Src\Resource\Local\ComposerJson(
            $this->rootPath,
            new Src\Resource\Remote\Packagist(fn () => $this->nextResponse),
        );
    }

    #[Framework\Attributes\Test]
    public function constructorResolvesManifestFile(): void
    {
        self::assertSame($this->rootPath.'/composer.json', $this->subject->manifest->path);
    }

    #[Framework\Attributes\Test]
    public function extractPackagesReturnsEmptyArrayIfNoPackagesAreConfiguredForGivenRelationKey(): void
    {
        self::assertSame([], $this->subject->extractPackages(Src\Enums\PackageRelation::Conflict));
    }

    #[Framework\Attributes\Test]
    public function extractPackagesReturnsResolvedComposerPackages(): void
    {
        $this->nextResponse = json_encode([
            'packages' => [
                'foo/boo' => [
                    [
                        'name' => 'foo/boo',
                        'version' => '1.1.0',
                        'type' => 'typo3-cms-extension',
                        'extra' => [
                            'typo3/cms' => [
                                'extension-key' => 'boo',
                            ],
                        ],
                    ],
                    [
                        'name' => 'foo/boo',
                        'version' => '1.0.0',
                        'type' => 'typo3-cms-extension',
                    ],
                    [
                        'name' => 'foo/boo',
                        'version' => '0.1.0',
                        'type' => 'typo3-cms-extension',
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $expected = [
            new Src\Resource\ComposerPackage(
                'foo/baz',
                '^1.0',
                Src\Enums\PackageRelation::Requirement,
                'library',
            ),
            new Src\Resource\ComposerPackage(
                'foo/boo',
                '^1.1',
                Src\Enums\PackageRelation::Requirement,
                'typo3-cms-extension',
                'boo',
            ),
            new Src\Resource\ComposerPackage(
                'php',
                '^8.2',
                Src\Enums\PackageRelation::Requirement,
            ),
        ];

        self::assertEquals($expected, $this->subject->extractPackages(Src\Enums\PackageRelation::Requirement));
    }
}
