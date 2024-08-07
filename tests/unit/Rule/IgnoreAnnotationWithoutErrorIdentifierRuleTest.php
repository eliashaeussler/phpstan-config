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

namespace EliasHaeussler\PHPStanConfig\Tests\Rule;

use EliasHaeussler\PHPStanConfig as Src;
use PHPStan\Rules;
use PHPStan\Testing;
use PHPStan\Type;
use PHPUnit\Framework;

use function dirname;

/**
 * IgnoreAnnotationWithoutErrorIdentifierRuleTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @extends Testing\RuleTestCase<Src\Rule\IgnoreAnnotationWithoutErrorIdentifierRule>
 */
#[Framework\Attributes\CoversClass(Src\Rule\IgnoreAnnotationWithoutErrorIdentifierRule::class)]
final class IgnoreAnnotationWithoutErrorIdentifierRuleTest extends Testing\RuleTestCase
{
    #[Framework\Attributes\Test]
    public function processNodeReturnsNoErrorsIfNoMonitoredIgnoreAnnotationsAreUsed(): void
    {
        $this->analyse(
            [
                dirname(__DIR__).'/Fixtures/Files/no-ignore-annotations.php',
            ],
            [],
        );
    }

    #[Framework\Attributes\Test]
    public function processNodeReturnsNoErrorsIfMonitoredIgnoreAnnotationHasErrorIdentifierConfigured(): void
    {
        $this->analyse(
            [
                dirname(__DIR__).'/Fixtures/Files/ignore-annotation-with-error-identifier.php',
            ],
            [],
        );
    }

    #[Framework\Attributes\Test]
    public function processNodeReturnsErrorsIfMonitoredIgnoreAnnotationHasNoErrorIdentifierConfigured(): void
    {
        $this->analyse(
            [
                dirname(__DIR__).'/Fixtures/Files/ignore-annotation-without-error-identifier.php',
            ],
            [
                [
                    'Using an @ignore-next-line annotation without specifying an error identifier is not allowed.',
                    33,
                    'Read more at https://phpstan.org/user-guide/ignoring-errors and learn how to properly ignore errors.',
                ],
            ],
        );
    }

    protected function getRule(): Rules\Rule
    {
        return new Src\Rule\IgnoreAnnotationWithoutErrorIdentifierRule(
            self::getContainer()->getByType(Type\FileTypeMapper::class),
            // Omitting the phpstan- prefix is intended, otherwise this would cause side effects in tests
            [
                'ignore-line',
                'ignore-next-line',
            ],
        );
    }
}
