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

namespace Netresearch\ContextsDevice\Tests\Unit\Service;

use DeviceDetector\DeviceDetector;
use Netresearch\ContextsDevice\Dto\DeviceInfo;
use Netresearch\ContextsDevice\Service\DeviceDetectionService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

#[CoversClass(DeviceDetectionService::class)]
final class DeviceDetectionServiceTest extends TestCase
{
    private const CHROME_DESKTOP_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    private const IPHONE_UA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1';

    private const IPAD_UA = 'Mozilla/5.0 (iPad; CPU OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1';

    private const GOOGLEBOT_UA = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';

    private const ANDROID_UA = 'Mozilla/5.0 (Linux; Android 14; SM-S928B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36';

    /**
     * @return iterable<string, array{string, bool, bool, bool, bool}>
     */
    public static function userAgentDataProvider(): iterable
    {
        yield 'Chrome on Windows' => [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            false, // isMobile
            false, // isTablet
            true,  // isDesktop
            false, // isBot
        ];

        yield 'Safari on macOS' => [
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_2) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15',
            false, // isMobile
            false, // isTablet
            true,  // isDesktop
            false, // isBot
        ];

        yield 'Firefox on Linux' => [
            'Mozilla/5.0 (X11; Linux x86_64; rv:120.0) Gecko/20100101 Firefox/120.0',
            false, // isMobile
            false, // isTablet
            true,  // isDesktop
            false, // isBot
        ];

        yield 'Chrome on Android phone' => [
            'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
            true,  // isMobile
            false, // isTablet
            false, // isDesktop
            false, // isBot
        ];

        yield 'Safari on iPhone' => [
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1',
            true,  // isMobile
            false, // isTablet
            false, // isDesktop
            false, // isBot
        ];

        yield 'Safari on iPad' => [
            'Mozilla/5.0 (iPad; CPU OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1',
            true,  // isMobile
            true,  // isTablet
            false, // isDesktop
            false, // isBot
        ];

        yield 'Googlebot' => [
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            false, // isMobile
            false, // isTablet
            false, // isDesktop
            true,  // isBot
        ];

        yield 'Bingbot' => [
            'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
            false, // isMobile
            false, // isTablet
            false, // isDesktop
            true,  // isBot
        ];
    }

    #[Test]
    public function detectFromUserAgentReturnsDeviceInfoForDesktop(): void
    {
        $deviceDetector = new DeviceDetector();
        $service = new DeviceDetectionService($deviceDetector);

        $result = $service->detectFromUserAgent(self::CHROME_DESKTOP_UA);

        self::assertInstanceOf(DeviceInfo::class, $result);
        self::assertTrue($result->isDesktop);
        self::assertFalse($result->isMobile);
        self::assertFalse($result->isTablet);
        self::assertFalse($result->isBot);
        self::assertSame('Chrome', $result->browserName);
        self::assertNotNull($result->browserVersion);
        self::assertSame('Windows', $result->osName);
    }

    #[Test]
    public function detectFromUserAgentReturnsDeviceInfoForMobilePhone(): void
    {
        $deviceDetector = new DeviceDetector();
        $service = new DeviceDetectionService($deviceDetector);

        $result = $service->detectFromUserAgent(self::IPHONE_UA);

        self::assertInstanceOf(DeviceInfo::class, $result);
        self::assertTrue($result->isMobile);
        self::assertFalse($result->isTablet);
        self::assertFalse($result->isDesktop);
        self::assertFalse($result->isBot);
        self::assertTrue($result->isPhone());
        self::assertSame('iOS', $result->osName);
        self::assertSame('Apple', $result->deviceBrand);
        self::assertStringContainsString('iPhone', $result->deviceModel ?? '');
    }

    #[Test]
    public function detectFromUserAgentReturnsDeviceInfoForTablet(): void
    {
        $deviceDetector = new DeviceDetector();
        $service = new DeviceDetectionService($deviceDetector);

        $result = $service->detectFromUserAgent(self::IPAD_UA);

        self::assertInstanceOf(DeviceInfo::class, $result);
        self::assertTrue($result->isMobile);
        self::assertTrue($result->isTablet);
        self::assertFalse($result->isDesktop);
        self::assertFalse($result->isBot);
        self::assertFalse($result->isPhone());
        // DeviceDetector reports iPadOS for modern iPads
        self::assertContains($result->osName, ['iOS', 'iPadOS'], 'OS should be iOS or iPadOS');
        self::assertSame('Apple', $result->deviceBrand);
    }

    #[Test]
    public function detectFromUserAgentReturnsDeviceInfoForBot(): void
    {
        $deviceDetector = new DeviceDetector();
        $service = new DeviceDetectionService($deviceDetector);

        $result = $service->detectFromUserAgent(self::GOOGLEBOT_UA);

        self::assertInstanceOf(DeviceInfo::class, $result);
        self::assertTrue($result->isBot);
        // Bot detection typically doesn't set device type flags
    }

    #[Test]
    public function detectFromUserAgentReturnsDeviceInfoForAndroid(): void
    {
        $deviceDetector = new DeviceDetector();
        $service = new DeviceDetectionService($deviceDetector);

        $result = $service->detectFromUserAgent(self::ANDROID_UA);

        self::assertInstanceOf(DeviceInfo::class, $result);
        self::assertTrue($result->isMobile);
        self::assertFalse($result->isTablet);
        self::assertFalse($result->isDesktop);
        self::assertSame('Android', $result->osName);
        self::assertSame('Samsung', $result->deviceBrand);
    }

    #[Test]
    public function detectFromUserAgentReturnsNullForEmptyUserAgent(): void
    {
        $deviceDetector = new DeviceDetector();
        $service = new DeviceDetectionService($deviceDetector);

        $result = $service->detectFromUserAgent('');

        self::assertNull($result);
    }

    #[Test]
    public function detectFromRequestExtractsUserAgentFromRequest(): void
    {
        $deviceDetector = new DeviceDetector();
        $service = new DeviceDetectionService($deviceDetector);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')
            ->with('User-Agent')
            ->willReturn(self::IPHONE_UA);

        $result = $service->detectFromRequest($request);

        self::assertInstanceOf(DeviceInfo::class, $result);
        self::assertTrue($result->isMobile);
        self::assertSame('Apple', $result->deviceBrand);
    }

    #[Test]
    public function detectFromRequestReturnsNullWhenNoUserAgentHeader(): void
    {
        $deviceDetector = new DeviceDetector();
        $service = new DeviceDetectionService($deviceDetector);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')
            ->with('User-Agent')
            ->willReturn('');

        $result = $service->detectFromRequest($request);

        self::assertNull($result);
    }

    #[Test]
    public function detectForCurrentRequestUsesGlobalTYPO3Request(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')
            ->with('User-Agent')
            ->willReturn(self::CHROME_DESKTOP_UA);

        $GLOBALS['TYPO3_REQUEST'] = $request;

        try {
            $deviceDetector = new DeviceDetector();
            $service = new DeviceDetectionService($deviceDetector);

            $result = $service->detectForCurrentRequest();

            self::assertInstanceOf(DeviceInfo::class, $result);
            self::assertTrue($result->isDesktop);
        } finally {
            unset($GLOBALS['TYPO3_REQUEST']);
        }
    }

    #[Test]
    public function detectForCurrentRequestReturnsNullWhenNoGlobalRequest(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);

        $deviceDetector = new DeviceDetector();
        $service = new DeviceDetectionService($deviceDetector);

        $result = $service->detectForCurrentRequest();

        self::assertNull($result);
    }

    #[Test]
    public function detectFromUserAgentCachesResultsForSameUserAgent(): void
    {
        $deviceDetector = new DeviceDetector();
        $service = new DeviceDetectionService($deviceDetector);

        // Call twice with same UA
        $result1 = $service->detectFromUserAgent(self::CHROME_DESKTOP_UA);
        $result2 = $service->detectFromUserAgent(self::CHROME_DESKTOP_UA);

        // Results should be the same instance (cached)
        self::assertSame($result1, $result2);
    }

    #[Test]
    public function detectFromUserAgentReturnsDifferentInstancesForDifferentUserAgents(): void
    {
        $deviceDetector = new DeviceDetector();
        $service = new DeviceDetectionService($deviceDetector);

        $result1 = $service->detectFromUserAgent(self::CHROME_DESKTOP_UA);
        $result2 = $service->detectFromUserAgent(self::IPHONE_UA);

        self::assertNotSame($result1, $result2);
        self::assertNotNull($result1);
        self::assertNotNull($result2);
        self::assertTrue($result1->isDesktop);
        self::assertTrue($result2->isMobile);
    }

    #[Test]
    #[DataProvider('userAgentDataProvider')]
    public function detectFromUserAgentHandlesVariousUserAgents(
        string $userAgent,
        bool $expectedIsMobile,
        bool $expectedIsTablet,
        bool $expectedIsDesktop,
        bool $expectedIsBot,
    ): void {
        $deviceDetector = new DeviceDetector();
        $service = new DeviceDetectionService($deviceDetector);

        $result = $service->detectFromUserAgent($userAgent);

        self::assertInstanceOf(DeviceInfo::class, $result);
        self::assertSame($expectedIsMobile, $result->isMobile, 'isMobile mismatch');
        self::assertSame($expectedIsTablet, $result->isTablet, 'isTablet mismatch');
        self::assertSame($expectedIsDesktop, $result->isDesktop, 'isDesktop mismatch');
        self::assertSame($expectedIsBot, $result->isBot, 'isBot mismatch');
    }

    #[Test]
    public function clearCacheRemovesCachedResults(): void
    {
        $deviceDetector = new DeviceDetector();
        $service = new DeviceDetectionService($deviceDetector);

        // First call - should cache the result
        $result1 = $service->detectFromUserAgent(self::CHROME_DESKTOP_UA);

        // Clear cache
        $service->clearCache();

        // Second call - should create a new instance
        $result2 = $service->detectFromUserAgent(self::CHROME_DESKTOP_UA);

        // Results should NOT be the same instance after cache clear
        self::assertNotSame($result1, $result2);
        self::assertNotNull($result1);
        self::assertNotNull($result2);

        // But the values should be equivalent
        self::assertSame($result1->isDesktop, $result2->isDesktop);
        self::assertSame($result1->browserName, $result2->browserName);
    }

    #[Test]
    public function detectFromUserAgentExtractsBrowserVersion(): void
    {
        $deviceDetector = new DeviceDetector();
        $service = new DeviceDetectionService($deviceDetector);

        $result = $service->detectFromUserAgent(self::CHROME_DESKTOP_UA);

        self::assertNotNull($result);
        self::assertNotNull($result->browserVersion);
        self::assertMatchesRegularExpression('/^\d+(\.\d+)*$/', $result->browserVersion);
    }

    #[Test]
    public function detectFromUserAgentExtractsOsVersion(): void
    {
        $deviceDetector = new DeviceDetector();
        $service = new DeviceDetectionService($deviceDetector);

        $result = $service->detectFromUserAgent(self::IPHONE_UA);

        self::assertNotNull($result);
        self::assertNotNull($result->osVersion);
        // iOS versions are like 17.2
        self::assertMatchesRegularExpression('/^\d+(\.\d+)*$/', $result->osVersion);
    }

    #[Test]
    public function detectFromUserAgentExtractsDeviceModel(): void
    {
        $deviceDetector = new DeviceDetector();
        $service = new DeviceDetectionService($deviceDetector);

        $result = $service->detectFromUserAgent(self::ANDROID_UA);

        self::assertNotNull($result);
        self::assertNotNull($result->deviceModel);
        // Samsung Galaxy models usually contain the model number
        self::assertNotEmpty($result->deviceModel);
    }
}
