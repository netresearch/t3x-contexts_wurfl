# Architecture

Agent-facing component map for `netresearch/contexts-wurfl`. Facts here are verified against the source files listed; when code and this document disagree, the code wins — fix this file in the same PR.

## System Overview

The extension adds two context types to the [netresearch/contexts](https://github.com/netresearch/t3x-contexts) base extension: `device` and `browser`. Editors configure them in the TYPO3 backend via FlexForms; at frontend request time the base extension evaluates every configured context, and these types answer their `match()` by parsing the visitor's User-Agent with `matomo/device-detector`.

## Components

| Component | Path | Responsibility |
|-----------|------|----------------|
| DeviceContext | `Classes/Context/Type/DeviceContext.php` | Matches configured device types (mobile, tablet, desktop, bot, …) |
| BrowserContext | `Classes/Context/Type/BrowserContext.php` | Matches configured browser names |
| DeviceDetectionAwareTrait | `Classes/Context/DeviceDetectionAwareTrait.php` | Shared request access (`$GLOBALS['TYPO3_REQUEST']`) and lazy service resolution for context types built via `GeneralUtility::makeInstance()` |
| DeviceDetectionService | `Classes/Service/DeviceDetectionService.php` | Wraps `DeviceDetector\DeviceDetector`; `detectForCurrentRequest()` / `detectFromRequest()` / `detectFromUserAgent()`; per-user-agent in-memory cache |
| DeviceInfo | `Classes/Dto/DeviceInfo.php` | `final readonly` DTO carrying detection results (isMobile, isTablet, …) |
| Context registration | `Configuration/TCA/Overrides/tx_contexts_contexts.php` | Registers both types via `Netresearch\Contexts\Api\Configuration::registerContextType()` |
| FlexForms | `Configuration/FlexForms/Device.xml`, `Configuration/FlexForms/Browser.xml` | Backend configuration forms per context type |
| DI configuration | `Configuration/Services.yaml` | Autowired namespace; `DeviceDetectionService` is `public: true` (resolved from the container by makeInstance-built context types); `DeviceDetector` is `shared: false` |

## Dependency Rules

Enforced by PHPat in `Tests/Architecture/LayerTest.php` (runs with the unit test suite):

1. Classes in `Netresearch\ContextsDevice\Context\Type` must extend `Netresearch\Contexts\Context\AbstractContext`.
2. Classes in `Netresearch\ContextsDevice\Dto` must be readonly.
3. Classes in `Netresearch\ContextsDevice\Service` must be final.

## Data Flow

1. Frontend request → base extension `netresearch/contexts` evaluates configured context records and instantiates the registered type class (`Factory::createFromDb()` via `GeneralUtility::makeInstance()`).
2. `DeviceContext`/`BrowserContext::match()` uses `DeviceDetectionAwareTrait::getDeviceInfo()`: it reads `$GLOBALS['TYPO3_REQUEST']` and calls `DeviceDetectionService::detectFromRequest()`.
3. The service parses the `User-Agent` header with Matomo DeviceDetector (regex-based, no database), caches the result per user agent string, and returns a `DeviceInfo` DTO — or `null` when no request/user agent is available (contexts then do not match).
4. The context compares the DTO against the FlexForm-configured values and returns the boolean match to the base extension.

## Key Decisions

- [ADR-0001](adr/0001-replace-wurfl-with-device-detector.md) — replace WURFL with Matomo DeviceDetector (rationale, options considered, capability trade-offs).
- Migration consequences for users are documented in `Documentation/Migration/Index.rst`.
