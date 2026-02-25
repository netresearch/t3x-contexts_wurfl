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

namespace Netresearch\ContextsDevice\Tests\Unit\Dto;

use Netresearch\ContextsDevice\Dto\DeviceInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DeviceInfo::class)]
final class DeviceInfoTest extends TestCase
{
    /**
     * @return iterable<string, array{bool, bool, bool, bool, bool}>
     */
    public static function deviceTypeDataProvider(): iterable
    {
        yield 'mobile phone' => [true, false, false, false, true];
        yield 'tablet' => [true, true, false, false, false];
        yield 'desktop' => [false, false, true, false, false];
        yield 'bot' => [false, false, false, true, false];
        yield 'mobile bot' => [true, false, false, true, true];
    }

    #[Test]
    public function constructorSetsAllProperties(): void
    {
        $deviceInfo = new DeviceInfo(
            isMobile: true,
            isTablet: false,
            isDesktop: false,
            isBot: false,
            browserName: 'Chrome Mobile',
            browserVersion: '120.0',
            osName: 'Android',
            osVersion: '14',
            deviceBrand: 'Samsung',
            deviceModel: 'Galaxy S24',
        );

        self::assertTrue($deviceInfo->isMobile);
        self::assertFalse($deviceInfo->isTablet);
        self::assertFalse($deviceInfo->isDesktop);
        self::assertFalse($deviceInfo->isBot);
        self::assertSame('Chrome Mobile', $deviceInfo->browserName);
        self::assertSame('120.0', $deviceInfo->browserVersion);
        self::assertSame('Android', $deviceInfo->osName);
        self::assertSame('14', $deviceInfo->osVersion);
        self::assertSame('Samsung', $deviceInfo->deviceBrand);
        self::assertSame('Galaxy S24', $deviceInfo->deviceModel);
    }

    #[Test]
    public function constructorDefaultsToNullAndFalseValues(): void
    {
        $deviceInfo = new DeviceInfo();

        self::assertFalse($deviceInfo->isMobile);
        self::assertFalse($deviceInfo->isTablet);
        self::assertFalse($deviceInfo->isDesktop);
        self::assertFalse($deviceInfo->isBot);
        self::assertNull($deviceInfo->browserName);
        self::assertNull($deviceInfo->browserVersion);
        self::assertNull($deviceInfo->osName);
        self::assertNull($deviceInfo->osVersion);
        self::assertNull($deviceInfo->deviceBrand);
        self::assertNull($deviceInfo->deviceModel);
    }

    #[Test]
    public function isPhoneReturnsTrueForMobileNonTablet(): void
    {
        $deviceInfo = new DeviceInfo(
            isMobile: true,
            isTablet: false,
        );

        self::assertTrue($deviceInfo->isPhone());
    }

    #[Test]
    public function isPhoneReturnsFalseForTablet(): void
    {
        $deviceInfo = new DeviceInfo(
            isMobile: true,
            isTablet: true,
        );

        self::assertFalse($deviceInfo->isPhone());
    }

    #[Test]
    public function isPhoneReturnsFalseForDesktop(): void
    {
        $deviceInfo = new DeviceInfo(
            isMobile: false,
            isDesktop: true,
        );

        self::assertFalse($deviceInfo->isPhone());
    }

    #[Test]
    #[DataProvider('deviceTypeDataProvider')]
    public function deviceTypeDetectionWorksCorrectly(
        bool $isMobile,
        bool $isTablet,
        bool $isDesktop,
        bool $isBot,
        bool $expectedIsPhone,
    ): void {
        $deviceInfo = new DeviceInfo(
            isMobile: $isMobile,
            isTablet: $isTablet,
            isDesktop: $isDesktop,
            isBot: $isBot,
        );

        self::assertSame($expectedIsPhone, $deviceInfo->isPhone());
    }

    #[Test]
    public function hasBrowserInfoReturnsTrueWhenBrowserNameSet(): void
    {
        $deviceInfo = new DeviceInfo(browserName: 'Firefox');

        self::assertTrue($deviceInfo->hasBrowserInfo());
    }

    #[Test]
    public function hasBrowserInfoReturnsFalseWhenBrowserNameNull(): void
    {
        $deviceInfo = new DeviceInfo();

        self::assertFalse($deviceInfo->hasBrowserInfo());
    }

    #[Test]
    public function hasOsInfoReturnsTrueWhenOsNameSet(): void
    {
        $deviceInfo = new DeviceInfo(osName: 'Windows');

        self::assertTrue($deviceInfo->hasOsInfo());
    }

    #[Test]
    public function hasOsInfoReturnsFalseWhenOsNameNull(): void
    {
        $deviceInfo = new DeviceInfo();

        self::assertFalse($deviceInfo->hasOsInfo());
    }

    #[Test]
    public function hasDeviceInfoReturnsTrueWhenBrandSet(): void
    {
        $deviceInfo = new DeviceInfo(deviceBrand: 'Apple');

        self::assertTrue($deviceInfo->hasDeviceInfo());
    }

    #[Test]
    public function hasDeviceInfoReturnsTrueWhenModelSet(): void
    {
        $deviceInfo = new DeviceInfo(deviceModel: 'iPhone 15');

        self::assertTrue($deviceInfo->hasDeviceInfo());
    }

    #[Test]
    public function hasDeviceInfoReturnsFalseWhenBothNull(): void
    {
        $deviceInfo = new DeviceInfo();

        self::assertFalse($deviceInfo->hasDeviceInfo());
    }
}
