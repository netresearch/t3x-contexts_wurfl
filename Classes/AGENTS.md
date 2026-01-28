<!-- Managed by agent: keep sections & order; edit content, not structure. Last updated: 2026-01-28 -->

# AGENTS.md - Classes/

Backend PHP code for the Contexts Device extension (formerly contexts_wurfl).

> **Important:** This documents TARGET patterns for the v12/v13 rewrite.
> Current legacy code uses WURFL library with PHP 5.x patterns - do NOT follow them.
> The rewrite uses `matomo/device-detector` library.

## Overview

This directory contains the core PHP implementation:
- **Context/Type/**: Device detection context types (Device, Browser)
- **Service/**: Device detection service (wraps Matomo DeviceDetector)
- **Dto/**: Value objects for device data
- **Exception/**: Custom exceptions

## Setup & Environment

```bash
composer install
ddev start && ddev install-v13
```

## Build & Tests

```bash
composer lint              # PHP_CodeSniffer
composer analyze           # PHPStan level 8
composer test:unit         # Unit tests for this code
```

## Code Style & Conventions

### PSR-12 + TYPO3 CGL

- Strict types: `declare(strict_types=1);`
- Final classes by default (unless designed for extension)
- Constructor property promotion where applicable
- Return types on all methods

### Namespace Pattern

```php
namespace Netresearch\ContextsDevice\Context\Type;
namespace Netresearch\ContextsDevice\Service;
namespace Netresearch\ContextsDevice\Dto;
```

### Dependency Injection

Prefer constructor injection via `Services.yaml`:

```php
public function __construct(
    private readonly DeviceDetectionService $deviceDetectionService,
) {}
```

## Extension-Specific Patterns

### DeviceInfo DTO

```php
<?php
declare(strict_types=1);

namespace Netresearch\ContextsDevice\Dto;

/**
 * Immutable value object for device detection results.
 *
 * Note: Screen dimensions are NOT available with Matomo DeviceDetector.
 * This is a known limitation compared to WURFL.
 */
final readonly class DeviceInfo
{
    public function __construct(
        // Device classification
        public bool $isMobile,
        public bool $isTablet,
        public bool $isDesktop,
        public bool $isTv,
        public bool $isBot,

        // Device details
        public ?string $deviceType = null,   // "smartphone", "tablet", "desktop", etc.
        public ?string $brandName = null,    // "Apple", "Samsung", "Google"
        public ?string $modelName = null,    // "iPhone 15 Pro", "Galaxy S24"

        // Operating system
        public ?string $osName = null,       // "iOS", "Android", "Windows"
        public ?string $osVersion = null,    // "17.2", "14", "11"
        public ?string $osFamily = null,     // "iOS", "Android", "Windows"

        // Browser/Client
        public ?string $browserName = null,  // "Chrome", "Safari", "Firefox"
        public ?string $browserVersion = null, // "120.0.0.0"
        public ?string $browserEngine = null,  // "Blink", "WebKit", "Gecko"

        // Bot info (if isBot)
        public ?string $botName = null,
        public ?string $botCategory = null,  // "Search bot", "Crawler"
    ) {}

    /**
     * Check if device is a phone (mobile but not tablet).
     */
    public function isPhone(): bool
    {
        return $this->isMobile && !$this->isTablet;
    }

    /**
     * Check if device is any mobile device (phone or tablet).
     *
     * This replaces WURFL's is_wireless_device capability.
     */
    public function isWireless(): bool
    {
        return $this->isMobile;
    }
}
```

### DeviceDetectionService

```php
<?php
declare(strict_types=1);

namespace Netresearch\ContextsDevice\Service;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Client\Browser;
use Netresearch\ContextsDevice\Dto\DeviceInfo;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ServerRequestFactory;

final class DeviceDetectionService
{
    private ?DeviceInfo $cachedResult = null;
    private ?string $cachedUserAgent = null;

    public function __construct(
        private readonly bool $treatBotsAsDesktop = true,
    ) {}

    /**
     * Detect device from the current TYPO3 request.
     */
    public function detectFromRequest(): DeviceInfo
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? ServerRequestFactory::fromGlobals();
        $userAgent = $this->getUserAgentFromRequest($request);

        return $this->detect($userAgent);
    }

    /**
     * Detect device from a User-Agent string.
     */
    public function detect(string $userAgent): DeviceInfo
    {
        // Return cached result if same User-Agent
        if ($this->cachedUserAgent === $userAgent && $this->cachedResult !== null) {
            return $this->cachedResult;
        }

        $dd = new DeviceDetector($userAgent);
        $dd->parse();

        $isBot = $dd->isBot();
        $isMobile = $dd->isMobile();
        $isTablet = $dd->isTablet();
        $isDesktop = $dd->isDesktop();
        $isTv = $this->isTv($dd);

        // Handle bots: optionally treat them as desktop
        if ($isBot && $this->treatBotsAsDesktop) {
            $isDesktop = true;
            $isMobile = false;
            $isTablet = false;
        }

        $botInfo = $isBot ? $dd->getBot() : null;

        $deviceInfo = new DeviceInfo(
            isMobile: $isMobile,
            isTablet: $isTablet,
            isDesktop: $isDesktop,
            isTv: $isTv,
            isBot: $isBot,
            deviceType: $dd->getDeviceName(),
            brandName: $dd->getBrandName(),
            modelName: $dd->getModel(),
            osName: $dd->getOs('name'),
            osVersion: $dd->getOs('version'),
            osFamily: $dd->getOs('family'),
            browserName: $dd->getClient('name'),
            browserVersion: $dd->getClient('version'),
            browserEngine: $this->getBrowserEngine($dd),
            botName: $botInfo['name'] ?? null,
            botCategory: $botInfo['category'] ?? null,
        );

        // Cache the result
        $this->cachedUserAgent = $userAgent;
        $this->cachedResult = $deviceInfo;

        return $deviceInfo;
    }

    /**
     * Get User-Agent from PSR-7 request.
     */
    private function getUserAgentFromRequest(ServerRequestInterface $request): string
    {
        return $request->getHeaderLine('User-Agent');
    }

    /**
     * Check if device is a TV (Smart TV, game console with TV output, etc.)
     */
    private function isTv(DeviceDetector $dd): bool
    {
        $deviceType = $dd->getDeviceName();
        return in_array($deviceType, ['tv', 'console', 'car browser'], true);
    }

    /**
     * Get browser engine from parsed client info.
     */
    private function getBrowserEngine(DeviceDetector $dd): ?string
    {
        $client = $dd->getClient();
        if (!is_array($client)) {
            return null;
        }
        return $client['engine'] ?? null;
    }
}
```

### DeviceContext Type

```php
<?php
declare(strict_types=1);

namespace Netresearch\ContextsDevice\Context\Type;

use Netresearch\Contexts\Context\AbstractContext;
use Netresearch\ContextsDevice\Service\DeviceDetectionService;

/**
 * Context type for device detection.
 *
 * Replaces WURFL-based device type matching with Matomo DeviceDetector.
 * Supports: mobile, tablet, phone, desktop, tv, bot.
 *
 * Note: Screen dimension matching (WURFL's resolution_width/height)
 * is NOT supported. Use responsive CSS for breakpoint-based styling.
 */
final class DeviceContext extends AbstractContext
{
    public function __construct(
        private readonly DeviceDetectionService $deviceDetectionService,
    ) {}

    public function match(array $arDependencies = []): bool
    {
        // Check session cache first
        [$fromSession, $result] = $this->getMatchFromSession();
        if ($fromSession) {
            return $result;
        }

        $matches = $this->matchDeviceType();

        return $this->storeInSession($this->invert($matches));
    }

    private function matchDeviceType(): bool
    {
        $deviceInfo = $this->deviceDetectionService->detectFromRequest();

        // Match "Mobile" (any mobile device: phone + tablet)
        $matchMobile = (bool) $this->getConfValue('field_isMobile', '', 'sDEF');
        if ($matchMobile && $deviceInfo->isMobile) {
            return true;
        }

        // If not matching "all mobile", check specific types
        if (!$matchMobile) {
            // Match "Phone" (mobile but not tablet)
            $matchPhone = (bool) $this->getConfValue('field_isPhone', '', 'sDEF');
            if ($matchPhone && $deviceInfo->isPhone()) {
                return true;
            }

            // Match "Tablet"
            $matchTablet = (bool) $this->getConfValue('field_isTablet', '', 'sDEF');
            if ($matchTablet && $deviceInfo->isTablet) {
                return true;
            }
        }

        // Match "Desktop"
        $matchDesktop = (bool) $this->getConfValue('field_isDesktop', '', 'sDEF');
        if ($matchDesktop && $deviceInfo->isDesktop) {
            return true;
        }

        // Match "TV" (Smart TV, game console)
        $matchTv = (bool) $this->getConfValue('field_isTv', '', 'sDEF');
        if ($matchTv && $deviceInfo->isTv) {
            return true;
        }

        // Match "Bot"
        $matchBot = (bool) $this->getConfValue('field_isBot', '', 'sDEF');
        if ($matchBot && $deviceInfo->isBot) {
            return true;
        }

        return false;
    }
}
```

### BrowserContext Type

```php
<?php
declare(strict_types=1);

namespace Netresearch\ContextsDevice\Context\Type;

use Netresearch\Contexts\Context\AbstractContext;
use Netresearch\ContextsDevice\Service\DeviceDetectionService;

/**
 * Context type for browser detection.
 *
 * Matches by browser name and optionally version.
 * Replaces WURFL's mobile_browser capability.
 */
final class BrowserContext extends AbstractContext
{
    public function __construct(
        private readonly DeviceDetectionService $deviceDetectionService,
    ) {}

    public function match(array $arDependencies = []): bool
    {
        [$fromSession, $result] = $this->getMatchFromSession();
        if ($fromSession) {
            return $result;
        }

        $matches = $this->matchBrowser();

        return $this->storeInSession($this->invert($matches));
    }

    private function matchBrowser(): bool
    {
        $deviceInfo = $this->deviceDetectionService->detectFromRequest();

        // Get configured browsers (comma-separated)
        $configuredBrowsers = $this->getConfValue('field_browsers', '', 'sDEF');
        if ($configuredBrowsers === '') {
            return false;
        }

        $browserName = $deviceInfo->browserName;
        if ($browserName === null) {
            // Handle unknown browser
            return in_array('*unknown*', $this->parseBrowserList($configuredBrowsers), true);
        }

        // Check if browser matches any configured value
        $allowedBrowsers = $this->parseBrowserList($configuredBrowsers);
        foreach ($allowedBrowsers as $allowedBrowser) {
            if ($this->browserMatches($browserName, $deviceInfo->browserVersion, $allowedBrowser)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse browser list from config.
     *
     * @return string[]
     */
    private function parseBrowserList(string $configured): array
    {
        return array_map('trim', explode(',', $configured));
    }

    /**
     * Check if browser matches a pattern.
     *
     * Supports:
     * - Exact name match: "Chrome"
     * - Name with version: "Chrome 120"
     * - Name with version comparison: "Chrome >= 120"
     */
    private function browserMatches(string $browserName, ?string $browserVersion, string $pattern): bool
    {
        // Simple name match (case-insensitive)
        if (strcasecmp($browserName, $pattern) === 0) {
            return true;
        }

        // Check for version comparison patterns
        if (preg_match('/^(\w+)\s*(>=|<=|>|<|=)\s*(\d+(?:\.\d+)*)$/i', $pattern, $matches)) {
            $patternName = $matches[1];
            $operator = $matches[2];
            $patternVersion = $matches[3];

            if (strcasecmp($browserName, $patternName) !== 0) {
                return false;
            }

            if ($browserVersion === null) {
                return false;
            }

            return version_compare($browserVersion, $patternVersion, $operator);
        }

        // Check for "Name Version" pattern
        if (preg_match('/^(\w+)\s+(\d+(?:\.\d+)*)$/i', $pattern, $matches)) {
            $patternName = $matches[1];
            $patternVersion = $matches[2];

            return strcasecmp($browserName, $patternName) === 0
                && $browserVersion !== null
                && version_compare($browserVersion, $patternVersion, '>=');
        }

        return false;
    }
}
```

### Exception

```php
<?php
declare(strict_types=1);

namespace Netresearch\ContextsDevice\Exception;

use RuntimeException;

/**
 * Exception thrown when device detection fails.
 */
class DeviceDetectionException extends RuntimeException
{
}
```

## Security & Safety

- Validate User-Agent strings before processing (max length)
- Handle malformed User-Agents gracefully
- Cache results in session to minimize CPU usage
- Use TYPO3's PSR-7 request instead of `$_SERVER` directly
- DeviceDetector has DoS protection built-in

## Testing Patterns

### Unit Tests for DeviceDetectionService

```php
<?php
declare(strict_types=1);

namespace Netresearch\ContextsDevice\Tests\Unit\Service;

use Netresearch\ContextsDevice\Service\DeviceDetectionService;
use PHPUnit\Framework\TestCase;

final class DeviceDetectionServiceTest extends TestCase
{
    /**
     * @dataProvider userAgentProvider
     */
    public function testDetectDeviceType(
        string $userAgent,
        bool $expectedMobile,
        bool $expectedTablet,
        bool $expectedDesktop
    ): void {
        $service = new DeviceDetectionService(treatBotsAsDesktop: true);
        $result = $service->detect($userAgent);

        self::assertSame($expectedMobile, $result->isMobile);
        self::assertSame($expectedTablet, $result->isTablet);
        self::assertSame($expectedDesktop, $result->isDesktop);
    }

    public static function userAgentProvider(): iterable
    {
        // iPhone
        yield 'iPhone Safari' => [
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1',
            true,  // mobile
            false, // tablet
            false, // desktop
        ];

        // iPad
        yield 'iPad Safari' => [
            'Mozilla/5.0 (iPad; CPU OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1',
            true,  // mobile (tablets are mobile)
            true,  // tablet
            false, // desktop
        ];

        // Desktop Chrome
        yield 'Chrome Windows' => [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            false, // mobile
            false, // tablet
            true,  // desktop
        ];

        // Android phone
        yield 'Android Chrome' => [
            'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.6099.43 Mobile Safari/537.36',
            true,  // mobile
            false, // tablet
            false, // desktop
        ];
    }

    public function testBotTreatedAsDesktop(): void
    {
        $service = new DeviceDetectionService(treatBotsAsDesktop: true);
        $result = $service->detect('Googlebot/2.1 (+http://www.google.com/bot.html)');

        self::assertTrue($result->isBot);
        self::assertTrue($result->isDesktop);
        self::assertFalse($result->isMobile);
    }

    public function testBotNotTreatedAsDesktop(): void
    {
        $service = new DeviceDetectionService(treatBotsAsDesktop: false);
        $result = $service->detect('Googlebot/2.1 (+http://www.google.com/bot.html)');

        self::assertTrue($result->isBot);
        self::assertFalse($result->isDesktop);
        self::assertFalse($result->isMobile);
    }

    public function testCachingWorks(): void
    {
        $service = new DeviceDetectionService();
        $ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X)';

        $result1 = $service->detect($ua);
        $result2 = $service->detect($ua);

        self::assertSame($result1, $result2);
    }
}
```

### Unit Tests for DeviceContext

```php
<?php
declare(strict_types=1);

namespace Netresearch\ContextsDevice\Tests\Unit\Context\Type;

use Netresearch\ContextsDevice\Context\Type\DeviceContext;
use Netresearch\ContextsDevice\Dto\DeviceInfo;
use Netresearch\ContextsDevice\Service\DeviceDetectionService;
use PHPUnit\Framework\TestCase;

final class DeviceContextTest extends TestCase
{
    public function testMatchesMobileDevice(): void
    {
        $deviceInfo = new DeviceInfo(
            isMobile: true,
            isTablet: false,
            isDesktop: false,
            isTv: false,
            isBot: false,
        );

        $service = $this->createMock(DeviceDetectionService::class);
        $service->method('detectFromRequest')->willReturn($deviceInfo);

        $context = $this->createContextWithConfig($service, ['field_isMobile' => '1']);

        // Test match method via reflection
        self::assertTrue($this->invokeMatchDeviceType($context));
    }

    private function createContextWithConfig(
        DeviceDetectionService $service,
        array $config
    ): DeviceContext {
        // Create mock context with configuration
        // ... test implementation
    }

    private function invokeMatchDeviceType(DeviceContext $context): bool
    {
        $reflection = new \ReflectionMethod($context, 'matchDeviceType');
        $reflection->setAccessible(true);
        return $reflection->invoke($context);
    }
}
```

### Unit Tests for BrowserContext

```php
<?php
declare(strict_types=1);

namespace Netresearch\ContextsDevice\Tests\Unit\Context\Type;

use Netresearch\ContextsDevice\Context\Type\BrowserContext;
use PHPUnit\Framework\TestCase;

final class BrowserContextTest extends TestCase
{
    /**
     * @dataProvider browserMatchPatternProvider
     */
    public function testBrowserMatchesPattern(
        string $browserName,
        ?string $browserVersion,
        string $pattern,
        bool $expected
    ): void {
        $context = $this->createBrowserContext();

        $result = $this->invokeBrowserMatches(
            $context,
            $browserName,
            $browserVersion,
            $pattern
        );

        self::assertSame($expected, $result);
    }

    public static function browserMatchPatternProvider(): iterable
    {
        // Exact match
        yield 'Chrome exact' => ['Chrome', '120.0', 'Chrome', true];
        yield 'Chrome case insensitive' => ['Chrome', '120.0', 'chrome', true];
        yield 'Safari no match' => ['Safari', '17.2', 'Chrome', false];

        // Version comparison
        yield 'Chrome >= 120' => ['Chrome', '120.0', 'Chrome >= 120', true];
        yield 'Chrome >= 121 (too low)' => ['Chrome', '120.0', 'Chrome >= 121', false];
        yield 'Firefox < 100' => ['Firefox', '95.0', 'Firefox < 100', true];

        // Name version pattern
        yield 'Chrome 120' => ['Chrome', '120.0', 'Chrome 120', true];
        yield 'Chrome 119 (old version)' => ['Chrome', '119.0', 'Chrome 120', false];
    }

    private function invokeBrowserMatches(
        BrowserContext $context,
        string $browserName,
        ?string $browserVersion,
        string $pattern
    ): bool {
        $reflection = new \ReflectionMethod($context, 'browserMatches');
        $reflection->setAccessible(true);
        return $reflection->invoke($context, $browserName, $browserVersion, $pattern);
    }
}
```

## PR/Commit Checklist

- [ ] `composer lint` passes
- [ ] `composer analyze` passes
- [ ] Unit tests added/updated for new functionality
- [ ] Strict types declared
- [ ] Return types on all methods
- [ ] DeviceInfo DTO uses readonly properties
- [ ] Session caching used for performance
- [ ] User-Agent accessed via PSR-7 request

## Good vs Bad Examples

### Device Detection

```php
// Good: Service-based detection with caching
$service = $this->deviceDetectionService;
$deviceInfo = $service->detectFromRequest();
if ($deviceInfo->isMobile) {
    // ...
}

// Bad: Direct DeviceDetector instantiation (no caching)
$dd = new DeviceDetector($_SERVER['HTTP_USER_AGENT']);
$dd->parse();
if ($dd->isMobile()) {
    // ...
}
```

### User-Agent Access

```php
// Good: PSR-7 request
$request = $GLOBALS['TYPO3_REQUEST'];
$userAgent = $request->getHeaderLine('User-Agent');

// Bad: Direct $_SERVER access
$userAgent = $_SERVER['HTTP_USER_AGENT'];
```

### Device Type Checks

```php
// Good: Use DTO methods
if ($deviceInfo->isPhone()) {
    // Phone (mobile but not tablet)
}

// Bad: Manual combination
if ($deviceInfo->isMobile && !$deviceInfo->isTablet) {
    // This works but duplicates logic
}
```

### FlexForm Configuration Access

```php
// Good: Use getConfValue with defaults
$matchMobile = (bool) $this->getConfValue('field_isMobile', '', 'sDEF');

// Bad: Legacy direct property access
$matchMobile = $this->arConfig['settings.isMobile'];
```

## WURFL Migration Notes

### Removed Features (Not Available in DeviceDetector)

```php
// THESE WURFL FEATURES ARE NOT AVAILABLE:
// - resolution_width / resolution_height (screen dimensions)
// - is_wireless_device (use isMobile instead)
// - can_assign_phone_number (use isPhone instead)
// - Java support flags
// - Streaming media capabilities
// - Hardware-specific features
```

### Equivalent Mappings

```php
// WURFL -> DeviceDetector equivalents:
$wurfl->isWireless()      -> $deviceInfo->isMobile
$wurfl->isTablet()        -> $deviceInfo->isTablet
$wurfl->isPhone()         -> $deviceInfo->isPhone()
$wurfl->isSmartTv()       -> $deviceInfo->isTv
$wurfl->getBrandName()    -> $deviceInfo->brandName
$wurfl->getModelName()    -> $deviceInfo->modelName
$wurfl->getMobileBrowser()-> $deviceInfo->browserName
```

### CLI Import Removal

```bash
# OLD WURFL commands (REMOVED):
# typo3/cli_dispatch.phpsh contexts_wurfl import --type local
# typo3/cli_dispatch.phpsh contexts_wurfl import --type remote

# NEW: No import needed - DeviceDetector uses bundled regexes
# Just install the extension and it works immediately
```

## House Rules

- All context types must work in both frontend and backend contexts
- Session caching is mandatory for performance
- User-Agent string must be validated (max 1024 chars)
- Bot detection enabled by default (treatBotsAsDesktop)
- No screen dimension matching (use responsive CSS)
- Browser version patterns support semantic versioning comparisons

## When Stuck

- Matomo DeviceDetector: https://github.com/matomo-org/device-detector
- DeviceDetector Demo: https://devicedetector.io/
- TYPO3 Core API: https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/
- Extension issues: https://github.com/netresearch/t3x-contexts_wurfl/issues
