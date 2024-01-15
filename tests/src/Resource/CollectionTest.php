<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/phpstan-config".
 *
 * Copyright (C) 2023-2024 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\PHPStanConfig\Tests\Resource;

use EliasHaeussler\PHPStanConfig as Src;
use PHPUnit\Framework;

/**
 * CollectionTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CollectionTest extends Framework\TestCase
{
    private Src\Resource\Collection $subject;

    protected function setUp(): void
    {
        $this->subject = Src\Resource\Collection::create();
    }

    #[Framework\Attributes\Test]
    public function createReturnsEmptyCollection(): void
    {
        $actual = Src\Resource\Collection::create();

        self::assertSame([], $actual->toArray());
    }

    #[Framework\Attributes\Test]
    public function createHandlesCustomPathDelimiters(): void
    {
        $actual = Src\Resource\Collection::create('.');

        $actual->set('foo.foo', 'baz');
        $actual->set('foo/foo', 'baz');

        self::assertSame(
            [
                'foo' => [
                    'foo' => 'baz',
                ],
                'foo/foo' => 'baz',
            ],
            $actual->toArray(),
        );
    }

    #[Framework\Attributes\Test]
    public function fromArrayReturnsCollectionFromGivenArray(): void
    {
        $array = [
            'foo' => 'baz',
        ];

        $actual = Src\Resource\Collection::fromArray($array);

        self::assertSame($array, $actual->toArray());
    }

    #[Framework\Attributes\Test]
    public function fromArrayHandlesCustomPathDelimiters(): void
    {
        $actual = Src\Resource\Collection::fromArray(['baz' => 'baz'], '.');

        $actual->set('foo.foo', 'baz');
        $actual->set('foo/foo', 'baz');

        self::assertSame(
            [
                'baz' => 'baz',
                'foo' => [
                    'foo' => 'baz',
                ],
                'foo/foo' => 'baz',
            ],
            $actual->toArray(),
        );
    }

    #[Framework\Attributes\Test]
    public function setReplacesCollectionValueOfGivenKey(): void
    {
        $this->subject->set('foo', 'baz');

        self::assertSame(['foo' => 'baz'], $this->subject->toArray());

        $this->subject->set('foo', 'foo');

        self::assertSame(['foo' => 'foo'], $this->subject->toArray());
    }

    #[Framework\Attributes\Test]
    public function setReplacesCollectionValueOfGivenPath(): void
    {
        $this->subject->set('foo/baz', 'baz');

        self::assertSame(
            [
                'foo' => [
                    'baz' => 'baz',
                ],
            ],
            $this->subject->toArray(),
        );

        $this->subject->set('foo', 'baz');

        self::assertSame(['foo' => 'baz'], $this->subject->toArray());
    }

    #[Framework\Attributes\Test]
    public function addAddsValueToListWithGivenKeyInCollection(): void
    {
        $this->subject->add('foo', 'baz');
        $this->subject->add('foo', 'dummy');

        self::assertSame(
            [
                'foo' => [
                    'baz',
                    'dummy',
                ],
            ],
            $this->subject->toArray(),
        );
    }

    #[Framework\Attributes\Test]
    public function addAddsValueToListWithGivenPathInCollection(): void
    {
        $this->subject->add('foo/baz', 'baz');
        $this->subject->add('foo/baz', 'dummy');

        self::assertSame(
            [
                'foo' => [
                    'baz' => [
                        'baz',
                        'dummy',
                    ],
                ],
            ],
            $this->subject->toArray(),
        );
    }

    #[Framework\Attributes\Test]
    public function addReplacesNonArrayValueInCollection(): void
    {
        $this->subject->set('foo', 'baz');
        $this->subject->add('foo', 'baz');

        self::assertSame(
            [
                'foo' => [
                    'baz',
                ],
            ],
            $this->subject->toArray(),
        );
    }

    #[Framework\Attributes\Test]
    public function mergeMergesTwoCollections(): void
    {
        $other = Src\Resource\Collection::fromArray([
            'foo' => [
                'dummy',
            ],
        ]);

        $this->subject->add('foo', 'baz');

        $actual = $this->subject->merge($other);

        self::assertSame(
            [
                'foo' => [
                    'baz',
                    'dummy',
                ],
            ],
            $actual->toArray(),
        );
    }
}
