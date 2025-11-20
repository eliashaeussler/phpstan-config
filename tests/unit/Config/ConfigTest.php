<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/phpstan-config".
 *
 * Copyright (C) 2023-2025 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\PHPStanConfig\Tests\Config;

use EliasHaeussler\PHPStanConfig as Src;
use EliasHaeussler\PHPStanConfig\Tests;
use Generator;
use PHPUnit\Framework;

/**
 * ConfigTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class ConfigTest extends Framework\TestCase
{
    private Src\Config\Config $subject;

    protected function setUp(): void
    {
        $this->subject = Src\Config\Config::create('/my-project');
    }

    #[Framework\Attributes\Test]
    public function createSetCreatesAndReturnsGivenSetByClassName(): void
    {
        $this->subject->withSet(Tests\Fixtures\DummySet::class);

        self::assertSame(
            [
                'includes' => [],
                'parameters' => [
                    'foo' => 'baz',
                ],
            ],
            $this->subject->toArray(),
        );
    }

    #[Framework\Attributes\Test]
    public function createSetInitializesAndReturnsGivenSet(): void
    {
        $validated = false;

        $this->subject->withSet(
            static function (Tests\Fixtures\DummySet $set) use (&$validated) {
                $validated = true;

                $expected = Tests\Fixtures\DummySet::create();
                $expected->setProjectPath(new Src\Resource\Path('/my-project'));

                self::assertEquals($expected, $set);

                $set->parameters = ['baz' => 'foo'];
            },
        );

        self::assertTrue($validated);
        self::assertSame(
            [
                'includes' => [],
                'parameters' => [
                    'baz' => 'foo',
                ],
            ],
            $this->subject->toArray(),
        );
    }

    #[Framework\Attributes\Test]
    public function withSetsIntegratesGivenSetsIntoConfig(): void
    {
        $sets = [
            Tests\Fixtures\DummySet::create(),
            Tests\Fixtures\DummySet::create(['baz' => 'foo']),
        ];

        $this->subject->withSets(...$sets);

        $expected = [
            'includes' => [],
            'parameters' => [
                'foo' => 'baz',
                'baz' => 'foo',
            ],
        ];

        self::assertSame($expected, $this->subject->toArray());
    }

    #[Framework\Attributes\Test]
    public function inConfiguresPaths(): void
    {
        $this->subject->in('foo', 'baz', '/foo/baz');

        $expected = [
            'includes' => [],
            'parameters' => [
                'paths' => [
                    '/my-project/foo',
                    '/my-project/baz',
                    '/foo/baz',
                ],
            ],
        ];

        self::assertSame($expected, $this->subject->toArray());
    }

    #[Framework\Attributes\Test]
    public function notConfiguresExcludePaths(): void
    {
        $this->subject->not('foo', 'baz');

        $expected = [
            'includes' => [],
            'parameters' => [
                'excludePaths' => [
                    'analyseAndScan' => [
                        '/my-project/foo',
                        '/my-project/baz',
                    ],
                ],
            ],
        ];

        self::assertSame($expected, $this->subject->toArray());
    }

    #[Framework\Attributes\Test]
    public function levelConfiguresRuleLevel(): void
    {
        $this->subject->level(6);

        $expected = [
            'includes' => [],
            'parameters' => [
                'level' => 6,
            ],
        ];

        self::assertSame($expected, $this->subject->toArray());
    }

    #[Framework\Attributes\Test]
    public function maxLevelConfiguresMaxRuleLevel(): void
    {
        $this->subject->maxLevel();

        $expected = [
            'includes' => [],
            'parameters' => [
                'level' => 'max',
            ],
        ];

        self::assertSame($expected, $this->subject->toArray());
    }

    #[Framework\Attributes\Test]
    public function withBleedingEdgeConfiguresIncludeForBleedingEdgeConfig(): void
    {
        $this->subject->withBleedingEdge();

        $expected = [
            'includes' => [
                'phar://phpstan.phar/conf/bleedingEdge.neon',
            ],
            'parameters' => [],
        ];

        self::assertSame($expected, $this->subject->toArray());
    }

    #[Framework\Attributes\Test]
    public function withBleedingEdgeTogglesGivenFeatures(): void
    {
        $this->subject->withBleedingEdge([
            'consistentConstructor' => false,
            'explicitMixedForGlobalVariables' => false,
        ]);

        $expected = [
            'includes' => [
                'phar://phpstan.phar/conf/bleedingEdge.neon',
            ],
            'parameters' => [
                'featureToggles' => [
                    'consistentConstructor' => false,
                    'explicitMixedForGlobalVariables' => false,
                ],
            ],
        ];

        self::assertSame($expected, $this->subject->toArray());
    }

    #[Framework\Attributes\Test]
    public function withBaselineConfiguresIncludeForBaselineFile(): void
    {
        $this->subject->withBaseline();

        $expected = [
            'includes' => [
                '/my-project/phpstan-baseline.neon',
            ],
            'parameters' => [],
        ];

        self::assertSame($expected, $this->subject->toArray());
    }

    #[Framework\Attributes\Test]
    public function withConfiguresIncludeForGivenFiles(): void
    {
        $this->subject->with('foo', 'baz');

        $expected = [
            'includes' => [
                '/my-project/foo',
                '/my-project/baz',
            ],
            'parameters' => [],
        ];

        self::assertSame($expected, $this->subject->toArray());
    }

    #[Framework\Attributes\Test]
    public function bootstrapFilesConfiguresBootstrapFiles(): void
    {
        $this->subject->bootstrapFiles('foo', 'baz');

        $expected = [
            'includes' => [],
            'parameters' => [
                'bootstrapFiles' => [
                    'foo',
                    'baz',
                ],
            ],
        ];

        self::assertSame($expected, $this->subject->toArray());
    }

    #[Framework\Attributes\Test]
    public function stubFilesConfiguresStubFiles(): void
    {
        $this->subject->stubFiles('foo', 'baz');

        $expected = [
            'includes' => [],
            'parameters' => [
                'stubFiles' => [
                    'foo',
                    'baz',
                ],
            ],
        ];

        self::assertSame($expected, $this->subject->toArray());
    }

    #[Framework\Attributes\Test]
    public function useCacheDirConfiguresCustomCacheDirectory(): void
    {
        $this->subject->useCacheDir('foo');

        $expected = [
            'includes' => [],
            'parameters' => [
                'tmpDir' => '/my-project/foo',
            ],
        ];

        self::assertSame($expected, $this->subject->toArray());
    }

    #[Framework\Attributes\Test]
    public function ignoreErrorThrowsExceptionIfNeitherMessageNorIdentifierAreSet(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\IgnoreErrorEntryIsNotValid(),
        );

        $this->subject->ignoreError();
    }

    /**
     * @param non-empty-string|null $message
     * @param non-empty-string|null $path
     * @param positive-int|null     $count
     * @param non-empty-string|null $identifier
     * @param array{
     *     message?: non-empty-string,
     *     path?: non-empty-string,
     *     count?: positive-int,
     *     reportUnmatched?: bool,
     *     identifier?: non-empty-string,
     * } $expected
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('ignoreErrorConfiguresIgnoreErrorForGivenMessageDataProvider')]
    public function ignoreErrorConfiguresIgnoreErrorForGivenMessage(
        ?string $message,
        ?string $path,
        ?int $count,
        ?bool $reportUnmatched,
        ?string $identifier,
        array $expected,
    ): void {
        $this->subject->ignoreError($message, $path, $count, $reportUnmatched, $identifier);

        self::assertSame(
            [
                'includes' => [],
                'parameters' => [
                    'ignoreErrors' => [
                        $expected,
                    ],
                ],
            ],
            $this->subject->toArray(),
        );
    }

    #[Framework\Attributes\Test]
    public function ignoreErrorConvertsPlainMessageToRegularExpression(): void
    {
        $this->subject->ignoreError('foo');

        self::assertSame(
            [
                'includes' => [],
                'parameters' => [
                    'ignoreErrors' => [
                        [
                            'message' => '#^foo$#',
                        ],
                    ],
                ],
            ],
            $this->subject->toArray(),
        );
    }

    #[Framework\Attributes\Test]
    public function ignoreErrorHandlesMessagesWithRegularExpressions(): void
    {
        $this->subject->ignoreError('#foo#');

        self::assertSame(
            [
                'includes' => [],
                'parameters' => [
                    'ignoreErrors' => [
                        [
                            'message' => '#foo#',
                        ],
                    ],
                ],
            ],
            $this->subject->toArray(),
        );
    }

    #[Framework\Attributes\Test]
    public function reportUnmatchedIgnoredErrorsConfiguresReportUnmatchedIgnoredErrorsParameter(): void
    {
        $this->subject->reportUnmatchedIgnoredErrors();

        $expected = [
            'includes' => [],
            'parameters' => [
                'reportUnmatchedIgnoredErrors' => true,
            ],
        ];

        self::assertSame($expected, $this->subject->toArray());
    }

    #[Framework\Attributes\Test]
    public function formatAsConfiguresErrorFormatter(): void
    {
        $this->subject->formatAs(Src\Enums\ErrorFormat::GitHub);

        $expected = [
            'includes' => [],
            'parameters' => [
                'errorFormat' => Src\Enums\ErrorFormat::GitHub->value,
            ],
        ];

        self::assertSame($expected, $this->subject->toArray());
    }

    #[Framework\Attributes\Test]
    public function treatPhpDocTypesAsCertainConfiguresTreatPhpDocTypesAsCertain(): void
    {
        $this->subject->treatPhpDocTypesAsCertain();

        $expected = [
            'includes' => [],
            'parameters' => [
                'treatPhpDocTypesAsCertain' => true,
            ],
        ];

        self::assertSame($expected, $this->subject->toArray());
    }

    #[Framework\Attributes\Test]
    public function checkTooWideThrowTypesConfiguresExceptionsCheckForTooWideThrowTypes(): void
    {
        $this->subject->checkTooWideThrowTypes();

        $expected = [
            'includes' => [],
            'parameters' => [
                'exceptions' => [
                    'check' => [
                        'tooWideThrowType' => true,
                    ],
                ],
            ],
        ];

        self::assertSame($expected, $this->subject->toArray());
    }

    #[Framework\Attributes\Test]
    public function checkMissingCheckedExceptionInThrowsConfiguresExceptionsCheckForMissingCheckedExceptionInThrows(): void
    {
        $this->subject->checkMissingCheckedExceptionInThrows();

        $expected = [
            'includes' => [],
            'parameters' => [
                'exceptions' => [
                    'check' => [
                        'missingCheckedExceptionInThrows' => true,
                    ],
                ],
            ],
        ];

        self::assertSame($expected, $this->subject->toArray());
    }

    #[Framework\Attributes\Test]
    public function reportUncheckedExceptionDeadCatchConfiguresReportForUncheckedExceptionDeadCatch(): void
    {
        $this->subject->reportUncheckedExceptionDeadCatch();

        $expected = [
            'includes' => [],
            'parameters' => [
                'exceptions' => [
                    'reportUncheckedExceptionDeadCatch' => true,
                ],
            ],
        ];

        self::assertSame($expected, $this->subject->toArray());
    }

    #[Framework\Attributes\Test]
    public function useCustomRuleEnablesOrDisablesCustomRule(): void
    {
        $this->subject->useCustomRule('foo');

        $expected = [
            'includes' => [],
            'parameters' => [
                'foo' => [
                    'enabled' => true,
                ],
            ],
        ];

        self::assertSame($expected, $this->subject->toArray());
    }

    /**
     * @return Generator<string, array{
     *     non-empty-string|null,
     *     non-empty-string|null,
     *     positive-int|null,
     *     bool|null,
     *     non-empty-string|null,
     *     array{
     *         message?: string,
     *         path?: non-empty-string,
     *         count?: positive-int,
     *         reportUnmatched?: bool,
     *         identifier?: non-empty-string,
     *     },
     * }>
     */
    public static function ignoreErrorConfiguresIgnoreErrorForGivenMessageDataProvider(): Generator
    {
        yield 'message only' => [
            'foo',
            null,
            null,
            null,
            null,
            ['message' => '#^foo$#'],
        ];
        yield 'message and relative path' => [
            'foo',
            'baz',
            null,
            null,
            null,
            ['message' => '#^foo$#', 'path' => '/my-project/baz'],
        ];
        yield 'message and absolute path' => [
            'foo',
            '/foo/baz',
            null,
            null,
            null,
            ['message' => '#^foo$#', 'path' => '/foo/baz'],
        ];
        yield 'message, path and count' => [
            'foo',
            'baz',
            3,
            null,
            null,
            ['message' => '#^foo$#', 'path' => '/my-project/baz', 'count' => 3],
        ];
        yield 'message, path, count and reportUnmatched' => [
            'foo',
            'baz',
            3,
            true,
            null,
            ['message' => '#^foo$#', 'path' => '/my-project/baz', 'count' => 3, 'reportUnmatched' => true],
        ];
        yield 'message, path, count, reportUnmatched and identifier' => [
            'foo',
            'baz',
            3,
            true,
            'boo',
            ['message' => '#^foo$#', 'path' => '/my-project/baz', 'count' => 3, 'reportUnmatched' => true, 'identifier' => 'boo'],
        ];
        yield 'identifier only' => [
            null,
            null,
            null,
            null,
            'foo',
            ['identifier' => 'foo'],
        ];
    }
}
