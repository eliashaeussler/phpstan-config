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

namespace EliasHaeussler\PHPStanConfig\Set;

use EliasHaeussler\PHPStanConfig\Resource;

/**
 * SymfonySet.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @see https://github.com/phpstan/phpstan-symfony
 */
final class SymfonySet implements ParameterizableSet
{
    private function __construct(
        private readonly Resource\Collection $parameters,
    ) {}

    public static function create(): self
    {
        return new self(
            Resource\Collection::create(),
        );
    }

    /**
     * @param non-empty-string $file
     *
     * @see https://github.com/phpstan/phpstan-symfony#analysis-of-symfony-console-commands
     */
    public function withConsoleApplicationLoader(string $file): self
    {
        $this->parameters->set('symfony/consoleApplicationLoader', $file);

        return $this;
    }

    /**
     * @param non-empty-string $file
     *
     * @see https://github.com/phpstan/phpstan-symfony#configuration
     */
    public function withContainerXmlPath(string $file): self
    {
        $this->parameters->set('symfony/containerXmlPath', $file);

        return $this;
    }

    /**
     * @see https://github.com/phpstan/phpstan-symfony#constant-hassers
     */
    public function disableConstantHassers(): self
    {
        $this->parameters->set('symfony/constantHassers', false);

        return $this;
    }

    public function getParameters(): Resource\Collection
    {
        return $this->parameters;
    }
}
