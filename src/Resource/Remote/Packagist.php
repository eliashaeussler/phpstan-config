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

namespace EliasHaeussler\PHPStanConfig\Resource\Remote;

use Closure;
use JsonException;

use function file_get_contents;
use function is_array;
use function sprintf;
use function stream_context_create;

/**
 * Packagist.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class Packagist
{
    private const API_URL = 'https://repo.packagist.org/p2/%s.json';

    /**
     * @var array<string, list<array<string, mixed>>>
     */
    private static array $cache = [];

    /**
     * @var Closure(string): ?string
     */
    private readonly Closure $client;

    /**
     * @param Closure(string): ?string|null $client
     */
    public function __construct(?Closure $client = null)
    {
        $this->client = $client ?? self::createDefaultClient();
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    public function fetchPackageVersions(string $name): ?array
    {
        if (isset(self::$cache[$name])) {
            return self::$cache[$name];
        }

        $apiUrl = sprintf(self::API_URL, $name);
        $result = ($this->client)($apiUrl);

        if (null === $result) {
            return null;
        }

        try {
            $json = json_decode($result, true, 20, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        if (!is_array($json) || !is_array($json['packages'] ?? null) || !is_array($json['packages'][$name] ?? null)) {
            return null;
        }

        /** @var list<array<string, mixed>> $versions */
        $versions = $json['packages'][$name];

        return self::$cache[$name] = $versions;
    }

    /**
     * @return Closure(string): ?string
     */
    private static function createDefaultClient(): Closure
    {
        return static function (string $url) {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 15,
                ],
            ]);

            $response = @file_get_contents($url, false, $context);

            if (false === $response) {
                return null;
            }

            return $response;
        };
    }
}
