# ADR-0001: Replace WURFL with Matomo DeviceDetector

## Status

**Accepted** — January 2026

## Context

The `contexts_wurfl` extension has been using the WURFL (Wireless Universal Resource FiLe) library for device detection since 2013. The legacy implementation:

- Uses WURFL DB-API 1.4.4.0 (TeraWurfl)
- Requires a MySQL database for capability lookups
- Depends on proprietary WURFL data (licensing concerns with modern WURFL)
- Has not been updated for PHP 8.x or TYPO3 v12/v13

As part of modernizing this extension for TYPO3 12.4 LTS and 13.4 LTS, we need to choose a device detection library that:

1. Is actively maintained with frequent updates for new devices
2. Has a permissive open-source license
3. Does not require external database infrastructure
4. Supports modern PHP (8.2+)
5. Has a large community for reliability

### Options Considered

| Library | License | Database Required | Update Frequency | Community |
|---------|---------|-------------------|------------------|-----------|
| WURFL Microcloud | Commercial | Cloud API | Frequent | Scientia Mobile |
| WURFL InFuze | Commercial | Local file | Frequent | Scientia Mobile |
| Matomo DeviceDetector | MIT/LGPL | None (regex) | Very frequent | Matomo community |
| Mobile Detect | MIT | None (regex) | Infrequent | Small |
| Browscap | MIT | INI file | Moderate | PHP community |

## Decision

We will replace WURFL with **Matomo DeviceDetector** (`matomo/device-detector` ^6.0).

### Rationale

1. **Higher update frequency**: DeviceDetector is updated frequently to support new devices, browsers, and operating systems. The Matomo team actively maintains device regex patterns.

2. **Larger community**: Backed by Matomo Analytics with millions of installations, ensuring long-term maintenance and broad real-world testing.

3. **Industry leader**: DeviceDetector is the de facto standard for PHP device detection in the open-source ecosystem.

4. **No database required**: Uses bundled YAML regex files, eliminating the need for MySQL database infrastructure and import CLI commands.

5. **MIT licensed**: Fully open source with no licensing concerns or commercial dependencies.

6. **Modern PHP support**: Actively supports PHP 8.2+ with type declarations and modern patterns.

## Consequences

### Positive

- **Simpler deployment**: No database setup, import commands, or scheduled updates required
- **Better device coverage**: More frequently updated device database
- **Reduced complexity**: Pure PHP library with no external dependencies
- **Clear licensing**: MIT license removes any commercial concerns
- **Bot detection**: Built-in bot/crawler detection included

### Negative — CAPABILITY LOSS WARNING

**Users relying on WURFL's exhaustive device capability flags will lose functionality.**

The following WURFL capabilities are **NOT available** in Matomo DeviceDetector:

| Lost Capability | WURFL Method | Impact |
|----------------|--------------|--------|
| Screen width | `resolution_width` | Cannot filter by screen dimensions |
| Screen height | `resolution_height` | Cannot filter by screen dimensions |
| Java support | `j2me_midp_*` | Cannot detect Java ME support |
| Flash support | `full_flash_support` | Cannot detect Flash capability |
| HTML5 features | Various | Cannot detect specific HTML5 features |
| Hardware details | Various | Cannot detect specific hardware features |

**Mitigation**: Modern web development practices (responsive design, feature detection via JavaScript) have largely eliminated the need for server-side screen dimension detection.

### Neutral

The extension will be renamed from `contexts_wurfl` to `contexts_device` to reflect the technology-agnostic approach.

## Capability Mapping

### Supported Mappings (WURFL → DeviceDetector)

| WURFL Capability | WURFL Method | DeviceDetector Method |
|-----------------|--------------|----------------------|
| Wireless device | `is_wireless_device` | `isMobile()` |
| Tablet | `is_tablet` | `isTablet()` |
| Phone | `can_assign_phone_number` | `isMobile() && !isTablet()` |
| Smart TV | `is_smarttv` | `isTv()` |
| Brand name | `brand_name` | `getBrandName()` |
| Model name | `model_name` | `getModel()` |
| Mobile browser | `mobile_browser` | `getClient('name')` |
| Desktop | (derived) | `isDesktop()` |
| Bot/Crawler | (not available) | `isBot()` |

### New Capabilities (DeviceDetector only)

| Capability | Method | Description |
|------------|--------|-------------|
| Device type | `getDeviceName()` | smartphone, tablet, desktop, tv, console, etc. |
| OS name | `getOs('name')` | iOS, Android, Windows, macOS, Linux, etc. |
| OS version | `getOs('version')` | Version string |
| OS family | `getOs('family')` | Android, iOS, Windows, GNU/Linux, etc. |
| Browser engine | `getClient('engine')` | Blink, WebKit, Gecko, etc. |
| Browser version | `getClient('version')` | Full version string |
| Bot info | `getBot()` | Bot name, category, URL, producer |

## Usage Example

```php
use DeviceDetector\DeviceDetector;

$deviceDetector = new DeviceDetector($userAgent);
$deviceDetector->parse();

// Device type detection
$deviceDetector->isMobile();     // true for phones and tablets
$deviceDetector->isTablet();     // true for tablets only
$deviceDetector->isDesktop();    // true for desktop browsers
$deviceDetector->isTv();         // true for smart TVs
$deviceDetector->isBot();        // true for bots/crawlers

// Device information
$deviceDetector->getBrandName(); // "Apple", "Samsung", etc.
$deviceDetector->getModel();     // "iPhone 15", "Galaxy S24", etc.

// OS information
$os = $deviceDetector->getOs();
// ['name' => 'iOS', 'version' => '17.2', 'platform' => '', 'family' => 'iOS']

// Browser information
$client = $deviceDetector->getClient();
// ['name' => 'Safari', 'version' => '17.2', 'engine' => 'WebKit', ...]
```

## Migration Guide

### Configuration Changes

**Removed settings:**
- `screenWidthMin`, `screenWidthMax`
- `screenHeightMin`, `screenHeightMax`

**Modified settings:**
- `isWireless` → Use `isMobile` instead
- `isPhone` → Still available, implemented as `isMobile && !isTablet`

**New settings:**
- `isDesktop` — Match desktop browsers
- `isTv` — Match smart TVs (replaces `isSmartTv`)
- `isBot` — Match bots/crawlers (opt-in)
- `osName` — Match by operating system
- `browserName` — Match by browser name

### Database Removal

The WURFL database tables and import CLI command are no longer needed:

```bash
# Old (WURFL) - NO LONGER NEEDED
typo3 contexts:wurfl:import

# New (DeviceDetector) - NO DATABASE REQUIRED
# Device data is bundled with the library
```

## References

- [Matomo DeviceDetector GitHub](https://github.com/matomo-org/device-detector)
- [DeviceDetector Demo](https://devicedetector.io/)
- [WURFL Official Site](https://www.scientiamobile.com/wurfl/) (for historical context)
- [Mobile Detect Comparison](https://github.com/serbanghita/Mobile-Detect/blob/master/doc/COMPARISON.md)

## Changelog

- **2026-01-28**: Initial decision documented
