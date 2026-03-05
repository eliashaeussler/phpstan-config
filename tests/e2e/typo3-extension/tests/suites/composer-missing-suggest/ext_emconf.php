<?php

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

/* @noinspection PhpUndefinedVariableInspection */
$EM_CONF[$_EXTKEY] = [
    'title' => 'phpstan-config typo3-extension test',
    'description' => 'TYPO3 test extension for phpstan-config library',
    'category' => 'misc',
    'version' => '1.0.0',
    'state' => 'stable',
    'author' => 'Elias Häußler',
    'author_email' => 'elias@haeussler.dev',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.3.99',
            'php' => '8.2.0-8.5.99',
        ],
        'suggests' => [
            'sitemap_locator' => '1.0.0-1.99.99',
        ],
    ],
];
