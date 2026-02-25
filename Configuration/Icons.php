<?php

/*
 * Copyright (c) 2025-2026 Netresearch DTT GmbH
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'ext-contexts_wurfl' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:contexts_wurfl/Resources/Public/Icons/Extension.svg',
    ],
];
