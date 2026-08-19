<!-- Managed by agent: keep sections & order; edit content, not structure. Last updated: 2026-08-19 -->

# AGENTS.md — Classes/

PHP source code for the Contexts Device Detection extension. Namespace `Netresearch\ContextsDevice` (PSR-4, mapped in `composer.json`).

## Overview

```
Classes/
├── Context/           # Device detection context types
│   ├── Type/          # DeviceContext, BrowserContext
│   └── DeviceDetectionAwareTrait.php  # Request/device-info resolution shared by all context types
├── Dto/               # DeviceInfo (readonly value object)
└── Service/           # DeviceDetectionService (matomo/device-detector wrapper)
```

## Setup

```bash
composer install     # installs into .Build/ (composer bin-dir: .Build/bin)
```

Services are wired in `../Configuration/Services.yaml` (autowire + autoconfigure, `Netresearch\ContextsDevice\` → `../Classes/`).

## Build & Tests

```bash
composer ci:test:php:unit      # unit tests for this code
composer ci:test:php:phpstan   # PHPStan (Build/phpstan.neon)
composer ci:test:php:cgl       # code style check (fix: composer ci:cgl)
```

Architecture rules for these classes are enforced by PHPat (`Tests/Architecture/LayerTest.php`): context types extend `AbstractContext`, DTOs are readonly, services are final.

## Code Style & Conventions

- Context types live in `Context/Type/`, extend `Netresearch\Contexts\Context\AbstractContext` and implement `match(array $arDependencies = []): bool`.
- Shared request/device-info resolution belongs in `DeviceDetectionAwareTrait`, not duplicated per context type.
- Service classes are `final`; DTOs are `readonly` with promoted constructor properties.
- `declare(strict_types=1);` and the SPDX/license header block in every file.

## Security

- Treat the User-Agent header (and client hints) as untrusted input — it is attacker-controlled. Never echo it unescaped or use it in queries/paths.
- Device detection must fail closed: on parse errors a context should not match rather than throw into the frontend rendering.
- No secrets, tokens, or credentials in PHP sources.

## Examples

```php
// Good: readonly DTO with promoted properties (see Dto/DeviceInfo.php)
final readonly class DeviceInfo
{
    public function __construct(
        public bool $isMobile = false,
        public bool $isTablet = false,
        // ...
    ) {}
}

// Bad: parsing the user agent inside a context type — use DeviceDetectionService
// via DeviceDetectionAwareTrait instead of instantiating DeviceDetector directly.
```

## PR/Commit Checklist

- [ ] New classes follow PSR-4 autoloading (`Netresearch\ContextsDevice\` → `Classes/`)
- [ ] DTOs are readonly, services are final (PHPat enforces this)
- [ ] Context types extend `AbstractContext`
- [ ] Unit tests added/updated for changed behavior

## When Stuck

- Base context API: `.Build/vendor/netresearch/contexts/Classes/Context/AbstractContext.php` (after `composer install`)
- DeviceDetector API: https://github.com/matomo-org/device-detector
- Architecture decisions: `../docs/adr/`, component map: `../docs/ARCHITECTURE.md`
- Open a GitHub issue: https://github.com/netresearch/t3x-contexts_wurfl/issues

## House Rules

- Device detection via matomo/device-detector library only — no WURFL remnants
- Cache device detection results per request; DTOs must be immutable (readonly)
