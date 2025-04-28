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

namespace EliasHaeussler\PHPStanConfig\Config;

use EliasHaeussler\PHPStanConfig\Enums;
use EliasHaeussler\PHPStanConfig\Exception;
use EliasHaeussler\PHPStanConfig\Resource;
use EliasHaeussler\PHPStanConfig\Set;

use function array_map;
use function preg_quote;
use function rtrim;
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
     * @param non-empty-string       $projectDirectory
     * @param list<non-empty-string> $includes
     * @param list<Set\Set>          $sets
     */
    private function __construct(
        private readonly string $projectDirectory,
        public readonly Resource\Collection $parameters,
        private array $includes = [],
        private array $sets = [],
    ) {}

    /**
     * @param non-empty-string $projectDirectory
     */
    public static function create(string $projectDirectory): self
    {
        return new self(
            rtrim($projectDirectory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR,
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
        $paths = array_map($this->expandPath(...), $paths);

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
        $paths = array_map($this->expandPath(...), $paths);

        $this->parameters->add('excludePaths/analyseAndScan', ...$paths);

        return $this;
    }

    /**
     * @param int<0, 10> $level
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
     * @param array<non-empty-string, bool> $featureToggles
     *
     * @see https://phpstan.org/blog/what-is-bleeding-edge
     */
    public function withBleedingEdge(array $featureToggles = []): self
    {
        foreach ($featureToggles as $name => $value) {
            $this->parameters->set('featureToggles/'.$name, $value);
        }

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
     * @param non-empty-string|null $message
     * @param non-empty-string|null $path
     * @param positive-int|null     $count
     * @param non-empty-string|null $identifier
     *
     * @throws Exception\IgnoreErrorEntryIsNotValid
     *
     * @see https://phpstan.org/config-reference#ignoring-errors
     */
    public function ignoreError(
        ?string $message = null,
        ?string $path = null,
        ?int $count = null,
        ?bool $reportUnmatched = null,
        ?string $identifier = null,
    ): self {
        if (null === $message && null === $identifier) {
            throw new Exception\IgnoreErrorEntryIsNotValid();
        }

        // Convert plain message to regex
        if (null !== $message && !str_starts_with($message, '#')) {
            $message = sprintf('#^%s$#', preg_quote($message, '#'));
        }

        $error = [];

        if (null !== $message) {
            $error['message'] = $message;
        }
        if (null !== $path) {
            $error['path'] = $this->expandPath($path);
        }
        if (null !== $count) {
            $error['count'] = $count;
        }
        if (null !== $reportUnmatched) {
            $error['reportUnmatched'] = $reportUnmatched;
        }
        if (null !== $identifier) {
            $error['identifier'] = $identifier;
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
     * @see https://phpstan.org/config-reference#treatphpdoctypesascertain
     */
    public function treatPhpDocTypesAsCertain(bool $enable = true): self
    {
        $this->parameters->set('treatPhpDocTypesAsCertain', $enable);

        return $this;
    }

    /**
     * @see https://phpstan.org/config-reference#exceptions
     */
    public function checkTooWideThrowTypes(bool $enable = true): self
    {
        $this->parameters->set('exceptions/check/tooWideThrowType', $enable);

        return $this;
    }

    /**
     * @see https://phpstan.org/config-reference#exceptions
     */
    public function checkMissingCheckedExceptionInThrows(bool $enable = true): self
    {
        $this->parameters->set('exceptions/check/missingCheckedExceptionInThrows', $enable);

        return $this;
    }

    /**
     * @see https://phpstan.org/config-reference#exceptions
     */
    public function reportUncheckedExceptionDeadCatch(bool $enable = true): self
    {
        $this->parameters->set('exceptions/reportUncheckedExceptionDeadCatch', $enable);

        return $this;
    }

    public function useCustomRule(string $identifier, bool $enable = true): self
    {
        $this->parameters->set($identifier.'/enabled', $enable);

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

    /**
     * @param non-empty-string $path
     *
     * @return non-empty-string
     */
    private function expandPath(string $path): string
    {
        foreach ([DIRECTORY_SEPARATOR, 'phar://'] as $pathPrefix) {
            if (str_starts_with($path, $pathPrefix)) {
                return $path;
            }
        }

        return $this->projectDirectory.$path;
    }
}
