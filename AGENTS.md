<!-- Managed by agent: keep sections & order; edit content, not structure. Last updated: 2026-01-28 -->

# AGENTS.md

**Project:** netresearch/contexts_device — Device detection context types for TYPO3
**Type:** TYPO3 CMS Extension (PHP 8.2+, TYPO3 12.4/13.4)
**Status:** LEGACY - Complete rewrite planned for TYPO3 v12/v13

> **Important:** This is currently a LEGACY extension (TYPO3 4.5-6.2, PHP 5.x) named `contexts_wurfl`.
> This AGENTS.md documents the TARGET architecture for the upcoming complete rewrite.
> The extension will be renamed to `contexts_device` and use Matomo DeviceDetector.
> Do NOT reference current legacy code patterns during rewrite.

## Architectural Decision: Matomo DeviceDetector

**Library:** `matomo/device-detector` ^6.0 (NOT the bundled WURFL library)

**Rationale:**
- Higher update frequency (crucial for detecting new devices)
- Larger community (Matomo user base)
- Industry leader for PHP device detection
- No database required (pure PHP, regex-based)
- MIT licensed

**WURFL Capability Loss Warning:**
Users relying on WURFL's exhaustive device capability flags will lose this functionality:
- Screen dimensions (resolution_width, resolution_height) - NOT available
- Java support - NOT available
- Specific hardware features - NOT available

**Available in Matomo DeviceDetector:**
- Device type (mobile, tablet, desktop, tv, console, car browser, etc.)
- Operating system (name, version, family)
- Browser (name, version, engine)
- Brand/manufacturer
- Model name
- Bot detection

## Precedence

The **closest AGENTS.md** to changed files wins. This root file holds global defaults only.

## Global Rules

- Keep PRs small (~300 net LOC)
- Conventional Commits: `type(scope): subject`
- Ask before: heavy dependencies, architecture changes, new context types
- Never commit secrets, credentials, or PII
- GrumPHP runs pre-commit checks automatically

## Pre-Commit Checks (GrumPHP)

```bash
# Automatic on commit (via GrumPHP):
composer lint          # PHP_CodeSniffer (PSR-12 + TYPO3 CGL)
composer analyze       # PHPStan level 8

# Manual testing:
composer test:unit        # PHPUnit unit tests
composer test:functional  # PHPUnit functional tests (needs DB)
composer test:coverage    # Coverage report (needs PCOV/Xdebug)
```

## Development Environment

```bash
# DDEV setup (recommended)
ddev start
ddev install-all          # Install TYPO3 v12, v13

# Access
https://v12.contexts-device.ddev.site/typo3/    # TYPO3 v12 backend
https://v13.contexts-device.ddev.site/typo3/    # TYPO3 v13 backend

# Credentials: admin / Password:joh316
```

## CI Workflows

| Workflow | Trigger | Purpose |
|----------|---------|---------|
| `ci.yml` | push/PR | Full test suite (unit, functional, lint, phpstan) |
| `phpstan.yml` | push/PR | Static analysis |
| `phpcs.yml` | push/PR | Code style |
| `security.yml` | schedule | Dependency vulnerability scan |
| `publish-to-ter.yml` | tag | Publish to TYPO3 Extension Repository |

## Project Structure (Target)

```
Classes/                       # PHP source code
├── Context/Type/              # Context type implementations
│   ├── DeviceContext.php      # Mobile/Tablet/Desktop matching
│   └── BrowserContext.php     # Browser name/version matching
├── Service/                   # Business logic services
│   └── DeviceDetectionService.php
├── Dto/                       # Value objects for device data
│   └── DeviceInfo.php
└── Exception/                 # Custom exceptions
    └── DeviceDetectionException.php
Tests/                         # Test suite
├── Unit/                      # Unit tests
│   ├── Context/Type/
│   └── Service/
└── Functional/                # Functional tests
Configuration/                 # TYPO3 configuration
├── TCA/Overrides/
├── FlexForms/
├── Services.yaml
└── SiteSet/                   # v13 site sets
Resources/                     # Language files, assets
Documentation/                 # RST documentation
```

## Index of Scoped AGENTS.md

| Path | Purpose |
|------|---------|
| `Classes/AGENTS.md` | PHP backend code, device detection service, context types |

## Dependencies

**Required:**
- `netresearch/contexts` ^2.0 - Base contexts extension
- `matomo/device-detector` ^6.0 - Device detection library

**Optional (for cache warming):**
- PSR-16 simple cache implementation

## Key Concepts

### Matomo DeviceDetector Integration

```php
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Client\Browser;

$dd = new DeviceDetector($userAgent);
$dd->parse();

// Device type detection
$dd->isMobile();     // Phone or Tablet
$dd->isTablet();     // Tablet specifically
$dd->isDesktop();    // Desktop/Laptop
$dd->isBot();        // Bot/Crawler

// Device information
$dd->getDeviceName();     // e.g., "smartphone", "tablet", "desktop"
$dd->getBrandName();      // e.g., "Apple", "Samsung"
$dd->getModel();          // e.g., "iPhone 15", "Galaxy S24"

// OS information
$dd->getOs('name');       // e.g., "iOS", "Android", "Windows"
$dd->getOs('version');    // e.g., "17.2", "14", "11"

// Browser information
$dd->getClient('name');   // e.g., "Chrome", "Safari", "Firefox"
$dd->getClient('version');// e.g., "120.0", "17.2"
```

### Context Types

| Context Type | Purpose | Configuration Fields |
|-------------|---------|---------------------|
| `DeviceContext` | Match by device type | Device types (mobile, tablet, desktop, tv, etc.) |
| `BrowserContext` | Match by browser | Browser names, version patterns |

### Device Type Mapping (WURFL to Matomo)

| WURFL Capability | Matomo DeviceDetector |
|-----------------|----------------------|
| `is_wireless_device` | `isMobile()` |
| `is_tablet` | `isTablet()` |
| `can_assign_phone_number` | `isMobile() && !isTablet()` |
| `is_smarttv` | `isTv()` |
| `brand_name` | `getBrandName()` |
| `model_name` | `getModel()` |
| `mobile_browser` | `getClient('name')` |
| `resolution_width` | NOT AVAILABLE |
| `resolution_height` | NOT AVAILABLE |

## Configuration

### Extension Configuration (ext_conf_template.txt)

```
# Cache parsed results (recommended for production)
enableCache = 1

# Bot detection behavior
treatBotsAsDesktop = 1

# Client hints support (for modern browsers)
enableClientHints = 1
```

## Migration from WURFL

### Breaking Changes

1. **No Screen Dimensions**: `screenWidthMin`, `screenWidthMax`, `screenHeightMin`, `screenHeightMax` are removed. Use responsive CSS instead.

2. **Simplified Device Types**: "Wireless" concept removed. Use "Mobile" which covers all non-desktop devices.

3. **Database Removed**: No more WURFL database import CLI. DeviceDetector uses bundled regex files.

### Migration Path

```php
// Old WURFL check
$wurfl->isWireless(); // Check for wireless capability

// New DeviceDetector equivalent
$dd->isMobile();      // Check for mobile/tablet
```

## When Instructions Conflict

Nearest AGENTS.md wins. User prompts override files.

## Resources

- [Matomo DeviceDetector](https://github.com/matomo-org/device-detector)
- [DeviceDetector Demo](https://devicedetector.io/)
- [TYPO3 Coding Guidelines](https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/CodingGuidelines/Index.html)
- [Base Extension](https://github.com/netresearch/t3x-contexts)
- [GitHub Issues](https://github.com/netresearch/t3x-contexts_wurfl/issues)
