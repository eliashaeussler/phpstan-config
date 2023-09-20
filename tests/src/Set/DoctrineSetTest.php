<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/phpstan-config".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\PHPStanConfig\Tests\Set;

use EliasHaeussler\PHPStanConfig as Src;
use PHPUnit\Framework;

/**
 * DoctrineSetTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class DoctrineSetTest extends Framework\TestCase
{
    private Src\Set\DoctrineSet $subject;

    protected function setUp(): void
    {
        $this->subject = Src\Set\DoctrineSet::create();
    }

    #[Framework\Attributes\Test]
    public function createReturnsEmptySet(): void
    {
        $actual = Src\Set\DoctrineSet::create();

        self::assertSame([], $actual->getParameters()->toArray());
    }

    #[Framework\Attributes\Test]
    public function withObjectManagerLoaderConfiguresObjectManagerLoader(): void
    {
        $this->subject->withObjectManagerLoader('foo');

        self::assertSame(
            [
                'doctrine' => [
                    'objectManagerLoader' => 'foo',
                ],
            ],
            $this->subject->getParameters()->toArray(),
        );
    }

    #[Framework\Attributes\Test]
    public function withOrmRepositoryClassConfiguresOrmRepositoryClass(): void
    {
        $this->subject->withOrmRepositoryClass(self::class);

        self::assertSame(
            [
                'doctrine' => [
                    'ormRepositoryClass' => self::class,
                ],
            ],
            $this->subject->getParameters()->toArray(),
        );
    }

    #[Framework\Attributes\Test]
    public function withOdmRepositoryClassConfiguresOdmRepositoryClass(): void
    {
        $this->subject->withOdmRepositoryClass(self::class);

        self::assertSame(
            [
                'doctrine' => [
                    'odmRepositoryClass' => self::class,
                ],
            ],
            $this->subject->getParameters()->toArray(),
        );
    }
}
