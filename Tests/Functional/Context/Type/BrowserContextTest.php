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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Functional tests for BrowserContext.
 *
 * Tests that browser context types can be loaded from database
 * and resolved by the Container. Match logic is tested in unit tests.
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
    public function browserContextCanBeLoadedFromDatabase(): void
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'Test Agent';

        $request = new ServerRequest('http://localhost/', 'GET');
        $request = $request->withHeader('User-Agent', 'Test Agent');

        Container::get()
            ->setRequest($request)
            ->initAll();

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

        Container::get()
            ->setRequest($request)
            ->initAll();

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

        Container::get()
            ->setRequest($request)
            ->initAll();

        $context = Container::get()->find(6);

        self::assertNotNull($context);
        self::assertInstanceOf(BrowserContext::class, $context);
    }
}
