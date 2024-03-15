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

namespace EliasHaeussler\PHPStanConfig\Tests\Fixtures;

use EliasHaeussler\PHPStanConfig\Resource;
use EliasHaeussler\PHPStanConfig\Set;

/**
 * DummySet.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @internal
 */
final class DummySet implements Set\ParameterizableSet
{
    /**
     * @param array<non-empty-string, mixed> $parameters
     */
    public function __construct(
        public array $parameters = ['foo' => 'baz'],
    ) {}

    public function getParameters(): Resource\Collection
    {
        return Resource\Collection::fromArray($this->parameters);
    }
}