<?php

/*
 * Copyright (c) 2025-2026 Netresearch DTT GmbH
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

/**
 * Router script for PHP built-in server to handle TYPO3 rewrites
 *
 * This router simulates Apache/Nginx URL rewriting for TYPO3 clean URLs during E2E testing
 * with the PHP built-in server.
 *
 * Usage:
 *   php -S 0.0.0.0:8080 -t .Build/Web Build/Scripts/router.php
 *
 * The router:
 * 1. Serves static files (CSS, JS, images, etc.) directly, but only if the
 *    canonicalised path stays inside the .Build/Web web root (closes #30).
 * 2. Routes all other requests through TYPO3's index.php.
 *
 * Even though this script is dev-only and never deployed, the realpath()
 * containment check makes the file-serving branch immune to traversal
 * payloads (`/../../etc/passwd`, double-encoded `%2e%2e`, symlinks, etc.).
 *
 * @package Netresearch\ContextsWurfl
 */

declare(strict_types=1);

$webRoot = realpath(__DIR__ . '/../../.Build/Web');
$path    = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);

// Serve a file directly only if the resolved path is a real file *inside*
// the web root. realpath() resolves `.`, `..`, and symlinks before we
// compare, so a request like `/../../etc/passwd` cannot escape the
// allowed root no matter how it's encoded.
if (is_string($path) && $webRoot !== false) {
    $candidate = realpath($webRoot . $path);

    if ($candidate !== false
        && is_file($candidate)
        && str_starts_with($candidate . DIRECTORY_SEPARATOR, $webRoot . DIRECTORY_SEPARATOR)
    ) {
        return false;
    }
}

// Route everything else through TYPO3 index.php
$_SERVER['SCRIPT_NAME']     = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../../.Build/Web/index.php';
require __DIR__ . '/../../.Build/Web/index.php';
