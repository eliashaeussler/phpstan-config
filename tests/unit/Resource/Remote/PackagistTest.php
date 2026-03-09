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

namespace EliasHaeussler\PHPStanConfig\Tests\Resource\Remote;

use EliasHaeussler\PHPStanConfig as Src;
use PHPUnit\Framework;

use function json_encode;

/**
 * PackagistTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Resource\Remote\Packagist::class)]
final class PackagistTest extends Framework\TestCase
{
    private Src\Resource\Remote\Packagist $subject;

    private ?string $nextResponse = null;

    public function setUp(): void
    {
        $this->subject = new Src\Resource\Remote\Packagist(fn () => $this->nextResponse);
    }

    #[Framework\Attributes\Test]
    public function constructorUsesDefaultClientIfNoCustomClientIsPassed(): void
    {
        $subject = new Src\Resource\Remote\Packagist();

        self::assertNull($subject->fetchPackageVersions('foo/baz'));
        self::assertIsArray($subject->fetchPackageVersions('eliashaeussler/phpstan-config'));
    }

    #[Framework\Attributes\Test]
    public function fetchPackageVersionsReturnsNullOnFailedRequest(): void
    {
        self::assertNull($this->subject->fetchPackageVersions('foo'));
    }

    #[Framework\Attributes\Test]
    public function fetchPackageVersionsReturnsNullOnInvalidResponse(): void
    {
        $this->nextResponse = 'invalid';

        self::assertNull($this->subject->fetchPackageVersions('foo'));
    }

    #[Framework\Attributes\Test]
    public function fetchPackageVersionsReturnsNullOnUnsupportedResponse(): void
    {
        $this->nextResponse = '{"foo": "bar"}';

        self::assertNull($this->subject->fetchPackageVersions('foo'));
    }

    #[Framework\Attributes\Test]
    public function fetchPackageVersionsReturnsFetchedPackageVersions(): void
    {
        $expected = [
            [
                'name' => 'foo/baz',
                'version' => '1.1.0',
            ],
            [
                'name' => 'foo/baz',
                'version' => '1.0.0',
            ],
            [
                'name' => 'foo/baz',
                'version' => '0.1.0',
            ],
        ];

        $this->nextResponse = json_encode([
            'packages' => [
                'foo/baz' => $expected,
            ],
        ], JSON_THROW_ON_ERROR);

        self::assertSame($expected, $this->subject->fetchPackageVersions('foo/baz'));
    }

    #[Framework\Attributes\Test]
    public function fetchPackageCachesSuccessfulResponse(): void
    {
        $expected = [
            [
                'name' => 'foo/baz',
                'version' => '1.1.0',
            ],
            [
                'name' => 'foo/baz',
                'version' => '1.0.0',
            ],
            [
                'name' => 'foo/baz',
                'version' => '0.1.0',
            ],
        ];

        $this->nextResponse = json_encode([
            'packages' => [
                'foo/baz' => $expected,
            ],
        ], JSON_THROW_ON_ERROR);

        self::assertSame($expected, $this->subject->fetchPackageVersions('foo/baz'));

        $this->nextResponse = null;

        self::assertSame($expected, $this->subject->fetchPackageVersions('foo/baz'));
    }
}
