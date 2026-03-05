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
 * ComposerPackageTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Resource\ComposerPackage::class)]
final class ComposerPackageTest extends Framework\TestCase
{
    private Src\Resource\ComposerPackage $subject;

    public function setUp(): void
    {
        $this->subject = $this->createPackage();
    }

    /**
     * @return Generator<string, array{string, Src\Enums\PackageRelation, string}>
     */
    public static function constructorParsesConstraintDataProvider(): Generator
    {
        yield 'conflict' => ['^1.0', Src\Enums\PackageRelation::Conflict, '^1.0'];
        yield 'requirement' => ['^1.0', Src\Enums\PackageRelation::Requirement, '^1.0'];
        yield 'suggestion without version' => ['Has (some) nice features', Src\Enums\PackageRelation::Suggestion, '*'];
        yield 'suggestion with empty version' => ['Has nice features ()', Src\Enums\PackageRelation::Suggestion, '*'];
        yield 'suggestion with text and version' => ['Has nice features (^1.0)', Src\Enums\PackageRelation::Suggestion, '^1.0'];
        yield 'suggestion with version' => ['^1.0', Src\Enums\PackageRelation::Suggestion, '*'];
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('constructorParsesConstraintDataProvider')]
    public function constructorParsesConstraint(
        string $constrint,
        Src\Enums\PackageRelation $relation,
        string $expected,
    ): void {
        $subject = new Src\Resource\ComposerPackage('foo/baz', $constrint, $relation);

        self::assertSame($expected, $subject->constraint);
    }

    #[Framework\Attributes\Test]
    public function fromDeclarationThrowsExceptionOnInvalidName(): void
    {
        $this->expectExceptionObject(new Src\Exception\ComposerPackageDeclarationIsInvalid());

        $declaration = [
            'name' => false,
        ];

        Src\Resource\ComposerPackage::fromDeclaration($declaration, '*', Src\Enums\PackageRelation::Requirement);
    }

    #[Framework\Attributes\Test]
    public function fromDeclarationThrowsExceptionOnInvalidType(): void
    {
        $this->expectExceptionObject(new Src\Exception\ComposerPackageDeclarationIsInvalid());

        $declaration = [
            'name' => 'foo/baz',
            'type' => false,
        ];

        Src\Resource\ComposerPackage::fromDeclaration($declaration, '*', Src\Enums\PackageRelation::Requirement);
    }

    #[Framework\Attributes\Test]
    public function fromDeclarationThrowsExceptionOnInvalidExtensionKey(): void
    {
        $this->expectExceptionObject(new Src\Exception\ComposerPackageDeclarationIsInvalid());

        $declaration = [
            'name' => 'foo/baz',
            'extra' => [
                'typo3/cms' => [
                    'extension-key' => false,
                ],
            ],
        ];

        Src\Resource\ComposerPackage::fromDeclaration($declaration, '*', Src\Enums\PackageRelation::Requirement);
    }

    #[Framework\Attributes\Test]
    public function fromDeclarationReturnsConstructedComposerPackage(): void
    {
        $declaration = [
            'name' => 'foo/baz',
            'type' => 'typo3-cms-extension',
            'extra' => [
                'typo3/cms' => [
                    'extension-key' => 'baz',
                ],
            ],
        ];

        $expected = $this->createPackage(extension: true);

        self::assertEquals(
            $expected,
            Src\Resource\ComposerPackage::fromDeclaration($declaration, '*', Src\Enums\PackageRelation::Requirement),
        );
    }

    #[Framework\Attributes\Test]
    public function fromPackageVersionsReturnsNullOnEmptyVersionsArray(): void
    {
        self::assertNull(Src\Resource\ComposerPackage::fromPackageVersions([], '*', Src\Enums\PackageRelation::Requirement));
    }

    #[Framework\Attributes\Test]
    public function fromPackageVersionsReturnsNullOnInvalidVersions(): void
    {
        $versions = [
            [
                'version' => false,
                'name' => 'foo/baz',
            ],
            [
                'version' => 'foo',
                'name' => 'foo/baz',
            ],
        ];

        self::assertNull(Src\Resource\ComposerPackage::fromPackageVersions($versions, '*', Src\Enums\PackageRelation::Requirement));
    }

    #[Framework\Attributes\Test]
    public function fromPackageVersionsSkipsVersionsWithInvalidVersion(): void
    {
        $versions = [
            [
                'version' => false,
                'name' => 'foo/baz',
            ],
            [
                'version' => '1.0.0',
                'name' => 'foo/baz',
                'type' => 'typo3-cms-extension',
                'extra' => [
                    'typo3/cms' => [
                        'extension-key' => 'baz',
                    ],
                ],
            ],
        ];

        $expected = $this->createPackage(extension: true);

        self::assertEquals(
            $expected,
            Src\Resource\ComposerPackage::fromPackageVersions($versions, '*', Src\Enums\PackageRelation::Requirement),
        );
    }

    #[Framework\Attributes\Test]
    public function isExtensionReturnsTrueOnTypo3ExtensionPackage(): void
    {
        self::assertFalse($this->subject->isExtension());
        self::assertTrue($this->createPackage(extension: true)->isExtension());
    }

    #[Framework\Attributes\Test]
    public function getPossibleEmConfRelationNamesReturnsPhpOnPhpRequirement(): void
    {
        $subject = $this->createPackage('php');

        self::assertSame(['php'], $subject->getPossibleEmConfRelationNames());
    }

    #[Framework\Attributes\Test]
    public function getPossibleEmConfRelationNamesReturnsEmptyStringOnIfPackageIsNotAnExtension(): void
    {
        self::assertSame([], $this->subject->getPossibleEmConfRelationNames());
    }

    #[Framework\Attributes\Test]
    public function getPossibleEmConfRelationNamesReturnsExtensionKeyIfPackageIsAnExtension(): void
    {
        $subject = $this->createPackage(extension: true);

        self::assertSame(['baz'], $subject->getPossibleEmConfRelationNames());
    }

    #[Framework\Attributes\Test]
    public function getPossibleEmConfRelationNamesReturnsExtensionKeyAndGenericNameIfPackageIsATypo3FrameworkPackage(): void
    {
        $subject = $this->createPackage(framework: true);

        self::assertSame(['baz', 'typo3'], $subject->getPossibleEmConfRelationNames());
    }

    #[Framework\Attributes\Test]
    public function isTypo3FrameworkPackageReturnsTrueIfPackageIsATypo3FrameworkPackage(): void
    {
        self::assertFalse($this->subject->isTypo3FrameworkPackage());
        self::assertTrue($this->createPackage(framework: true)->isTypo3FrameworkPackage());
    }

    #[Framework\Attributes\Test]
    public function normalizeConstraintReturnsParsedConstraint(): void
    {
        $expected = new Semver\Constraint\MatchAllConstraint();
        $expected->setPrettyString('*');

        self::assertEquals($expected, $this->subject->normalizeConstraint());
    }

    private function createPackage(string $name = 'foo/baz', bool $extension = false, bool $framework = false): Src\Resource\ComposerPackage
    {
        return new Src\Resource\ComposerPackage(
            $name,
            '*',
            Src\Enums\PackageRelation::Requirement,
            match (true) {
                $extension => 'typo3-cms-extension',
                $framework => 'typo3-cms-framework',
                default => null,
            },
            $extension || $framework ? 'baz' : null,
        );
    }
}
