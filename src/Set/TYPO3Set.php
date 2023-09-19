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

namespace EliasHaeussler\PHPStanConfig\Set;

use EliasHaeussler\PHPStanConfig\Resource;

/**
 * TYPO3Set.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @see https://github.com/sascha-egerer/phpstan-typo3
 */
final class TYPO3Set implements ParameterizableSet
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
     * @param non-empty-string $name
     * @param class-string     $class
     *
     * @see https://github.com/sascha-egerer/phpstan-typo3#custom-context-api-aspects
     */
    public function withCustomAspect(string $name, string $class): self
    {
        $this->parameters->set('typo3/contextApiGetAspectMapping/'.$name, $class);

        return $this;
    }

    /**
     * @param non-empty-string $name
     * @param non-empty-string $type
     *
     * @see https://github.com/sascha-egerer/phpstan-typo3#custom-request-attribute
     */
    public function withCustomRequestAttribute(string $name, string $type): self
    {
        $this->parameters->set('typo3/requestGetAttributeMapping/'.$name, $type);

        return $this;
    }

    /**
     * @param non-empty-string $name
     * @param non-empty-string $type
     *
     * @see https://github.com/sascha-egerer/phpstan-typo3#custom-site-attribute
     */
    public function withCustomSiteAttribute(string $name, string $type): self
    {
        $this->parameters->set('typo3/siteGetAttributeMapping/'.$name, $type);

        return $this;
    }

    public function getParameters(): Resource\Collection
    {
        return $this->parameters;
    }
}
