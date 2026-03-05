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

namespace EliasHaeussler\PHPStanConfig\Resource\Local;

use JsonException;

use function array_key_exists;
use function file_get_contents;
use function is_array;
use function is_file;
use function json_decode;

/**
 * JsonFile.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class JsonFile
{
    /**
     * @var array<string, array<mixed>|null>
     */
    private static array $parsed = [];

    public function __construct(
        public readonly string $path,
    ) {}

    /**
     * @return array<mixed>|null
     */
    public function parse(): ?array
    {
        if (array_key_exists($this->path, self::$parsed)) {
            return self::$parsed[$this->path];
        }

        if (!is_file($this->path)) {
            return null;
        }

        $content = file_get_contents($this->path);

        if (false === $content) {
            return null;
        }

        try {
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        if (!is_array($decoded)) {
            $decoded = null;
        }

        return self::$parsed[$this->path] = $decoded;
    }
}
