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

namespace EliasHaeussler\PHPStanConfig\Config;

use EliasHaeussler\PHPStanConfig\Enums;
use EliasHaeussler\PHPStanConfig\Resource;
use EliasHaeussler\PHPStanConfig\Set;

use function preg_quote;
use function sprintf;
use function str_starts_with;

/**
 * Config.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class Config
{
    /**
     * @param list<non-empty-string> $includes
     * @param list<Set\Set>          $sets
     */
    private function __construct(
        private readonly Resource\Collection $parameters,
        private array $includes = [],
        private array $sets = [],
    ) {
    }

    public static function create(): self
    {
        return new self(
            Resource\Collection::create(),
        );
    }

    public function withSets(Set\Set ...$sets): self
    {
        foreach ($sets as $set) {
            $this->sets[] = $set;
        }

        return $this;
    }

    /**
     * @param non-empty-string ...$paths
     *
     * @see https://phpstan.org/config-reference#analysed-files
     */
    public function in(string ...$paths): self
    {
        $this->parameters->add('paths', ...$paths);

        return $this;
    }

    /**
     * @param non-empty-string ...$paths
     *
     * @see https://phpstan.org/config-reference#analysed-files
     */
    public function not(string ...$paths): self
    {
        $this->parameters->add('excludePaths', ...$paths);

        return $this;
    }

    /**
     * @param int<0, 9> $level
     *
     * @see https://phpstan.org/config-reference#rule-level
     */
    public function level(int $level): self
    {
        $this->parameters->set('level', $level);

        return $this;
    }

    /**
     * @see https://phpstan.org/user-guide/rule-levels
     */
    public function maxLevel(): self
    {
        $this->parameters->set('level', 'max');

        return $this;
    }

    /**
     * @see https://phpstan.org/blog/what-is-bleeding-edge
     */
    public function withBleedingEdge(): self
    {
        return $this->with('phar://phpstan.phar/conf/bleedingEdge.neon');
    }

    /**
     * @param non-empty-string $file
     *
     * @see https://phpstan.org/user-guide/baseline
     */
    public function withBaseline(string $file = 'phpstan-baseline.neon'): self
    {
        return $this->with($file);
    }

    /**
     * @param non-empty-string ...$files
     *
     * @see https://phpstan.org/config-reference#multiple-files
     */
    public function with(string ...$files): self
    {
        foreach ($files as $file) {
            $this->includes[] = $file;
        }

        return $this;
    }

    /**
     * @param non-empty-string ...$files
     *
     * @see https://phpstan.org/config-reference#bootstrap
     */
    public function bootstrapFiles(string ...$files): self
    {
        $this->parameters->add('bootstrapFiles', ...$files);

        return $this;
    }

    /**
     * @param non-empty-string ...$files
     *
     * @see https://phpstan.org/config-reference#stub-files
     */
    public function stubFiles(string ...$files): self
    {
        $this->parameters->add('stubFiles', ...$files);

        return $this;
    }

    /**
     * @param non-empty-string $cacheDir
     *
     * @see https://phpstan.org/config-reference#caching
     */
    public function useCacheDir(string $cacheDir): self
    {
        $this->parameters->set('tmpDir', $cacheDir);

        return $this;
    }

    /**
     * @param non-empty-string      $message
     * @param non-empty-string|null $path
     * @param positive-int|null     $count
     *
     * @see https://phpstan.org/config-reference#ignoring-errors
     */
    public function ignoreError(
        string $message,
        string $path = null,
        int $count = null,
        bool $reportUnmatched = null,
    ): self {
        // Convert plain message to regex
        if (!str_starts_with($message, '#')) {
            $message = sprintf('#^%s$#', preg_quote($message, '#'));
        }

        $error = [
            'message' => $message,
        ];

        if (null !== $path) {
            $error['path'] = $path;
        }
        if (null !== $count) {
            $error['count'] = $count;
        }
        if (null !== $reportUnmatched) {
            $error['reportUnmatched'] = $reportUnmatched;
        }

        $this->parameters->add('ignoreErrors', $error);

        return $this;
    }

    /**
     * @see https://phpstan.org/user-guide/ignoring-errors#reporting-unused-ignores
     */
    public function reportUnmatchedIgnoredErrors(bool $enable = true): self
    {
        $this->parameters->set('reportUnmatchedIgnoredErrors', $enable);

        return $this;
    }

    /**
     * @see https://phpstan.org/config-reference#errorformat
     */
    public function formatAs(Enums\ErrorFormat $format): self
    {
        $this->parameters->set('errorFormat', $format->value);

        return $this;
    }

    /**
     * @return array{
     *     includes: list<non-empty-string>,
     *     parameters: array<non-empty-string, mixed>,
     * }
     */
    public function toArray(): array
    {
        $parameters = $this->parameters;

        foreach ($this->sets as $set) {
            if ($set instanceof Set\ParameterizableSet) {
                $parameters = $parameters->merge($set->getParameters());
            }
        }

        return [
            'includes' => $this->includes,
            'parameters' => $parameters->toArray(),
        ];
    }
}
