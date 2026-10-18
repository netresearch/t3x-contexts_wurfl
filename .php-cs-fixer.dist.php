<?php

/*
 * Copyright (c) 2025-2026 Netresearch DTT GmbH
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

$createConfig = require __DIR__ . '/.Build/vendor/netresearch/typo3-ci-workflows/config/php-cs-fixer/config.php';

return $createConfig(
    "Copyright (c) 2025-2026 Netresearch DTT GmbH\nSPDX-License-Identifier: AGPL-3.0-or-later",
    __DIR__,
    [
        // Rules this repository enforced before and the shared set does not
        // (or only in a narrower form).
        '@PHP8x0Migration:risky' => true,
        'strict_comparison'      => true,
        'strict_param'           => true,

        'class_attributes_separation' => [
            'elements' => [
                'const'    => 'one',
                'method'   => 'one',
                'property' => 'one',
            ],
        ],
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public_static',
                'property_protected_static',
                'property_private_static',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public_static',
                'method_protected_static',
                'method_private_static',
                'method_public',
                'method_protected',
                'method_private',
            ],
        ],
        'ordered_interfaces' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'curly_brace_block',
                'extra',
                'parenthesis_brace_block',
                'square_brace_block',
                'throw',
                'use',
            ],
        ],

        'explicit_string_variable'               => true,
        'multiline_comment_opening_closing'      => true,
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'no_superfluous_elseif'                  => true,
        'simplified_if_return'                   => true,
        'simplified_null_return'                 => true,

        'php_unit_construct'                     => true,
        'php_unit_dedicate_assert'               => ['target' => 'newest'],
        'php_unit_expectation'                   => ['target' => 'newest'],
        'php_unit_mock'                          => ['target' => 'newest'],
        'php_unit_mock_short_will_return'        => true,
        'php_unit_set_up_tear_down_visibility'   => true,
        'php_unit_strict'                        => true,
        'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
    ],
);
