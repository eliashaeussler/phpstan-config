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

/**
 * PackageMap.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final readonly class PackageMap
{
    public function __construct(
        public ?EmConfRelation $emConfRelation = null,
        public ?ComposerPackage $composerPackage = null,
    ) {}

    /**
     * @phpstan-assert-if-true !null $this->emConfRelation
     * @phpstan-assert-if-true !null $this->composerPackage
     */
    public function isComplete(): bool
    {
        return null !== $this->emConfRelation && null !== $this->composerPackage;
    }

    public function hasEqualConstraints(): bool
    {
        if (!$this->isComplete()) {
            return false;
        }

        $emConfRelationConstraint = $this->emConfRelation->normalizeConstraint();
        $composerPackageConstraint = $this->composerPackage->normalizeConstraint();

        $emConfRelationLowerBound = $emConfRelationConstraint->getLowerBound();
        $emConfRelationUpperBound = $emConfRelationConstraint->getUpperBound();
        $composerPackageLowerBound = $composerPackageConstraint->getLowerBound();
        $composerPackageUpperBound = $composerPackageConstraint->getUpperBound();

        return Semver\Comparator::equalTo($emConfRelationLowerBound->getVersion(), $composerPackageLowerBound->getVersion())
            && $emConfRelationLowerBound->isInclusive() === $composerPackageLowerBound->isInclusive()
            && Semver\Comparator::equalTo($emConfRelationUpperBound->getVersion(), $composerPackageUpperBound->getVersion())
            && $emConfRelationUpperBound->isInclusive() === $composerPackageUpperBound->isInclusive()
        ;
    }
}
