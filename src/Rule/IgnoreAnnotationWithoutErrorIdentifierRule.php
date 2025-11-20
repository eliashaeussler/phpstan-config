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

namespace EliasHaeussler\PHPStanConfig\Rule;

use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\PhpDocParser;
use PHPStan\Rules;
use PHPStan\Type;

use function in_array;
use function preg_match;
use function sprintf;

/**
 * IgnoreAnnotationWithoutErrorIdentifierRule.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @implements Rules\Rule<Node\Stmt>
 */
final readonly class IgnoreAnnotationWithoutErrorIdentifierRule implements Rules\Rule
{
    /**
     * @param list<non-empty-string> $monitoredAnnotations
     */
    public function __construct(
        private Type\FileTypeMapper $fileTypeMapper,
        private array $monitoredAnnotations,
    ) {}

    public function getNodeType(): string
    {
        return Node\Stmt::class;
    }

    public function processNode(Node $node, Analyser\Scope $scope): array
    {
        $errors = [];

        foreach ($node->getComments() as $comment) {
            $commentText = $comment->getText();

            // Convert inline comments to phpdoc to allow usage of phpdoc parser
            if (1 !== preg_match('#^/\*{2}#', $commentText)) {
                $commentText = '/** '.preg_replace(['#^(/\*|//)#', '#\*/$#'], '', $commentText).' */';
            }

            // Parse and resolve phpdoc
            $resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
                $scope->getFile(),
                $scope->getClassReflection()?->getName(),
                $scope->getTraitReflection()?->getName(),
                $scope->getFunction()?->getName(),
                $commentText,
            );

            foreach ($resolvedPhpDoc->getPhpDocNodes() as $phpDocNode) {
                foreach ($phpDocNode->children as $phpDocChildNode) {
                    // We only check phpdoc tag nodes
                    if (!($phpDocChildNode instanceof PhpDocParser\Ast\PhpDoc\PhpDocTagNode)) {
                        continue;
                    }

                    $name = ltrim($phpDocChildNode->name, '@');

                    // Add error if ignore annotation has no error identifier configured
                    if (in_array($name, $this->monitoredAnnotations, true) && '' === trim((string) $phpDocChildNode->value)) {
                        $errors[] = $this->createRuleError($name, $scope, $node);
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * @return Rules\IdentifierRuleError
     */
    private function createRuleError(string $annotation, Analyser\Scope $scope, Node $node): Rules\RuleError
    {
        $ruleError = Rules\RuleErrorBuilder::message(
            sprintf('Using an @%s annotation without specifying an error identifier is not allowed.', $annotation),
        );

        return $ruleError
            ->identifier('ignoreAnnotation.withoutErrorIdentifier')
            ->tip('Read more at https://phpstan.org/user-guide/ignoring-errors and learn how to properly ignore errors.')
            ->nonIgnorable()
            ->build()
        ;
    }
}
