<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/phpstan-config".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
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
        $this->subject = Src\Config\Config::create();
    }

    #[Framework\Attributes\Test]
    public function withSetsIntegratesGivenSetsIntoConfig(): void
    {
        $sets = [
            new Src\Tests\Fixtures\DummySet(),
            new Src\Tests\Fixtures\DummySet(['baz' => 'foo']),
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
        $this->subject->in('foo', 'baz');

        $expected = [
            'includes' => [],
            'parameters' => [
                'paths' => [
                    'foo',
                    'baz',
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
                    'foo',
                    'baz',
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
    public function withBaselineConfiguresIncludeForBaselineFile(): void
    {
        $this->subject->withBaseline();

        $expected = [
            'includes' => [
                'phpstan-baseline.neon',
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
                'foo',
                'baz',
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
                'tmpDir' => 'foo',
            ],
        ];

        self::assertSame($expected, $this->subject->toArray());
    }

    /**
     * @param non-empty-string|null                                                                         $path
     * @param positive-int|null                                                                             $count
     * @param array{message: string, path?: non-empty-string, count?: positive-int, reportUnmatched?: bool} $expected
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('ignoreErrorConfiguresIgnoreErrorForGivenMessageDataProvider')]
    public function ignoreErrorConfiguresIgnoreErrorForGivenMessage(
        ?string $path,
        ?int $count,
        ?bool $reportUnmatched,
        array $expected,
    ): void {
        $this->subject->ignoreError('foo', $path, $count, $reportUnmatched);

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

    /**
     * @return Generator<string, array{
     *     non-empty-string|null,
     *     positive-int|null,
     *     bool|null,
     *     array{message: string, path?: non-empty-string, count?: positive-int, reportUnmatched?: bool},
     * }>
     */
    public static function ignoreErrorConfiguresIgnoreErrorForGivenMessageDataProvider(): Generator
    {
        yield 'message only' => [
            null,
            null,
            null,
            ['message' => '#^foo$#'],
        ];
        yield 'message and path' => [
            'baz',
            null,
            null,
            ['message' => '#^foo$#', 'path' => 'baz'],
        ];
        yield 'message, path and count' => [
            'baz',
            3,
            null,
            ['message' => '#^foo$#', 'path' => 'baz', 'count' => 3],
        ];
        yield 'message, path, count and reportUnmatched' => [
            'baz',
            3,
            true,
            ['message' => '#^foo$#', 'path' => 'baz', 'count' => 3, 'reportUnmatched' => true],
        ];
    }
}
