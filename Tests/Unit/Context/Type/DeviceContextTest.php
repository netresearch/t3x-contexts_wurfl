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

namespace Netresearch\ContextsDevice\Tests\Unit\Context\Type;

use DeviceDetector\DeviceDetector;
use Netresearch\ContextsDevice\Context\Type\DeviceContext;
use Netresearch\ContextsDevice\Service\DeviceDetectionService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

#[CoversClass(DeviceContext::class)]
final class DeviceContextTest extends TestCase
{
    private const CHROME_DESKTOP_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    private const IPHONE_UA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1';

    private const IPAD_UA = 'Mozilla/5.0 (iPad; CPU OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1';

    private const GOOGLEBOT_UA = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';

    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);
    }

    /**
     * @return iterable<string, array{string, bool, bool, bool, bool, bool, bool}>
     */
    public static function deviceTypeMatchDataProvider(): iterable
    {
        // Desktop user agent scenarios
        yield 'desktop matches desktop' => [
            self::CHROME_DESKTOP_UA,
            false, false, false, true, false,
            true,
        ];
        yield 'desktop matches mobile+desktop' => [
            self::CHROME_DESKTOP_UA,
            true, false, false, true, false,
            true,
        ];
        yield 'desktop does not match mobile only' => [
            self::CHROME_DESKTOP_UA,
            true, false, false, false, false,
            false,
        ];
        yield 'desktop does not match bot only' => [
            self::CHROME_DESKTOP_UA,
            false, false, false, false, true,
            false,
        ];

        // Phone user agent scenarios
        yield 'phone matches mobile' => [
            self::IPHONE_UA,
            true, false, false, false, false,
            true,
        ];
        yield 'phone matches phone' => [
            self::IPHONE_UA,
            false, true, false, false, false,
            true,
        ];
        yield 'phone does not match tablet only' => [
            self::IPHONE_UA,
            false, false, true, false, false,
            false,
        ];
        yield 'phone does not match desktop only' => [
            self::IPHONE_UA,
            false, false, false, true, false,
            false,
        ];

        // Tablet user agent scenarios
        yield 'tablet matches mobile' => [
            self::IPAD_UA,
            true, false, false, false, false,
            true,
        ];
        yield 'tablet matches tablet' => [
            self::IPAD_UA,
            false, false, true, false, false,
            true,
        ];
        yield 'tablet does not match phone only' => [
            self::IPAD_UA,
            false, true, false, false, false,
            false,
        ];
        yield 'tablet does not match desktop only' => [
            self::IPAD_UA,
            false, false, false, true, false,
            false,
        ];

        // Bot user agent scenarios
        yield 'bot matches bot' => [
            self::GOOGLEBOT_UA,
            false, false, false, false, true,
            true,
        ];
        yield 'bot does not match desktop only' => [
            self::GOOGLEBOT_UA,
            false, false, false, true, false,
            false,
        ];
        yield 'bot does not match mobile only' => [
            self::GOOGLEBOT_UA,
            true, false, false, false, false,
            false,
        ];
    }

    #[Test]
    public function matchReturnsTrueWhenDesktopMatchesDesktopConfig(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::CHROME_DESKTOP_UA);

        $context = $this->createTestableDeviceContext(
            service: $service,
            isDesktop: true,
        );

        self::assertTrue($context->match());
    }

    #[Test]
    public function matchReturnsFalseWhenDesktopDoesNotMatchMobileConfig(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::CHROME_DESKTOP_UA);

        $context = $this->createTestableDeviceContext(
            service: $service,
            isMobile: true,
        );

        self::assertFalse($context->match());
    }

    #[Test]
    public function matchReturnsTrueWhenPhoneMatchesMobileConfig(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::IPHONE_UA);

        $context = $this->createTestableDeviceContext(
            service: $service,
            isMobile: true,
        );

        self::assertTrue($context->match());
    }

    #[Test]
    public function matchReturnsTrueWhenPhoneMatchesPhoneConfig(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::IPHONE_UA);

        $context = $this->createTestableDeviceContext(
            service: $service,
            isPhone: true,
        );

        self::assertTrue($context->match());
    }

    #[Test]
    public function matchReturnsFalseWhenTabletMatchesPhoneConfig(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::IPAD_UA);

        $context = $this->createTestableDeviceContext(
            service: $service,
            isPhone: true,
        );

        // iPad is tablet, not phone
        self::assertFalse($context->match());
    }

    #[Test]
    public function matchReturnsTrueWhenTabletMatchesTabletConfig(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::IPAD_UA);

        $context = $this->createTestableDeviceContext(
            service: $service,
            isTablet: true,
        );

        self::assertTrue($context->match());
    }

    #[Test]
    public function matchReturnsTrueWhenTabletMatchesMobileConfig(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::IPAD_UA);

        $context = $this->createTestableDeviceContext(
            service: $service,
            isMobile: true,
        );

        // Tablets are considered mobile
        self::assertTrue($context->match());
    }

    #[Test]
    public function matchReturnsTrueWhenBotMatchesBotConfig(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::GOOGLEBOT_UA);

        $context = $this->createTestableDeviceContext(
            service: $service,
            isBot: true,
        );

        self::assertTrue($context->match());
    }

    #[Test]
    public function matchReturnsFalseWhenBotDoesNotMatchDesktopConfig(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::GOOGLEBOT_UA);

        $context = $this->createTestableDeviceContext(
            service: $service,
            isDesktop: true,
        );

        self::assertFalse($context->match());
    }

    #[Test]
    public function matchReturnsTrueWhenMultipleTypesConfiguredAndOneMatches(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::IPAD_UA);

        $context = $this->createTestableDeviceContext(
            service: $service,
            isDesktop: true,  // Won't match
            isTablet: true,   // Will match
        );

        self::assertTrue($context->match());
    }

    #[Test]
    public function matchReturnsFalseWhenNoTypesConfigured(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::CHROME_DESKTOP_UA);

        $context = $this->createTestableDeviceContext(
            service: $service,
            // Nothing configured
        );

        self::assertFalse($context->match());
    }

    #[Test]
    public function matchReturnsFalseWhenNoRequestAvailable(): void
    {
        $service = $this->createDeviceDetectionService();
        // No TYPO3_REQUEST set

        $context = $this->createTestableDeviceContext(
            service: $service,
            isDesktop: true,
        );

        self::assertFalse($context->match());
    }

    #[Test]
    public function matchReturnsInvertedResultWhenInvertIsTrue(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::CHROME_DESKTOP_UA);

        $context = $this->createTestableDeviceContext(
            service: $service,
            isDesktop: true,
            invert: true,
        );

        // Desktop matches config, but invert should return false
        self::assertFalse($context->match());
    }

    #[Test]
    public function matchReturnsTrueWhenNoMatchAndInverted(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::CHROME_DESKTOP_UA);

        $context = $this->createTestableDeviceContext(
            service: $service,
            isMobile: true,
            invert: true,
        );

        // Desktop doesn't match mobile, invert should return true
        self::assertTrue($context->match());
    }

    #[Test]
    #[DataProvider('deviceTypeMatchDataProvider')]
    public function matchWorksForVariousDeviceTypeConfigurations(
        string $userAgent,
        bool $configMobile,
        bool $configPhone,
        bool $configTablet,
        bool $configDesktop,
        bool $configBot,
        bool $expectedMatch,
    ): void {
        $service = $this->createDeviceDetectionService();
        $this->setRequest($userAgent);

        $context = $this->createTestableDeviceContext(
            service: $service,
            isMobile: $configMobile,
            isPhone: $configPhone,
            isTablet: $configTablet,
            isDesktop: $configDesktop,
            isBot: $configBot,
        );

        self::assertSame($expectedMatch, $context->match());
    }

    #[Test]
    public function matchReturnsFalseWhenEmptyUserAgent(): void
    {
        $service = $this->createDeviceDetectionService();

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')->with('User-Agent')->willReturn('');
        $GLOBALS['TYPO3_REQUEST'] = $request;

        $context = $this->createTestableDeviceContext(
            service: $service,
            isDesktop: true,
        );

        self::assertFalse($context->match());
    }

    #[Test]
    public function matchReturnsTrueWhenPhoneAndTabletConfiguredAndDeviceIsPhone(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::IPHONE_UA);

        $context = $this->createTestableDeviceContext(
            service: $service,
            isPhone: true,
            isTablet: true,
        );

        // Phone matches phone config
        self::assertTrue($context->match());
    }

    #[Test]
    public function matchReturnsTrueWhenPhoneAndTabletConfiguredAndDeviceIsTablet(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::IPAD_UA);

        $context = $this->createTestableDeviceContext(
            service: $service,
            isPhone: true,
            isTablet: true,
        );

        // Tablet matches tablet config
        self::assertTrue($context->match());
    }

    /**
     * Create a real DeviceDetectionService with Matomo DeviceDetector.
     */
    private function createDeviceDetectionService(): DeviceDetectionService
    {
        return new DeviceDetectionService(new DeviceDetector());
    }

    /**
     * Set up a mock request with the given User-Agent.
     */
    private function setRequest(string $userAgent): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')
            ->with('User-Agent')
            ->willReturn($userAgent);
        $GLOBALS['TYPO3_REQUEST'] = $request;
    }

    /**
     * Create a testable DeviceContext that bypasses TYPO3 dependencies.
     */
    private function createTestableDeviceContext(
        DeviceDetectionService $service,
        bool $isMobile = false,
        bool $isPhone = false,
        bool $isTablet = false,
        bool $isDesktop = false,
        bool $isBot = false,
        bool $invert = false,
    ): DeviceContext {
        return new class (
            $service,
            $isMobile,
            $isPhone,
            $isTablet,
            $isDesktop,
            $isBot,
            $invert,
        ) extends DeviceContext {
            private bool $testIsMobile;

            private bool $testIsPhone;

            private bool $testIsTablet;

            private bool $testIsDesktop;

            private bool $testIsBot;

            private bool $testInvert;

            public function __construct(
                DeviceDetectionService $service,
                bool $isMobile,
                bool $isPhone,
                bool $isTablet,
                bool $isDesktop,
                bool $isBot,
                bool $invert,
            ) {
                // Skip parent constructor to avoid TYPO3 dependencies
                $this->deviceDetectionService = $service;
                $this->testIsMobile = $isMobile;
                $this->testIsPhone = $isPhone;
                $this->testIsTablet = $isTablet;
                $this->testIsDesktop = $isDesktop;
                $this->testIsBot = $isBot;
                $this->testInvert = $invert;
                $this->use_session = false;
            }

            protected function getConfValue(
                string $fieldName,
                string $default = '',
                string $sheet = 'sDEF',
                string $lang = 'lDEF',
                string $value = 'vDEF',
            ): string {
                return match ($fieldName) {
                    'field_is_mobile' => $this->testIsMobile ? '1' : '0',
                    'field_is_phone' => $this->testIsPhone ? '1' : '0',
                    'field_is_tablet' => $this->testIsTablet ? '1' : '0',
                    'field_is_desktop' => $this->testIsDesktop ? '1' : '0',
                    'field_is_bot' => $this->testIsBot ? '1' : '0',
                    default => $default,
                };
            }

            protected function invert(bool $bMatch): bool
            {
                if ($this->testInvert) {
                    return !$bMatch;
                }

                return $bMatch;
            }

            protected function getMatchFromSession(): array
            {
                return [false, null];
            }

            protected function storeInSession(bool $bMatch): bool
            {
                return $bMatch;
            }
        };
    }
}
