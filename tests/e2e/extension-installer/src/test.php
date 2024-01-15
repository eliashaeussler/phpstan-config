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

use PHPStan\ExtensionInstaller;

require dirname(__DIR__).'/vendor/autoload.php';

function error(string $message): never
{
    echo '🚨 '.$message.PHP_EOL;
    exit(1);
}

// Parse arguments
/** @var list<mixed> $arguments */
$arguments = $_SERVER['argv'] ?? [];
array_shift($arguments);

// Check if package name is passed
if ([] === $arguments) {
    error('No package name provided.');
}

// Get package name
$packageName = array_shift($arguments);
if (!is_string($packageName)) {
    error('Invalid package name provided.');
}

// Get installed extensions
$installedExtensions = ExtensionInstaller\GeneratedConfig::EXTENSIONS;

// Test if config package is installed
if (array_key_exists($packageName, $installedExtensions)) {
    echo '✅ Package "'.$packageName.'" is installed.'.PHP_EOL;
    exit(0);
}

// Exit with failure on error
error('Failed.');
