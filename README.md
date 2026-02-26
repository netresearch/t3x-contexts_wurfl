# TYPO3 Contexts: Device Detection

[![TYPO3 12](https://img.shields.io/badge/TYPO3-12-green.svg)](https://get.typo3.org/version/12)
[![TYPO3 13](https://img.shields.io/badge/TYPO3-13-green.svg)](https://get.typo3.org/version/13)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://www.php.net/)
[![License: AGPL v3](https://img.shields.io/badge/License-AGPL%20v3-blue.svg)](https://www.gnu.org/licenses/agpl-3.0)

Device detection context types for TYPO3. Show pages and content elements based on the visitor's device type (mobile, tablet, desktop), browser, or operating system.

**Part of the [Contexts extension suite](https://github.com/netresearch/t3x-contexts) by [Netresearch](https://www.netresearch.de/).**

## Features

- **Device Type Detection** - Match content to mobile phones, tablets, desktops, or bots
- **Browser Detection** - Target specific browsers (Chrome, Firefox, Safari, Edge, etc.)
- **No Database Required** - Uses bundled device patterns, no external database setup
- **Accurate Detection** - Powered by [Matomo DeviceDetector](https://github.com/matomo-org/device-detector)
- **Bot Detection** - Built-in detection for search engine crawlers and bots

## Requirements

- TYPO3 12.4 LTS or 13.4 LTS
- PHP 8.2 or higher
- [Contexts](https://github.com/netresearch/t3x-contexts) extension v4.0+

## Installation

```bash
composer require netresearch/contexts-wurfl
```

Activate the extension:

```bash
vendor/bin/typo3 extension:activate contexts_wurfl
```

## Quick Start

### Device Type Context

Target visitors based on their device type:

1. Go to **Admin Tools > Contexts**
2. Create a new context of type "Device Type"
3. Select the device types to match:
   - **Mobile devices** - All mobile devices (phones and tablets)
   - **Phones** - Mobile phones specifically (not tablets)
   - **Tablets** - Tablets specifically
   - **Desktop/Laptop** - Desktop and laptop computers
   - **Bots/Crawlers** - Search engine bots and crawlers
4. Save and use the context for content visibility

### Browser Context

Target visitors based on their browser:

1. Go to **Admin Tools > Contexts**
2. Create a new context of type "Browser"
3. Enter a comma-separated list of browser names, e.g., `Chrome, Firefox, Safari`
4. Save and use the context for content visibility

Common browser names:
- Desktop: `Chrome`, `Firefox`, `Safari`, `Microsoft Edge`, `Opera`
- Mobile: `Mobile Safari`, `Chrome Mobile`, `Firefox Mobile`, `Samsung Browser`

## Migration from WURFL

Version 2.0 is a complete rewrite using Matomo DeviceDetector instead of the legacy WURFL library.

### Key Changes

| Aspect | Legacy (1.x) | New (2.x) |
|--------|-------------|-----------|
| Detection library | WURFL DB-API | Matomo DeviceDetector |
| Database required | Yes | No |
| Data updates | Manual import | Composer update |
| TYPO3 support | 4.5 - 6.2 | 12.4, 13.4 |
| PHP support | 5.3 - 5.6 | 8.2 - 8.5 |

### Breaking Changes

**Screen dimension filters are no longer available:**
- `screenWidthMin`, `screenWidthMax`
- `screenHeightMin`, `screenHeightMax`

Modern web development uses CSS media queries for responsive layouts.

**WURFL database tables are no longer used.**
You can safely remove them after migration.

See the [Migration Guide](Documentation/Migration/Index.rst) for detailed instructions.

## Documentation

Full documentation is available at [docs.typo3.org](https://docs.typo3.org/) (coming soon) or in the [Documentation](Documentation/) directory.

- [Introduction](Documentation/Introduction/Index.rst)
- [Installation](Documentation/Installation/Index.rst)
- [Configuration](Documentation/Configuration/Index.rst)
- [Context Types](Documentation/ContextTypes/Index.rst)
- [Migration Guide](Documentation/Migration/Index.rst)

## Related Extensions

- **[contexts](https://github.com/netresearch/t3x-contexts)** - Base contexts extension (required)
- **[contexts_geolocation](https://github.com/netresearch/t3x-contexts_geolocation)** - Geolocation-based contexts

## Development

```bash
# Install dependencies
composer install

# Run tests
composer ci:test:php:unit
composer ci:test:php:functional

# Code quality
composer ci:test:php:cgl
composer ci:test:php:phpstan
```

## License

This extension is licensed under the [GNU Affero General Public License v3.0](LICENSE).

## Credits

Developed and maintained by [Netresearch DTT GmbH](https://www.netresearch.de/).

Device detection powered by [Matomo DeviceDetector](https://github.com/matomo-org/device-detector).

---

**[n] Netresearch** - We make e-commerce work.
