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

namespace Netresearch\ContextsDevice\Tests\Unit\Context;

use DeviceDetector\DeviceDetector;
use Netresearch\ContextsDevice\Context\DeviceDetectionAwareTrait;
use Netresearch\ContextsDevice\Service\DeviceDetectionService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for DeviceDetectionAwareTrait.
 *
 * Covers the injected branch of the service accessor. The lazy fallback needs a
 * real container and is covered in the functional test of the same name.
 */
final class DeviceDetectionAwareTraitTest extends TestCase
{
    #[Test]
    public function injectedServiceIsReturnedWithoutTouchingTheContainer(): void
    {
        // DeviceDetectionService is final, so the suite builds real instances
        // rather than mocks (same as the context type tests).
        $service = new DeviceDetectionService(new DeviceDetector());

        $host = new class ($service) {
            use DeviceDetectionAwareTrait;

            public function __construct(DeviceDetectionService $service)
            {
                $this->deviceDetectionService = $service;
            }

            public function resolveDeviceDetectionService(): DeviceDetectionService
            {
                return $this->getDeviceDetectionService();
            }
        };

        // Same instance: an injected service must never be replaced by a
        // container lookup (GeneralUtility::makeInstance would fail here, as
        // there is no container in a unit test).
        self::assertSame($service, $host->resolveDeviceDetectionService());
    }
}
