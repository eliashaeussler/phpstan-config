<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/phpstan-config".
 *
 * Copyright (C) 2023-2025 Elias Häußler <elias@haeussler.dev>
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
 * SymfonySetTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class SymfonySetTest extends Framework\TestCase
{
    private Src\Set\SymfonySet $subject;

    protected function setUp(): void
    {
        $this->subject = Src\Set\SymfonySet::create();
        $this->subject->setProjectPath(new Src\Resource\Path('/my-project'));
    }

    #[Framework\Attributes\Test]
    public function createReturnsEmptySet(): void
    {
        $actual = Src\Set\SymfonySet::create();

        self::assertSame([], $actual->getParameters()->toArray());
    }

    #[Framework\Attributes\Test]
    public function withConsoleApplicationLoaderConfiguresConsoleApplicationLoader(): void
    {
        $this->subject->withConsoleApplicationLoader('foo');

        self::assertSame(
            [
                'symfony' => [
                    'consoleApplicationLoader' => '/my-project/foo',
                ],
            ],
            $this->subject->getParameters()->toArray(),
        );
    }

    #[Framework\Attributes\Test]
    public function withContainerXmlPathConfiguresContainerXmlPath(): void
    {
        $this->subject->withContainerXmlPath('foo');

        self::assertSame(
            [
                'symfony' => [
                    'containerXmlPath' => '/my-project/foo',
                ],
            ],
            $this->subject->getParameters()->toArray(),
        );
    }

    #[Framework\Attributes\Test]
    public function disableConstantHassersDisablesConstantHassers(): void
    {
        $this->subject->disableConstantHassers();

        self::assertSame(
            [
                'symfony' => [
                    'constantHassers' => false,
                ],
            ],
            $this->subject->getParameters()->toArray(),
        );
    }
}
