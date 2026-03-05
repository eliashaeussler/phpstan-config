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

namespace EliasHaeussler\PHPStanConfig\Tests\Resource;

use Composer\Semver;
use EliasHaeussler\PHPStanConfig as Src;
use Generator;
use PHPUnit\Framework;

/**
 * EmConfRelationTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Resource\EmConfRelation::class)]
final class EmConfRelationTest extends Framework\TestCase
{
    /**
     * @return Generator<string, array{string, Semver\Constraint\ConstraintInterface}>
     */
    public static function normalizeConstraintReturnsParsedConstraintWithLowerAndUpperBoundDataProvider(): Generator
    {
        yield 'upper bound 99 at patch level' => ['13.4.0-14.3.99', self::createConstraint('14.4.0')];
        yield 'upper bound 999 at patch level' => ['13.4.0-14.3.999', self::createConstraint('14.4.0')];
        yield 'upper bound 9999 at patch level' => ['13.4.0-14.3.9999', self::createConstraint('14.4.0')];
        yield 'upper bound 99999 at patch level' => ['13.4.0-14.3.99999', self::createConstraint('14.4.0')];

        yield 'upper bound 99 at minor level' => ['13.4.0-14.99.99', self::createConstraint('15.0.0')];
        yield 'upper bound 999 at minor level' => ['13.4.0-14.999.999', self::createConstraint('15.0.0')];
        yield 'upper bound 9999 at minor level' => ['13.4.0-14.9999.9999', self::createConstraint('15.0.0')];
        yield 'upper bound 99999 at minor level' => ['13.4.0-14.99999.99999', self::createConstraint('15.0.0')];

        yield 'mixed upper bound' => ['13.4.0-14.9999.99', self::createConstraint('15.0.0')];
    }

    #[Framework\Attributes\Test]
    public function normalizeConstraintTrimsWhitespacesFromVersionBounds(): void
    {
        $subject = new Src\Resource\EmConfRelation(
            'typo3',
            '13.4.0 - 0.0.0',
            Src\Enums\PackageRelation::Requirement,
            17,
        );

        $expected = new Semver\Constraint\Constraint('>=', '13.4.0.0-dev');
        $expected->setPrettyString('>= 13.4.0');

        self::assertEquals($expected, $subject->normalizeConstraint());
    }

    #[Framework\Attributes\Test]
    public function normalizeConstraintReturnsParsedConstraintWithLowerBoundOnly(): void
    {
        $subject = new Src\Resource\EmConfRelation(
            'typo3',
            '13.4.0',
            Src\Enums\PackageRelation::Requirement,
            17,
        );

        $expected = new Semver\Constraint\Constraint('>=', '13.4.0.0-dev');
        $expected->setPrettyString('>= 13.4.0');

        self::assertEquals($expected, $subject->normalizeConstraint());
    }

    #[Framework\Attributes\Test]
    public function normalizeConstraintReturnsParsedConstraintWithLowerBoundAndInfiniteUpperBound(): void
    {
        $subject = new Src\Resource\EmConfRelation(
            'typo3',
            '13.4.0-0.0.0',
            Src\Enums\PackageRelation::Requirement,
            17,
        );

        $expected = new Semver\Constraint\Constraint('>=', '13.4.0.0-dev');
        $expected->setPrettyString('>= 13.4.0');

        self::assertEquals($expected, $subject->normalizeConstraint());
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('normalizeConstraintReturnsParsedConstraintWithLowerAndUpperBoundDataProvider')]
    public function normalizeConstraintReturnsParsedConstraintWithLowerAndUpperBound(
        string $constraint,
        Semver\Constraint\ConstraintInterface $expected,
    ): void {
        $subject = new Src\Resource\EmConfRelation(
            'typo3',
            $constraint,
            Src\Enums\PackageRelation::Requirement,
            17,
        );

        self::assertEquals($expected, $subject->normalizeConstraint());
    }

    private static function createConstraint(string $upperBound): Semver\Constraint\ConstraintInterface
    {
        $lowerBoundConstraint = new Semver\Constraint\Constraint('>=', '13.4.0.0-dev');
        $upperBoundConstraint = new Semver\Constraint\Constraint('<', $upperBound.'.0-dev');

        $constraint = Semver\Constraint\MultiConstraint::create(
            [
                $lowerBoundConstraint,
                $upperBoundConstraint,
            ],
        );
        $constraint->setPrettyString('>= 13.4.0 < '.$upperBound);

        return $constraint;
    }
}
