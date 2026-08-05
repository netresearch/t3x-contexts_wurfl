<?php

/*
 * Copyright (c) 2025-2026 Netresearch DTT GmbH
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

use Rector\Config\RectorConfig;

$configure = require_once __DIR__ . '/../.Build/vendor/netresearch/typo3-ci-workflows/config/rector/rector.php';

return static function (RectorConfig $rectorConfig) use ($configure): void {
    // Shared org base config: code-quality sets, rule skips, importNames,
    // and the package's ergebnis-free phpstan-rector.neon.
    $configure($rectorConfig, __DIR__ . '/..');

    // paths() REPLACES the shared list, so restate it and add Tests/ — the
    // test suite is analysed by PHPStan and CGL here too.
    $rectorConfig->paths([
        __DIR__ . '/../Classes',
        __DIR__ . '/../Configuration',
        __DIR__ . '/../Resources',
        __DIR__ . '/../Tests',
        __DIR__ . '/../ext_localconf.php',
    ]);
};
