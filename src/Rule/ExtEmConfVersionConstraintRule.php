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

namespace EliasHaeussler\PHPStanConfig\Rule;

use EliasHaeussler\PHPStanConfig\Enums;
use EliasHaeussler\PHPStanConfig\Resource;
use Exception;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Rules;

use function array_values;

/**
 * ExtEmConfVersionConstraintRule.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @implements CustomRule<Node\Expr\Assign>
 */
final readonly class ExtEmConfVersionConstraintRule implements CustomRule
{
    private Resource\Local\ComposerJson $composerJson;
    private Resource\Local\ExtEmConf $extEmConf;

    public function __construct(
        private string $currentWorkingDirectory,
    ) {
        $this->composerJson = new Resource\Local\ComposerJson($this->currentWorkingDirectory);
        $this->extEmConf = new Resource\Local\ExtEmConf($this->currentWorkingDirectory);
    }

    public static function getIdentifier(): string
    {
        return 'extEmConfVersionConstraint';
    }

    public function getNodeType(): string
    {
        return Node\Expr\Assign::class;
    }

    public function processNode(Node $node, Analyser\Scope $scope): array
    {
        // Early return on other files than ext_emconf.php
        if ($scope->getFile() !== $this->extEmConf->filename) {
            return [];
        }

        // Left-hand side must be $EM_CONF[$_EXTKEY]
        if (!$this->isEmConfAssignment($node->var)) {
            return [];
        }

        // Right-hand side must be an array literal
        if (!$node->expr instanceof Node\Expr\Array_) {
            return [];
        }

        $emConfDependencies = $this->extEmConf->extractRelations($node->expr, Enums\PackageRelation::Requirement);
        $emConfConflicts = $this->extEmConf->extractRelations($node->expr, Enums\PackageRelation::Conflict);
        $emConfSuggestions = $this->extEmConf->extractRelations($node->expr, Enums\PackageRelation::Suggestion);

        $composerRequirements = $this->composerJson->extractPackages(Enums\PackageRelation::Requirement);
        $composerConflicts = $this->composerJson->extractPackages(Enums\PackageRelation::Conflict);
        $composerSuggestions = $this->composerJson->extractPackages(Enums\PackageRelation::Suggestion);

        $dependenciesMaps = $this->buildPackageMaps($emConfDependencies, $composerRequirements);
        $conflictsMaps = $this->buildPackageMaps($emConfConflicts, $composerConflicts);
        $suggestionsMaps = $this->buildPackageMaps($emConfSuggestions, $composerSuggestions);

        return [
            ...$this->collectErrors($dependenciesMaps),
            ...$this->collectErrors($conflictsMaps),
            ...$this->collectErrors($suggestionsMaps),
        ];
    }

    private function isEmConfAssignment(Node\Expr $expr): bool
    {
        if (!$expr instanceof Node\Expr\ArrayDimFetch) {
            return false;
        }

        if (!$expr->var instanceof Node\Expr\Variable) {
            return false;
        }

        if ('EM_CONF' !== $expr->var->name) {
            return false;
        }

        if (null === $expr->dim) {
            return false;
        }

        return $expr->dim instanceof Node\Expr\Variable && '_EXTKEY' === $expr->dim->name;
    }

    /**
     * @param list<Resource\EmConfRelation>  $emConfRelations
     * @param list<Resource\ComposerPackage> $composerPackages
     *
     * @return list<Resource\PackageMap>
     */
    private function buildPackageMaps(array $emConfRelations, array $composerPackages): array
    {
        $map = [];
        $identify = static fn (
            ?Resource\EmConfRelation $relation,
            ?Resource\ComposerPackage $package,
        ) => $relation?->name.'_'.$package?->name;

        foreach ($emConfRelations as $emConfRelation) {
            $resolvedPackage = null;

            foreach ($composerPackages as $composerPackage) {
                $possibleRelationNames = $composerPackage->getPossibleEmConfRelationNames();

                if (in_array($emConfRelation->name, $possibleRelationNames, true)) {
                    $resolvedPackage = $composerPackage;
                    break;
                }
            }

            $identifier = $identify($emConfRelation, $resolvedPackage);
            $map[$identifier] = new Resource\PackageMap($emConfRelation, $resolvedPackage);
        }

        foreach ($composerPackages as $composerPackage) {
            $possibleRelationNames = $composerPackage->getPossibleEmConfRelationNames();
            $resolvedRelation = null;

            // Skip all non-platform and non-extension packages
            if ('php' !== $composerPackage->name && !$composerPackage->isExtension()) {
                continue;
            }

            foreach ($emConfRelations as $emConfRelation) {
                if (in_array($emConfRelation->name, $possibleRelationNames, true)) {
                    $resolvedRelation = $emConfRelation;
                    break;
                }
            }

            $identifier = $identify($resolvedRelation, $composerPackage);
            $map[$identifier] = new Resource\PackageMap($resolvedRelation, $composerPackage);
        }

        return array_values($map);
    }

    /**
     * @param list<Resource\PackageMap> $packageMaps
     *
     * @return list<Rules\IdentifierRuleError>
     */
    private function collectErrors(array $packageMaps): array
    {
        $errors = [];

        foreach ($packageMaps as $packageMap) {
            if (!$packageMap->isComplete()) {
                $errors[] = $this->buildIncompletePackageMapError($packageMap);
            } else {
                try {
                    if (!$packageMap->hasEqualConstraints()) {
                        $errors[] = $this->buildConstraintMismatchError($packageMap);
                    }
                } catch (Exception) {
                    // Ignore invalid constraints to keep analysis stable
                }
            }
        }

        return $errors;
    }

    /**
     * @return Rules\IdentifierRuleError
     */
    private function buildIncompletePackageMapError(Resource\PackageMap $packageMap): Rules\RuleError
    {
        if (null === $packageMap->emConfRelation) {
            /** @var Resource\ComposerPackage $composerPackage */
            $composerPackage = $packageMap->composerPackage;
            $identifier = 'extEmConf.missing.'.$composerPackage->relation->forExtEmConf();
            $ruleError = Rules\RuleErrorBuilder::message(
                sprintf(
                    '%s Composer package "%s" is not reflected in ext_emconf.php file.',
                    match ($composerPackage->relation) {
                        Enums\PackageRelation::Conflict => 'Conflicting',
                        Enums\PackageRelation::Suggestion => 'Suggested',
                        Enums\PackageRelation::Requirement => 'Required',
                    },
                    $composerPackage->name,
                ),
            );

            return $ruleError
                ->identifier($identifier)
                ->file($this->composerJson->manifest->path)
                // @todo Determine line from composer.json
                // ->line(1)
                ->build()
            ;
        }

        $identifier = 'composerJson.missing.'.$packageMap->emConfRelation->relation->forComposerJson();
        $ruleError = Rules\RuleErrorBuilder::message(
            sprintf(
                '%s extension "%s" is not reflected in composer.json file.',
                match ($packageMap->emConfRelation->relation) {
                    Enums\PackageRelation::Conflict => 'Conflicting',
                    Enums\PackageRelation::Requirement => 'Required',
                    Enums\PackageRelation::Suggestion => 'Suggested',
                },
                $packageMap->emConfRelation->name,
            ),
        );

        if ('typo3' === $packageMap->emConfRelation->name) {
            $ruleError->tip('Use "typo3/cms-core" as Composer package name.');
        }

        return $ruleError
            ->identifier($identifier)
            ->file($this->extEmConf->filename)
            ->line($packageMap->emConfRelation->line)
            ->build()
        ;
    }

    /**
     * @return Rules\IdentifierRuleError
     */
    private function buildConstraintMismatchError(Resource\PackageMap $packageMap): Rules\RuleError
    {
        /** @var Resource\EmConfRelation $emConfRelation */
        $emConfRelation = $packageMap->emConfRelation;
        /** @var Resource\ComposerPackage $composerPackage */
        $composerPackage = $packageMap->composerPackage;

        return Rules\RuleErrorBuilder::message(
            sprintf(
                'Version constraint of %s "%s" in ext_emconf.php (%s) is not equal to version constraint in composer.json (%s).',
                match ($emConfRelation->relation) {
                    Enums\PackageRelation::Conflict => 'conflict',
                    Enums\PackageRelation::Requirement => 'dependency',
                    Enums\PackageRelation::Suggestion => 'suggestion',
                },
                $emConfRelation->name,
                $emConfRelation->constraint,
                $composerPackage->constraint,
            ),
        )
            ->identifier('extEmConf.constraints.mismatch')
            ->file($this->extEmConf->filename)
            ->line($emConfRelation->line)
            ->build()
        ;
    }
}
