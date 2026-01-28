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
use Netresearch\ContextsDevice\Context\Type\DeviceContext;
use Netresearch\ContextsDevice\Dto\DeviceInfo;
use Netresearch\ContextsDevice\Service\DeviceDetectionService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Functional tests for DeviceContext.
 *
 * Tests that context types can be loaded from database and match correctly
 * based on visitor device detection data.
 */
#[CoversClass(DeviceContext::class)]
final class DeviceContextTest extends FunctionalTestCase
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
    public function deviceContextCanBeLoadedFromDatabase(): void
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

        // UID 1 is "Mobile Device Context" from fixture
        $context = Container::get()->find(1);

        self::assertNotNull($context, 'Context with UID 1 should exist');
        self::assertSame('device', $context->getType());
        self::assertSame('Mobile Device Context', $context->getTitle());
        self::assertSame('mobile_device', $context->getAlias());
    }

    #[Test]
    public function deviceContextCanBeFoundByAlias(): void
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

        $context = Container::get()->find('mobile_device');

        self::assertNotNull($context, 'Context with alias "mobile_device" should exist');
        self::assertSame(1, $context->getUid());
    }

    #[Test]
    public function multipleDeviceContextsCanBeLoaded(): void
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

        // Check that multiple contexts are loaded from fixtures
        $mobile = Container::get()->find('mobile_device');
        $desktop = Container::get()->find('desktop_device');
        $tablet = Container::get()->find('tablet_device');

        self::assertNotNull($mobile, 'Mobile device context should exist');
        self::assertNotNull($desktop, 'Desktop device context should exist');
        self::assertNotNull($tablet, 'Tablet device context should exist');

        self::assertSame(1, $mobile->getUid());
        self::assertSame(2, $desktop->getUid());
        self::assertSame(3, $tablet->getUid());
    }

    #[Test]
    public function disabledContextIsNotLoaded(): void
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

        // UID 5 is disabled in fixture
        $context = Container::get()->find(5);

        self::assertNull($context, 'Disabled context should not be loadable');
    }

    #[Test]
    public function contextTypeIsDevice(): void
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

        $context = Container::get()->find(1);

        self::assertNotNull($context);
        self::assertInstanceOf(DeviceContext::class, $context);
    }

    #[Test]
    public function deviceContextMatchesMobileDevice(): void
    {
        // Create a mock detection service that returns a mobile device
        $service = $this->createMock(DeviceDetectionService::class);
        $service->method('detectFromRequest')
            ->willReturn(new DeviceInfo(
                isMobile: true,
                isTablet: false,
                isDesktop: false,
                isBot: false,
                browserName: 'Chrome Mobile',
                browserVersion: '120.0',
                osName: 'Android',
                osVersion: '14.0',
                deviceBrand: 'Samsung',
                deviceModel: 'Galaxy S24',
            ));

        // Create context directly with mocked service
        $row = [
            'uid' => 100,
            'type' => 'device',
            'title' => 'Test Mobile',
            'alias' => 'test_mobile',
            'tstamp' => time(),
            'invert' => 0,
            'use_session' => 0,
            'disabled' => 0,
            'hide_in_backend' => 0,
            'type_conf' => '<?xml version="1.0" encoding="utf-8"?><T3FlexForms><data><sheet index="sDEF"><language index="lDEF"><field index="field_is_mobile"><value index="vDEF">1</value></field><field index="field_is_phone"><value index="vDEF">0</value></field><field index="field_is_tablet"><value index="vDEF">0</value></field><field index="field_is_desktop"><value index="vDEF">0</value></field><field index="field_is_bot"><value index="vDEF">0</value></field></language></sheet></data></T3FlexForms>',
        ];

        $context = new DeviceContext($row, $service);

        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $request = new ServerRequest('http://localhost/', 'GET');
        $request = $request->withHeader('User-Agent', 'Mozilla/5.0 (Linux; Android 14; SM-S921B) AppleWebKit/537.36');
        $GLOBALS['TYPO3_REQUEST'] = $request;

        self::assertTrue(
            $context->match(),
            'Device context should match when visitor is on mobile device',
        );
    }

    #[Test]
    public function deviceContextMatchesDesktopDevice(): void
    {
        // Create a mock detection service that returns a desktop device
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

        // Create context configured for desktop
        $row = [
            'uid' => 100,
            'type' => 'device',
            'title' => 'Test Desktop',
            'alias' => 'test_desktop',
            'tstamp' => time(),
            'invert' => 0,
            'use_session' => 0,
            'disabled' => 0,
            'hide_in_backend' => 0,
            'type_conf' => '<?xml version="1.0" encoding="utf-8"?><T3FlexForms><data><sheet index="sDEF"><language index="lDEF"><field index="field_is_mobile"><value index="vDEF">0</value></field><field index="field_is_phone"><value index="vDEF">0</value></field><field index="field_is_tablet"><value index="vDEF">0</value></field><field index="field_is_desktop"><value index="vDEF">1</value></field><field index="field_is_bot"><value index="vDEF">0</value></field></language></sheet></data></T3FlexForms>',
        ];

        $context = new DeviceContext($row, $service);

        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $request = new ServerRequest('http://localhost/', 'GET');
        $request = $request->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        $GLOBALS['TYPO3_REQUEST'] = $request;

        self::assertTrue(
            $context->match(),
            'Device context should match when visitor is on desktop device',
        );
    }

    #[Test]
    public function deviceContextDoesNotMatchWhenDeviceTypeDiffers(): void
    {
        // Create a mock detection service that returns a desktop device
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

        // Create context configured for mobile (but visitor is on desktop)
        $row = [
            'uid' => 100,
            'type' => 'device',
            'title' => 'Test Mobile',
            'alias' => 'test_mobile',
            'tstamp' => time(),
            'invert' => 0,
            'use_session' => 0,
            'disabled' => 0,
            'hide_in_backend' => 0,
            'type_conf' => '<?xml version="1.0" encoding="utf-8"?><T3FlexForms><data><sheet index="sDEF"><language index="lDEF"><field index="field_is_mobile"><value index="vDEF">1</value></field><field index="field_is_phone"><value index="vDEF">0</value></field><field index="field_is_tablet"><value index="vDEF">0</value></field><field index="field_is_desktop"><value index="vDEF">0</value></field><field index="field_is_bot"><value index="vDEF">0</value></field></language></sheet></data></T3FlexForms>',
        ];

        $context = new DeviceContext($row, $service);

        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $request = new ServerRequest('http://localhost/', 'GET');
        $request = $request->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        $GLOBALS['TYPO3_REQUEST'] = $request;

        self::assertFalse(
            $context->match(),
            'Device context should not match when visitor device type differs from configured type',
        );
    }

    #[Test]
    public function invertedDeviceContextInvertsMatchResult(): void
    {
        // Create a mock detection service that returns a mobile device
        $service = $this->createMock(DeviceDetectionService::class);
        $service->method('detectFromRequest')
            ->willReturn(new DeviceInfo(
                isMobile: true,
                isTablet: false,
                isDesktop: false,
                isBot: false,
                browserName: 'Chrome Mobile',
                browserVersion: '120.0',
                osName: 'Android',
                osVersion: '14.0',
                deviceBrand: 'Samsung',
                deviceModel: 'Galaxy S24',
            ));

        // Create inverted context configured for mobile
        $row = [
            'uid' => 100,
            'type' => 'device',
            'title' => 'Test Inverted',
            'alias' => 'test_inverted',
            'tstamp' => time(),
            'invert' => 1, // Inverted!
            'use_session' => 0,
            'disabled' => 0,
            'hide_in_backend' => 0,
            'type_conf' => '<?xml version="1.0" encoding="utf-8"?><T3FlexForms><data><sheet index="sDEF"><language index="lDEF"><field index="field_is_mobile"><value index="vDEF">1</value></field><field index="field_is_phone"><value index="vDEF">0</value></field><field index="field_is_tablet"><value index="vDEF">0</value></field><field index="field_is_desktop"><value index="vDEF">0</value></field><field index="field_is_bot"><value index="vDEF">0</value></field></language></sheet></data></T3FlexForms>',
        ];

        $context = new DeviceContext($row, $service);

        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $request = new ServerRequest('http://localhost/', 'GET');
        $request = $request->withHeader('User-Agent', 'Mozilla/5.0 (Linux; Android 14; SM-S921B) AppleWebKit/537.36');
        $GLOBALS['TYPO3_REQUEST'] = $request;

        // Normal match would be true (mobile configured, mobile detected), but inverted should be false
        self::assertFalse(
            $context->match(),
            'Inverted device context should return false when device type matches',
        );
    }

    #[Test]
    public function deviceContextMatchesBot(): void
    {
        // Create a mock detection service that returns a bot
        $service = $this->createMock(DeviceDetectionService::class);
        $service->method('detectFromRequest')
            ->willReturn(new DeviceInfo(
                isMobile: false,
                isTablet: false,
                isDesktop: false,
                isBot: true,
                browserName: 'Googlebot',
                browserVersion: '2.1',
                osName: null,
                osVersion: null,
                deviceBrand: null,
                deviceModel: null,
            ));

        // Create context configured for bots
        $row = [
            'uid' => 100,
            'type' => 'device',
            'title' => 'Test Bot',
            'alias' => 'test_bot',
            'tstamp' => time(),
            'invert' => 0,
            'use_session' => 0,
            'disabled' => 0,
            'hide_in_backend' => 0,
            'type_conf' => '<?xml version="1.0" encoding="utf-8"?><T3FlexForms><data><sheet index="sDEF"><language index="lDEF"><field index="field_is_mobile"><value index="vDEF">0</value></field><field index="field_is_phone"><value index="vDEF">0</value></field><field index="field_is_tablet"><value index="vDEF">0</value></field><field index="field_is_desktop"><value index="vDEF">0</value></field><field index="field_is_bot"><value index="vDEF">1</value></field></language></sheet></data></T3FlexForms>',
        ];

        $context = new DeviceContext($row, $service);

        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REMOTE_ADDR'] = '66.249.66.1'; // Google bot IP

        $request = new ServerRequest('http://localhost/', 'GET');
        $request = $request->withHeader('User-Agent', 'Googlebot/2.1 (+http://www.google.com/bot.html)');
        $GLOBALS['TYPO3_REQUEST'] = $request;

        self::assertTrue(
            $context->match(),
            'Device context should match when visitor is a bot',
        );
    }

    #[Test]
    public function deviceContextWithNoConfigurationDoesNotMatch(): void
    {
        // Create a mock detection service
        $service = $this->createMock(DeviceDetectionService::class);
        $service->method('detectFromRequest')
            ->willReturn(new DeviceInfo(
                isMobile: true,
                isTablet: false,
                isDesktop: false,
                isBot: false,
                browserName: 'Chrome Mobile',
                browserVersion: '120.0',
                osName: 'Android',
                osVersion: '14.0',
                deviceBrand: 'Samsung',
                deviceModel: 'Galaxy S24',
            ));

        // Create context with no device types configured
        $row = [
            'uid' => 100,
            'type' => 'device',
            'title' => 'Test Empty',
            'alias' => 'test_empty',
            'tstamp' => time(),
            'invert' => 0,
            'use_session' => 0,
            'disabled' => 0,
            'hide_in_backend' => 0,
            'type_conf' => '<?xml version="1.0" encoding="utf-8"?><T3FlexForms><data><sheet index="sDEF"><language index="lDEF"><field index="field_is_mobile"><value index="vDEF">0</value></field><field index="field_is_phone"><value index="vDEF">0</value></field><field index="field_is_tablet"><value index="vDEF">0</value></field><field index="field_is_desktop"><value index="vDEF">0</value></field><field index="field_is_bot"><value index="vDEF">0</value></field></language></sheet></data></T3FlexForms>',
        ];

        $context = new DeviceContext($row, $service);

        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $request = new ServerRequest('http://localhost/', 'GET');
        $request = $request->withHeader('User-Agent', 'Mozilla/5.0 (Linux; Android 14; SM-S921B) AppleWebKit/537.36');
        $GLOBALS['TYPO3_REQUEST'] = $request;

        self::assertFalse(
            $context->match(),
            'Device context with no device types configured should not match',
        );
    }
}
