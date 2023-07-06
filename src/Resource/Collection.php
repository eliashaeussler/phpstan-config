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

namespace EliasHaeussler\PHPStanConfig\Resource;

use function array_merge_recursive;
use function is_array;
use function str_getcsv;

/**
 * Collection.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class Collection
{
    /**
     * @param non-empty-string               $pathDelimiter
     * @param array<non-empty-string, mixed> $collection
     */
    private function __construct(
        private readonly string $pathDelimiter,
        private array $collection,
    ) {
    }

    /**
     * @param non-empty-string $pathDelimiter
     */
    public static function create(string $pathDelimiter = '/'): self
    {
        return new self($pathDelimiter, []);
    }

    /**
     * @param array<non-empty-string, mixed> $collection
     * @param non-empty-string               $pathDelimiter
     */
    public static function fromArray(array $collection, string $pathDelimiter = '/'): self
    {
        return new self($pathDelimiter, $collection);
    }

    /**
     * @param non-empty-string $key
     */
    public function set(string $key, mixed $value): self
    {
        $this->modifyAtPath(
            $key,
            static function (array &$reference) use ($value) {
                $reference = $value;
            },
        );

        return $this;
    }

    /**
     * @param non-empty-string $key
     */
    public function add(string $key, mixed ...$values): self
    {
        $this->modifyAtPath(
            $key,
            static function (array &$reference) use ($values) {
                foreach ($values as $value) {
                    $reference[] = $value;
                }
            },
        );

        return $this;
    }

    public function merge(self $other): self
    {
        $clone = clone $this;
        $clone->collection = array_merge_recursive($clone->collection, $other->collection);

        return $clone;
    }

    /**
     * @return array<non-empty-string, mixed>
     */
    public function toArray(): array
    {
        return $this->collection;
    }

    /**
     * @param non-empty-string                                 $path
     * @param callable(array<string, mixed> &$reference): void $function
     */
    private function modifyAtPath(string $path, callable $function): void
    {
        $pathSegments = str_getcsv($path, $this->pathDelimiter);
        $reference = &$this->collection;

        foreach ($pathSegments as $pathSegment) {
            if (!is_array($reference[$pathSegment] ?? null)) {
                $reference[$pathSegment] = [];
            }

            $reference = &$reference[$pathSegment];
        }

        $function($reference);
    }
}
