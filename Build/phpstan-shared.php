<?php

/*
 * Copyright (c) 2025-2026 Netresearch DTT GmbH
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

// Included by Build/phpstan.neon. CI removes netresearch/typo3-ci-workflows
// on the TYPO3 ^12.4 cells (remove-dev-deps in .github/workflows/ci.yml),
// because its saschaegerer/phpstan-typo3 requires typo3/cms-core ^13.4.3.
// phpstan/extension-installer goes with it. A plain NEON include of the
// shared config would fail there on a missing file, so the include is made
// only when the package is installed; phpstan/extension-installer then
// registers the strict, deprecation, PHPUnit, phpat, TYPO3 and ergebnis
// rules. Without the package, the ^12.4 cells include the rule sets they
// ran before by hand and keep the parameters the old config set.
$vendor = __DIR__ . '/../.Build/vendor';
$shared = $vendor . '/netresearch/typo3-ci-workflows/config/phpstan/phpstan.neon';

if (is_file($shared)) {
    return ['includes' => [$shared]];
}

return [
    'includes' => [
        $vendor . '/phpstan/phpstan-strict-rules/rules.neon',
        $vendor . '/phpstan/phpstan-deprecation-rules/rules.neon',
        $vendor . '/phpstan/phpstan-phpunit/extension.neon',
        $vendor . '/phpat/phpat/extension.neon',
    ],
    'parameters' => [
        'level'                     => 10,
        'treatPhpDocTypesAsCertain' => false,
        // The explicit analyseAndScan key is needed here: a plain list that
        // works in NEON arrives empty from a PHP config file.
        'excludePaths' => ['analyseAndScan' => [
            __DIR__ . '/../.Build/*',
        ]],
    ],
];
