<?php

/*
 * Copyright (c) 2025-2026 Netresearch DTT GmbH
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

/**
 * This file is part of the package netresearch/contexts-wurfl.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Netresearch\ContextsDevice\Tests\Functional\Context;

use Netresearch\ContextsDevice\Context\Type\DeviceContext;
use Netresearch\ContextsDevice\Service\DeviceDetectionService;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Functional tests for the lazy service fallback in DeviceDetectionAwareTrait.
 *
 * Context types are built by netresearch/contexts Factory::createFromDb() via
 * GeneralUtility::makeInstance() with only the database row, so the fallback is
 * the production path. It requires DeviceDetectionService to be retrievable
 * from the container; these tests pin that.
 */
final class DeviceDetectionAwareTraitTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'netresearch/contexts',
        'netresearch/contexts-wurfl',
    ];

    /**
     * Guards the `public: true` entry in Configuration/Services.yaml. Without
     * it the container lookup misses and makeInstance() falls back to plain
     * instantiation, which cannot satisfy the required DeviceDetector argument.
     */
    #[Test]
    public function deviceDetectionServiceIsRetrievableFromTheContainer(): void
    {
        // getContainer() hands out the container of PUBLIC services only, so
        // has() here really asserts public visibility. get() would also see
        // non-public services and would pass without the fix.
        self::assertTrue(
            $this->getContainer()->has(DeviceDetectionService::class),
            'DeviceDetectionService must be public so makeInstance() can resolve it',
        );
    }

    #[Test]
    public function makeInstanceResolvesTheAutowiredService(): void
    {
        self::assertInstanceOf(
            DeviceDetectionService::class,
            GeneralUtility::makeInstance(DeviceDetectionService::class),
        );
    }

    #[Test]
    public function contextWithoutInjectedServiceResolvesOneLazily(): void
    {
        // Mirrors Factory::createFromDb(): only the row, no service.
        $context = new class ([]) extends DeviceContext {
            public function resolveDeviceDetectionService(): DeviceDetectionService
            {
                return $this->getDeviceDetectionService();
            }
        };

        self::assertInstanceOf(
            DeviceDetectionService::class,
            $context->resolveDeviceDetectionService(),
        );
    }
}
