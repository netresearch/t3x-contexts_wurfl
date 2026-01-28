<?php

/**
 * This file is part of the package netresearch/contexts-wurfl.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Netresearch\ContextsDevice\Tests\Functional\Context\Type;

use Netresearch\Contexts\Context\Container;
use Netresearch\ContextsDevice\Context\Type\BrowserContext;
use Netresearch\ContextsDevice\Dto\DeviceInfo;
use Netresearch\ContextsDevice\Service\DeviceDetectionService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Functional tests for BrowserContext.
 *
 * Tests that context types can be loaded from database and match correctly
 * based on visitor browser detection data.
 */
#[CoversClass(BrowserContext::class)]
final class BrowserContextTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'netresearch/contexts',
        'netresearch/contexts-wurfl',
    ];

    /**
     * @var array<string, mixed>
     */
    private array $originalServer = [];

    protected function setUp(): void
    {
        parent::setUp();

        Container::reset();

        // Backup original $_SERVER values we'll modify
        $this->originalServer = [
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? null,
            'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? null,
            'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ];

        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/tx_contexts_contexts.csv');
    }

    protected function tearDown(): void
    {
        Container::reset();
        unset($GLOBALS['TYPO3_REQUEST']);

        // Restore original $_SERVER values
        foreach ($this->originalServer as $key => $value) {
            if ($value === null) {
                unset($_SERVER[$key]);
            } else {
                $_SERVER[$key] = $value;
            }
        }

        parent::tearDown();
    }

    #[Test]
    public function browserContextCanBeLoadedFromDatabase(): void
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'Test Agent';

        $request = new ServerRequest('http://localhost/', 'GET');
        $request = $request->withHeader('User-Agent', 'Test Agent');
        $GLOBALS['TYPO3_REQUEST'] = $request;

        Container::get()
            ->setRequest($request)
            ->initMatching();

        // UID 6 is "Chrome Browser Context" from fixture
        $context = Container::get()->find(6);

        self::assertNotNull($context, 'Context with UID 6 should exist');
        self::assertSame('browser', $context->getType());
        self::assertSame('Chrome Browser Context', $context->getTitle());
        self::assertSame('chrome_browser', $context->getAlias());
    }

    #[Test]
    public function browserContextCanBeFoundByAlias(): void
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'Test Agent';

        $request = new ServerRequest('http://localhost/', 'GET');
        $request = $request->withHeader('User-Agent', 'Test Agent');
        $GLOBALS['TYPO3_REQUEST'] = $request;

        Container::get()
            ->setRequest($request)
            ->initMatching();

        $context = Container::get()->find('chrome_browser');

        self::assertNotNull($context, 'Context with alias "chrome_browser" should exist');
        self::assertSame(6, $context->getUid());
    }

    #[Test]
    public function contextTypeIsBrowser(): void
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'Test Agent';

        $request = new ServerRequest('http://localhost/', 'GET');
        $request = $request->withHeader('User-Agent', 'Test Agent');
        $GLOBALS['TYPO3_REQUEST'] = $request;

        Container::get()
            ->setRequest($request)
            ->initMatching();

        $context = Container::get()->find(6);

        self::assertNotNull($context);
        self::assertInstanceOf(BrowserContext::class, $context);
    }

    #[Test]
    public function browserContextMatchesChrome(): void
    {
        // Create a mock detection service that returns Chrome
        $service = $this->createMock(DeviceDetectionService::class);
        $service->method('detectFromRequest')
            ->willReturn(new DeviceInfo(
                isMobile: false,
                isTablet: false,
                isDesktop: true,
                isBot: false,
                browserName: 'Chrome',
                browserVersion: '120.0',
                osName: 'Windows',
                osVersion: '11',
                deviceBrand: null,
                deviceModel: null,
            ));

        // Create context configured for Chrome
        $row = [
            'uid' => 100,
            'type' => 'browser',
            'title' => 'Test Chrome',
            'alias' => 'test_chrome',
            'tstamp' => time(),
            'invert' => 0,
            'use_session' => 0,
            'disabled' => 0,
            'hide_in_backend' => 0,
            'type_conf' => '<?xml version="1.0" encoding="utf-8"?><T3FlexForms><data><sheet index="sDEF"><language index="lDEF"><field index="field_browsers"><value index="vDEF">Chrome</value></field></language></sheet></data></T3FlexForms>',
        ];

        $context = new BrowserContext($row, $service);

        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $request = new ServerRequest('http://localhost/', 'GET');
        $request = $request->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0');
        $GLOBALS['TYPO3_REQUEST'] = $request;

        self::assertTrue(
            $context->match(),
            'Browser context should match when visitor is using Chrome',
        );
    }

    #[Test]
    public function browserContextMatchesFirefox(): void
    {
        // Create a mock detection service that returns Firefox
        $service = $this->createMock(DeviceDetectionService::class);
        $service->method('detectFromRequest')
            ->willReturn(new DeviceInfo(
                isMobile: false,
                isTablet: false,
                isDesktop: true,
                isBot: false,
                browserName: 'Firefox',
                browserVersion: '121.0',
                osName: 'Linux',
                osVersion: null,
                deviceBrand: null,
                deviceModel: null,
            ));

        // Create context configured for Firefox
        $row = [
            'uid' => 100,
            'type' => 'browser',
            'title' => 'Test Firefox',
            'alias' => 'test_firefox',
            'tstamp' => time(),
            'invert' => 0,
            'use_session' => 0,
            'disabled' => 0,
            'hide_in_backend' => 0,
            'type_conf' => '<?xml version="1.0" encoding="utf-8"?><T3FlexForms><data><sheet index="sDEF"><language index="lDEF"><field index="field_browsers"><value index="vDEF">Firefox</value></field></language></sheet></data></T3FlexForms>',
        ];

        $context = new BrowserContext($row, $service);

        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $request = new ServerRequest('http://localhost/', 'GET');
        $request = $request->withHeader('User-Agent', 'Mozilla/5.0 (X11; Linux x86_64; rv:121.0) Gecko/20100101 Firefox/121.0');
        $GLOBALS['TYPO3_REQUEST'] = $request;

        self::assertTrue(
            $context->match(),
            'Browser context should match when visitor is using Firefox',
        );
    }

    #[Test]
    public function browserContextMatchesMultipleBrowsers(): void
    {
        // Create a mock detection service that returns Safari
        $service = $this->createMock(DeviceDetectionService::class);
        $service->method('detectFromRequest')
            ->willReturn(new DeviceInfo(
                isMobile: false,
                isTablet: false,
                isDesktop: true,
                isBot: false,
                browserName: 'Safari',
                browserVersion: '17.2',
                osName: 'macOS',
                osVersion: '14.2',
                deviceBrand: 'Apple',
                deviceModel: 'MacBook Pro',
            ));

        // Create context configured for multiple browsers: Chrome, Firefox, Safari
        $row = [
            'uid' => 100,
            'type' => 'browser',
            'title' => 'Test Multiple',
            'alias' => 'test_multiple',
            'tstamp' => time(),
            'invert' => 0,
            'use_session' => 0,
            'disabled' => 0,
            'hide_in_backend' => 0,
            'type_conf' => '<?xml version="1.0" encoding="utf-8"?><T3FlexForms><data><sheet index="sDEF"><language index="lDEF"><field index="field_browsers"><value index="vDEF">Chrome,Firefox,Safari</value></field></language></sheet></data></T3FlexForms>',
        ];

        $context = new BrowserContext($row, $service);

        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $request = new ServerRequest('http://localhost/', 'GET');
        $request = $request->withHeader('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_2) AppleWebKit/605.1.15 Safari/17.2');
        $GLOBALS['TYPO3_REQUEST'] = $request;

        self::assertTrue(
            $context->match(),
            'Browser context should match when visitor browser (Safari) is in list (Chrome,Firefox,Safari)',
        );
    }

    #[Test]
    public function browserContextDoesNotMatchDifferentBrowser(): void
    {
        // Create a mock detection service that returns Edge
        $service = $this->createMock(DeviceDetectionService::class);
        $service->method('detectFromRequest')
            ->willReturn(new DeviceInfo(
                isMobile: false,
                isTablet: false,
                isDesktop: true,
                isBot: false,
                browserName: 'Microsoft Edge',
                browserVersion: '120.0',
                osName: 'Windows',
                osVersion: '11',
                deviceBrand: null,
                deviceModel: null,
            ));

        // Create context configured for Chrome only
        $row = [
            'uid' => 100,
            'type' => 'browser',
            'title' => 'Test Chrome Only',
            'alias' => 'test_chrome_only',
            'tstamp' => time(),
            'invert' => 0,
            'use_session' => 0,
            'disabled' => 0,
            'hide_in_backend' => 0,
            'type_conf' => '<?xml version="1.0" encoding="utf-8"?><T3FlexForms><data><sheet index="sDEF"><language index="lDEF"><field index="field_browsers"><value index="vDEF">Chrome</value></field></language></sheet></data></T3FlexForms>',
        ];

        $context = new BrowserContext($row, $service);

        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $request = new ServerRequest('http://localhost/', 'GET');
        $request = $request->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Edge/120.0');
        $GLOBALS['TYPO3_REQUEST'] = $request;

        self::assertFalse(
            $context->match(),
            'Browser context should not match when visitor browser (Edge) is not in configured list (Chrome)',
        );
    }

    #[Test]
    public function browserContextIsCaseInsensitive(): void
    {
        // Create a mock detection service that returns "CHROME" (uppercase)
        $service = $this->createMock(DeviceDetectionService::class);
        $service->method('detectFromRequest')
            ->willReturn(new DeviceInfo(
                isMobile: false,
                isTablet: false,
                isDesktop: true,
                isBot: false,
                browserName: 'CHROME', // Uppercase to test case insensitivity
                browserVersion: '120.0',
                osName: 'Windows',
                osVersion: '11',
                deviceBrand: null,
                deviceModel: null,
            ));

        // Create context configured for "chrome" (lowercase)
        $row = [
            'uid' => 100,
            'type' => 'browser',
            'title' => 'Test Case',
            'alias' => 'test_case',
            'tstamp' => time(),
            'invert' => 0,
            'use_session' => 0,
            'disabled' => 0,
            'hide_in_backend' => 0,
            'type_conf' => '<?xml version="1.0" encoding="utf-8"?><T3FlexForms><data><sheet index="sDEF"><language index="lDEF"><field index="field_browsers"><value index="vDEF">chrome</value></field></language></sheet></data></T3FlexForms>',
        ];

        $context = new BrowserContext($row, $service);

        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $request = new ServerRequest('http://localhost/', 'GET');
        $request = $request->withHeader('User-Agent', 'Test');
        $GLOBALS['TYPO3_REQUEST'] = $request;

        self::assertTrue(
            $context->match(),
            'Browser context matching should be case insensitive',
        );
    }

    #[Test]
    public function browserContextWithEmptyConfigurationDoesNotMatch(): void
    {
        // Create a mock detection service
        $service = $this->createMock(DeviceDetectionService::class);
        $service->method('detectFromRequest')
            ->willReturn(new DeviceInfo(
                isMobile: false,
                isTablet: false,
                isDesktop: true,
                isBot: false,
                browserName: 'Chrome',
                browserVersion: '120.0',
                osName: 'Windows',
                osVersion: '11',
                deviceBrand: null,
                deviceModel: null,
            ));

        // Create context with empty browser configuration
        $row = [
            'uid' => 100,
            'type' => 'browser',
            'title' => 'Test Empty',
            'alias' => 'test_empty',
            'tstamp' => time(),
            'invert' => 0,
            'use_session' => 0,
            'disabled' => 0,
            'hide_in_backend' => 0,
            'type_conf' => '<?xml version="1.0" encoding="utf-8"?><T3FlexForms><data><sheet index="sDEF"><language index="lDEF"><field index="field_browsers"><value index="vDEF"></value></field></language></sheet></data></T3FlexForms>',
        ];

        $context = new BrowserContext($row, $service);

        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $request = new ServerRequest('http://localhost/', 'GET');
        $request = $request->withHeader('User-Agent', 'Test');
        $GLOBALS['TYPO3_REQUEST'] = $request;

        self::assertFalse(
            $context->match(),
            'Browser context with empty configuration should not match',
        );
    }

    #[Test]
    public function browserContextWithNoBrowserDetectedDoesNotMatch(): void
    {
        // Create a mock detection service that returns no browser name
        $service = $this->createMock(DeviceDetectionService::class);
        $service->method('detectFromRequest')
            ->willReturn(new DeviceInfo(
                isMobile: false,
                isTablet: false,
                isDesktop: false,
                isBot: true,
                browserName: null, // No browser detected
                browserVersion: null,
                osName: null,
                osVersion: null,
                deviceBrand: null,
                deviceModel: null,
            ));

        // Create context configured for Chrome
        $row = [
            'uid' => 100,
            'type' => 'browser',
            'title' => 'Test No Browser',
            'alias' => 'test_no_browser',
            'tstamp' => time(),
            'invert' => 0,
            'use_session' => 0,
            'disabled' => 0,
            'hide_in_backend' => 0,
            'type_conf' => '<?xml version="1.0" encoding="utf-8"?><T3FlexForms><data><sheet index="sDEF"><language index="lDEF"><field index="field_browsers"><value index="vDEF">Chrome</value></field></language></sheet></data></T3FlexForms>',
        ];

        $context = new BrowserContext($row, $service);

        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $request = new ServerRequest('http://localhost/', 'GET');
        $request = $request->withHeader('User-Agent', 'curl/7.88.1');
        $GLOBALS['TYPO3_REQUEST'] = $request;

        self::assertFalse(
            $context->match(),
            'Browser context should not match when no browser is detected',
        );
    }
}
