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
use EliasHaeussler\PHPStanConfig\Exception;

use function is_string;

/**
 * ComposerPackage.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final readonly class ComposerPackage
{
    public string $constraint;

    public function __construct(
        public string $name,
        string $constraint,
        public Enums\PackageRelation $relation,
        public ?string $type = null,
        public ?string $extensionKey = null,
    ) {
        $this->constraint = self::parseConstraint($constraint, $relation);
    }

    /**
     * @param array{name?: mixed, type?: mixed, extra?: array{'typo3/cms'?: array{'extension-key': mixed}}} $declaration
     *
     * @throws Exception\ComposerPackageDeclarationIsInvalid
     */
    public static function fromDeclaration(
        array $declaration,
        string $constraint,
        Enums\PackageRelation $relation,
    ): self {
        $name = $declaration['name'] ?? null;
        $type = $declaration['type'] ?? null;
        $extensionKey = $declaration['extra']['typo3/cms']['extension-key'] ?? null;

        if (!is_string($name)) {
            throw new Exception\ComposerPackageDeclarationIsInvalid();
        }

        if (!is_string($type) && null !== $type) {
            throw new Exception\ComposerPackageDeclarationIsInvalid();
        }

        if (!is_string($extensionKey) && null !== $extensionKey) {
            throw new Exception\ComposerPackageDeclarationIsInvalid();
        }

        return new self($name, $constraint, $relation, $type, $extensionKey);
    }

    /**
     * @param list<array<string, mixed>> $versions
     *
     * @throws Exception\ComposerPackageDeclarationIsInvalid
     */
    public static function fromPackageVersions(
        array $versions,
        string $constraint,
        Enums\PackageRelation $relation,
    ): ?self {
        $parsedConstraint = self::parseConstraint($constraint, $relation);

        foreach ($versions as $metadata) {
            $version = $metadata['version'] ?? null;

            if (!is_string($version)) {
                continue;
            }

            try {
                if (!Semver\Semver::satisfies($version, $parsedConstraint)) {
                    continue;
                }
            } catch (\Exception) {
                continue;
            }

            /** @var array{'typo3/cms'?: array{'extension-key': mixed}} $extra */
            $extra = $metadata['extra'] ?? [];
            $declaration = [
                'name' => $metadata['name'] ?? null,
                'type' => $metadata['type'] ?? null,
                'extra' => $extra,
            ];

            return self::fromDeclaration($declaration, $parsedConstraint, $relation);
        }

        return null;
    }

    /**
     * @phpstan-assert-if-true !null $this->extensionKey
     */
    public function isExtension(): bool
    {
        return null !== $this->extensionKey;
    }

    /**
     * @return list<string>
     */
    public function getPossibleEmConfRelationNames(): array
    {
        if ('php' === $this->name) {
            return ['php'];
        }

        if (!$this->isExtension()) {
            return [];
        }

        $relationNames = [$this->extensionKey];

        if ($this->isTypo3FrameworkPackage()) {
            $relationNames[] = 'typo3';
        }

        return $relationNames;
    }

    public function isTypo3FrameworkPackage(): bool
    {
        return 'typo3-cms-framework' === $this->type;
    }

    public function normalizeConstraint(): Semver\Constraint\ConstraintInterface
    {
        return (new Semver\VersionParser())->parseConstraints($this->constraint);
    }

    private static function parseConstraint(string $constraint, Enums\PackageRelation $relation): string
    {
        $regex = match ($relation) {
            Enums\PackageRelation::Conflict, Enums\PackageRelation::Requirement => '/^(.+)$/',
            Enums\PackageRelation::Suggestion => '/^.+ \(([^)]+)\)$/',
        };

        if (1 !== preg_match($regex, $constraint, $matches)) {
            return Enums\PackageRelation::Suggestion === $relation ? '*' : $constraint;
        }

        return $matches[1];
    }
}
