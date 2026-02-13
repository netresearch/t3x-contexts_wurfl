<?php

/**
 * This file is part of the package netresearch/contexts-wurfl.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Netresearch\ContextsDevice\Tests\Unit\Context\Type;

use DeviceDetector\DeviceDetector;
use Netresearch\ContextsDevice\Context\Type\BrowserContext;
use Netresearch\ContextsDevice\Service\DeviceDetectionService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

#[CoversClass(BrowserContext::class)]
final class BrowserContextTest extends TestCase
{
    private const CHROME_DESKTOP_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    private const FIREFOX_DESKTOP_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0';

    private const SAFARI_IPHONE_UA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1';

    private const EDGE_DESKTOP_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0';

    private const OPERA_DESKTOP_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 OPR/106.0.0.0';

    private const GOOGLEBOT_UA = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';

    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);
    }

    /**
     * @return iterable<string, array{string, string, bool}>
     */
    public static function browserMatchDataProvider(): iterable
    {
        // Chrome scenarios
        yield 'Chrome matches Chrome' => [
            self::CHROME_DESKTOP_UA,
            'Chrome',
            true,
        ];
        yield 'Chrome matches Chrome in list' => [
            self::CHROME_DESKTOP_UA,
            'Firefox, Chrome, Safari',
            true,
        ];
        yield 'Chrome does not match Firefox' => [
            self::CHROME_DESKTOP_UA,
            'Firefox',
            false,
        ];

        // Firefox scenarios
        yield 'Firefox matches Firefox' => [
            self::FIREFOX_DESKTOP_UA,
            'Firefox',
            true,
        ];
        yield 'Firefox matches Firefox in list' => [
            self::FIREFOX_DESKTOP_UA,
            'Chrome, Firefox, Safari',
            true,
        ];
        yield 'Firefox does not match Chrome' => [
            self::FIREFOX_DESKTOP_UA,
            'Chrome',
            false,
        ];

        // Safari scenarios (Mobile Safari)
        yield 'Mobile Safari matches Safari' => [
            self::SAFARI_IPHONE_UA,
            'Safari, Mobile Safari',
            true,
        ];

        // Edge scenarios
        yield 'Edge matches Edge' => [
            self::EDGE_DESKTOP_UA,
            'Microsoft Edge',
            true,
        ];
        yield 'Edge matches Edge in list' => [
            self::EDGE_DESKTOP_UA,
            'Chrome, Microsoft Edge, Firefox',
            true,
        ];

        // Opera scenarios
        yield 'Opera matches Opera' => [
            self::OPERA_DESKTOP_UA,
            'Opera',
            true,
        ];
    }

    #[Test]
    public function matchReturnsTrueWhenBrowserIsInConfiguredList(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::CHROME_DESKTOP_UA);

        $context = $this->createTestableBrowserContext(
            service: $service,
            browsers: 'Chrome, Firefox, Safari',
        );

        self::assertTrue($context->match());
    }

    #[Test]
    public function matchReturnsFalseWhenBrowserIsNotInConfiguredList(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::CHROME_DESKTOP_UA);

        $context = $this->createTestableBrowserContext(
            service: $service,
            browsers: 'Firefox, Safari, Opera',
        );

        self::assertFalse($context->match());
    }

    #[Test]
    public function matchIsCaseInsensitive(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::CHROME_DESKTOP_UA);

        $context = $this->createTestableBrowserContext(
            service: $service,
            browsers: 'chrome, FIREFOX, Safari',
        );

        self::assertTrue($context->match());
    }

    #[Test]
    public function matchTrimsWhitespaceFromBrowserNames(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::FIREFOX_DESKTOP_UA);

        $context = $this->createTestableBrowserContext(
            service: $service,
            browsers: '  Chrome  ,   Firefox   ,  Safari  ',
        );

        self::assertTrue($context->match());
    }

    #[Test]
    public function matchReturnsFalseWhenNoBrowsersConfigured(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::CHROME_DESKTOP_UA);

        $context = $this->createTestableBrowserContext(
            service: $service,
            browsers: '',
        );

        self::assertFalse($context->match());
    }

    #[Test]
    public function matchReturnsFalseWhenNoRequestAvailable(): void
    {
        $service = $this->createDeviceDetectionService();
        // No TYPO3_REQUEST set

        $context = $this->createTestableBrowserContext(
            service: $service,
            browsers: 'Chrome, Firefox',
        );

        self::assertFalse($context->match());
    }

    #[Test]
    public function matchReturnsFalseWhenUserAgentIsEmpty(): void
    {
        $service = $this->createDeviceDetectionService();

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')->with('User-Agent')->willReturn('');
        $GLOBALS['TYPO3_REQUEST'] = $request;

        $context = $this->createTestableBrowserContext(
            service: $service,
            browsers: 'Chrome, Firefox',
        );

        self::assertFalse($context->match());
    }

    #[Test]
    public function matchReturnsFalseWhenBrowserCannotBeDetected(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::GOOGLEBOT_UA);

        $context = $this->createTestableBrowserContext(
            service: $service,
            browsers: 'Chrome, Firefox, Safari',
        );

        // Googlebot is detected as a bot, not as a browser
        self::assertFalse($context->match());
    }

    #[Test]
    public function matchReturnsInvertedResultWhenInvertIsTrue(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::CHROME_DESKTOP_UA);

        $context = $this->createTestableBrowserContext(
            service: $service,
            browsers: 'Chrome, Firefox',
            invert: true,
        );

        // Chrome matches, but invert should return false
        self::assertFalse($context->match());
    }

    #[Test]
    public function matchReturnsTrueWhenNoMatchAndInverted(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::CHROME_DESKTOP_UA);

        $context = $this->createTestableBrowserContext(
            service: $service,
            browsers: 'Firefox, Safari',
            invert: true,
        );

        // Chrome doesn't match Firefox/Safari, invert should return true
        self::assertTrue($context->match());
    }

    #[Test]
    public function matchHandlesSingleBrowserInList(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::FIREFOX_DESKTOP_UA);

        $context = $this->createTestableBrowserContext(
            service: $service,
            browsers: 'Firefox',
        );

        self::assertTrue($context->match());
    }

    #[Test]
    #[DataProvider('browserMatchDataProvider')]
    public function matchWorksForVariousBrowsers(
        string $userAgent,
        string $configuredBrowsers,
        bool $expectedMatch,
    ): void {
        $service = $this->createDeviceDetectionService();
        $this->setRequest($userAgent);

        $context = $this->createTestableBrowserContext(
            service: $service,
            browsers: $configuredBrowsers,
        );

        self::assertSame($expectedMatch, $context->match());
    }

    #[Test]
    public function matchIgnoresEmptyEntriesInBrowserList(): void
    {
        $service = $this->createDeviceDetectionService();
        $this->setRequest(self::CHROME_DESKTOP_UA);

        $context = $this->createTestableBrowserContext(
            service: $service,
            browsers: 'Chrome,,Firefox,, ,Safari',
        );

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
     * Create a testable BrowserContext that bypasses TYPO3 dependencies.
     */
    private function createTestableBrowserContext(
        DeviceDetectionService $service,
        string $browsers = '',
        bool $invert = false,
    ): BrowserContext {
        return new class (
            $service,
            $browsers,
            $invert,
        ) extends BrowserContext {
            private string $testBrowsers;

            private bool $testInvert;

            public function __construct(
                DeviceDetectionService $service,
                string $browsers,
                bool $invert,
            ) {
                // Skip parent constructor to avoid TYPO3 dependencies
                $this->deviceDetectionService = $service;
                $this->testBrowsers = $browsers;
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
                    'field_browsers' => $this->testBrowsers,
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

            /**
             * @return array{bool, bool|null}
             */
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
