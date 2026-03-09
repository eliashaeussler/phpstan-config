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

use Symfony\Component\Filesystem;
use Symfony\Component\Process;

require dirname(__DIR__).'/vendor/autoload.php';

function error(string $message): never
{
    echo '🚨 '.$message.PHP_EOL;
    exit(1);
}

// Define paths
$filesystem = new Filesystem\Filesystem();
$workingDirectory = getcwd();
$rootPath = dirname(__DIR__);
$suitesPath = $rootPath.'/tests/suites';
$applicationPath = $rootPath.'/tests/application';

// Check if working directory is correct
if ($workingDirectory !== $rootPath) {
    error('Working directory must be the root of the project.');
}

// Parse arguments
/** @var list<mixed> $arguments */
$arguments = $_SERVER['argv'] ?? [];
array_shift($arguments);

// Check if suite name is passed
if ([] === $arguments) {
    error('No suite name provided.');
}

// Get suite name
$suiteName = array_shift($arguments);
if (!is_string($suiteName)) {
    error('Invalid suite name provided.');
}

// Check suite path
$suitePath = $suitesPath.'/'.$suiteName;
if (!is_dir($suitePath)) {
    error('Suite "'.$suiteName.'" not found.');
}

echo '⏳ Suite "'.$suiteName.'" is running...'.PHP_EOL;

// Prepare test suite
$filesystem->remove($applicationPath);
$filesystem->mirror($suitePath, $applicationPath);

// Run test suite
$installProcess = new Process\Process(['composer', 'install'], $applicationPath);
$installProcess->run();
$testProcess = new Process\Process(['composer', 'test'], $applicationPath);
$testProcess->run();

// Parse result
try {
    $result = json_decode($testProcess->getOutput(), true, 20, JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    error('Failed: '.$exception->getMessage());
}

// Validate result
if (is_array($result)
    && is_array($result['errors'] ?? null)
    && [] === $result['errors']
    && 0 === $testProcess->getExitCode()
) {
    echo '✅ Suite "'.$suiteName.'" was successful.'.PHP_EOL;
    exit(0);
}

// Exit with failure on error
echo $testProcess->getOutput().PHP_EOL;
error('Failed.');
