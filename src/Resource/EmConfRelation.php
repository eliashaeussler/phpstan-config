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

namespace EliasHaeussler\PHPStanConfig\Resource;

use Composer\Semver;
use EliasHaeussler\PHPStanConfig\Enums;

use function array_map;
use function array_reverse;
use function explode;
use function in_array;
use function sprintf;
use function str_contains;
use function trim;

/**
 * EmConfRelation.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final readonly class EmConfRelation
{
    public function __construct(
        public string $name,
        public string $constraint,
        public Enums\PackageRelation $relation,
        public int $line,
    ) {}

    public function normalizeConstraint(): Semver\Constraint\ConstraintInterface
    {
        $versionParser = new Semver\VersionParser();

        if (str_contains($this->constraint, '-')) {
            [$lowerBound, $upperBound] = array_map(trim(...), explode('-', $this->constraint));
        } else {
            $lowerBound = $this->constraint;
            $upperBound = '0.0.0';
        }

        if ('0.0.0' === $upperBound) {
            $constraint = sprintf('>= %s', $lowerBound);
        } else {
            $constraint = sprintf('>= %s < %s', $lowerBound, $this->increaseUpperBound($upperBound));
        }

        return $versionParser->parseConstraints($constraint);
    }

    private function increaseUpperBound(string $upperBound): string
    {
        $versionParts = explode('.', $upperBound);
        $increaseNext = false;

        foreach (array_reverse($versionParts, true) as $i => $versionPart) {
            $versionPart = (int) $versionPart;

            if (in_array($versionPart, [99, 999, 9999, 99999], true)) {
                $versionPart = 0;
                $increaseNext = true;
            } elseif ($increaseNext) {
                ++$versionPart;
                $increaseNext = false;
            }

            $versionParts[$i] = $versionPart;
        }

        return implode('.', $versionParts);
    }
}
