<!-- Managed by agent: keep sections & order; edit content, not structure. Last updated: 2026-01-28 -->

# AGENTS.md — Classes/

PHP source code for the Contexts Device Detection extension.

## Overview

```
Classes/
├── Context/           # Device detection context types
│   └── Type/          # DeviceContext implementation
├── Dto/               # Data Transfer Objects
└── Service/           # Device detection services
```

## Code Style & Conventions

### Device Detection Context

```php
namespace Netresearch\ContextsDevice\Context\Type;

use Netresearch\Contexts\Context\AbstractContext;
use Netresearch\ContextsDevice\Service\DeviceDetectionService;

final class DeviceContext extends AbstractContext
{
    public function match(array $arDependencies = []): bool
    {
        // Match by device type, OS, browser
    }
}
```

### Device Detection Service

```php
namespace Netresearch\ContextsDevice\Service;

final class DeviceDetectionService
{
    // Uses matomo/device-detector for user agent parsing
}
```

### DTOs

```php
namespace Netresearch\ContextsDevice\Dto;

readonly class DeviceInfo
{
    public function __construct(
        public string $type,
        public ?string $brand,
        public ?string $model,
    ) {}
}
```

## PR/Commit Checklist

- [ ] New classes follow PSR-4 autoloading
- [ ] DTOs are readonly
- [ ] Services are final
- [ ] Context types extend AbstractContext

## House Rules

- Device detection via matomo/device-detector library
- Cache device detection results for performance
- DTOs must be immutable (readonly)
