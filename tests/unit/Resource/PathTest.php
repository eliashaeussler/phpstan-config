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

namespace EliasHaeussler\PHPStanConfig\Tests\Resource;

use EliasHaeussler\PHPStanConfig as Src;
use PHPUnit\Framework;

/**
 * PathTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Resource\Path::class)]
final class PathTest extends Framework\TestCase
{
    private Src\Resource\Path $subject;

    public function setUp(): void
    {
        $this->subject = new Src\Resource\Path('/my-project');
    }

    #[Framework\Attributes\Test]
    public function resolveReturnsGivenAbsolutePath(): void
    {
        self::assertSame('/foo', $this->subject->resolve('/foo'));
    }

    #[Framework\Attributes\Test]
    public function resolveReturnsGivenPharPath(): void
    {
        self::assertSame('/foo', $this->subject->resolve('/foo'));
    }

    #[Framework\Attributes\Test]
    public function resolveReturnsResolvedRelativePath(): void
    {
        self::assertSame('/my-project/foo', $this->subject->resolve('foo'));
    }
}
