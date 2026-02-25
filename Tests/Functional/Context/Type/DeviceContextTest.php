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

namespace Netresearch\ContextsDevice\Tests\Functional\Context\Type;

use Netresearch\Contexts\Context\Container;
use Netresearch\ContextsDevice\Context\Type\DeviceContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Functional tests for DeviceContext.
 *
 * Tests that device context types can be loaded from database
 * and resolved by the Container. Match logic is tested in unit tests.
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

        // Ensure TYPO3_REQUEST is NOT set so ContextRestriction short-circuits
        // (avoids ApplicationType::fromRequest() which requires applicationType attribute)
        unset($GLOBALS['TYPO3_REQUEST']);

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

        Container::get()
            ->setRequest($request)
            ->initAll();

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

        Container::get()
            ->setRequest($request)
            ->initAll();

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

        Container::get()
            ->setRequest($request)
            ->initAll();

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

        Container::get()
            ->setRequest($request)
            ->initAll();

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

        Container::get()
            ->setRequest($request)
            ->initAll();

        $context = Container::get()->find(1);

        self::assertNotNull($context);
        self::assertInstanceOf(DeviceContext::class, $context);
    }
}
