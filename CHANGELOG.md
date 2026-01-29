# Changelog

All notable changes to this project will be documented in this file.

## [2.0.0] - 2026-01-28

### Added
- TYPO3 v12 LTS and v13 LTS support
- PHP 8.2, 8.3, 8.4, and 8.5 support
- Complete rewrite using Matomo DeviceDetector instead of legacy WURFL library
- No external database required - uses bundled device patterns updated via Composer
- Device Type Context: Match mobile phones, tablets, desktop/laptop, or bots
- Browser Context: Match specific browsers (Chrome, Firefox, Safari, Edge, Opera, etc.)
- Bot/Crawler detection for search engine bots
- Full type safety with strict PHP 8.2+ typing
- Comprehensive unit and functional test suite
- PHPStan level 10 compliance (strict static analysis)
- Mutation testing to ensure code quality
- CI/CD integration with GitHub Actions
- Full documentation in reStructuredText format
- Migration guide from legacy WURFL extension

### Changed
- **Breaking**: Complete architectural rewrite from WURFL to Matomo DeviceDetector
- **Breaking**: Minimum TYPO3 version now 12.4 LTS (dropped TYPO3 4.5-6.2)
- **Breaking**: Minimum PHP version now 8.2 (dropped PHP 5.3-5.6)
- **Breaking**: No database required anymore (WURFL tables no longer used)
- **Breaking**: Data updates now managed via Composer instead of manual import
- Detection library changed from WURFL DB-API to Matomo DeviceDetector
- All classes moved to Netresearch\ContextsDevice namespace
- FlexForms configuration updated for TYPO3 12/13
- TCA configuration modernized for current TYPO3 versions
- Context type names updated for clarity

### Removed
- Support for TYPO3 4.5 through 11.5
- Support for PHP 5.3 through 8.1
- Legacy WURFL database integration
- Screen dimension filtering (screenWidthMin, screenWidthMax, screenHeightMin, screenHeightMax)
  - Modern web development uses CSS media queries for responsive layouts
- WURFL database tables (safe to remove after migration)
- Legacy extension settings in TYPO3 backend

### Fixed
- Improved device detection accuracy using Matomo DeviceDetector
- More reliable browser identification
- Better bot/crawler detection
- Simplified setup process (no database configuration needed)

### Dependencies
- Updated to Matomo DeviceDetector ^6.0
- Updated to TYPO3 12.4/13.4 LTS versions
- Updated contexts dependency to ^4.0
- Updated all dev dependencies to latest versions supporting PHP 8.2+

## [1.x] - Legacy (WURFL)

See GitHub releases for version 1.x changelog (WURFL-based implementation).
