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

namespace EliasHaeussler\PHPStanConfig\Resource\Local;

use EliasHaeussler\PHPStanConfig\Enums;
use EliasHaeussler\PHPStanConfig\Resource;
use PhpParser\Node;
use Symfony\Component\Filesystem;

/**
 * ExtEmConf.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final readonly class ExtEmConf
{
    public string $filename;

    public function __construct(
        private string $currentWorkingDirectory,
    ) {
        $this->filename = Filesystem\Path::join($this->currentWorkingDirectory, 'ext_emconf.php');
    }

    /**
     * @return list<Resource\EmConfRelation>
     */
    public function extractRelations(Node\Expr\Array_ $array, Enums\PackageRelation $relation): array
    {
        foreach ($array->items as $item) {
            if (!$item->key instanceof Node\Scalar\String_ || 'constraints' !== $item->key->value) {
                continue;
            }

            if (!$item->value instanceof Node\Expr\Array_) {
                return [];
            }

            foreach ($item->value->items as $relations) {
                if (!$relations->key instanceof Node\Scalar\String_ || $relations->key->value !== $relation->forExtEmConf()) {
                    continue;
                }

                if (!$relations->value instanceof Node\Expr\Array_) {
                    return [];
                }

                $result = [];

                foreach ($relations->value->items as $relationItem) {
                    if (!$relationItem->key instanceof Node\Scalar\String_ || !$relationItem->value instanceof Node\Scalar\String_) {
                        continue;
                    }

                    $result[] = new Resource\EmConfRelation(
                        $relationItem->key->value,
                        $relationItem->value->value,
                        $relation,
                        $relationItem->getLine(),
                    );
                }

                return $result;
            }
        }

        return [];
    }
}
