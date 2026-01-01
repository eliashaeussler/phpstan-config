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

namespace EliasHaeussler\PHPStanConfig\Tests\Set;

use EliasHaeussler\PHPStanConfig as Src;
use PHPUnit\Framework;

/**
 * TYPO3SetTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class TYPO3SetTest extends Framework\TestCase
{
    private Src\Set\TYPO3Set $subject;

    protected function setUp(): void
    {
        $this->subject = Src\Set\TYPO3Set::create();
    }

    #[Framework\Attributes\Test]
    public function createReturnsEmptySet(): void
    {
        $actual = Src\Set\SymfonySet::create();

        self::assertSame([], $actual->getParameters()->toArray());
    }

    #[Framework\Attributes\Test]
    public function withCustomAspectConfiguresCustomAspect(): void
    {
        $this->subject->withCustomAspect('foo', self::class);

        self::assertSame(
            [
                'typo3' => [
                    'contextApiGetAspectMapping' => [
                        'foo' => self::class,
                    ],
                ],
            ],
            $this->subject->getParameters()->toArray(),
        );
    }

    #[Framework\Attributes\Test]
    public function withCustomRequestAttributeConfiguresCustomRequestAttribute(): void
    {
        $this->subject->withCustomRequestAttribute('foo', self::class);

        self::assertSame(
            [
                'typo3' => [
                    'requestGetAttributeMapping' => [
                        'foo' => self::class,
                    ],
                ],
            ],
            $this->subject->getParameters()->toArray(),
        );
    }

    #[Framework\Attributes\Test]
    public function withCustomSiteAttributeConfiguresCustomSiteAttribute(): void
    {
        $this->subject->withCustomSiteAttribute('foo', self::class);

        self::assertSame(
            [
                'typo3' => [
                    'siteGetAttributeMapping' => [
                        'foo' => self::class,
                    ],
                ],
            ],
            $this->subject->getParameters()->toArray(),
        );
    }
}
