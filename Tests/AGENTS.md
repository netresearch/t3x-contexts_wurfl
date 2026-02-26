<!-- Managed by agent: keep sections & order; edit content, not structure. Last updated: 2026-01-28 -->

# AGENTS.md — Tests/

Test suite for the Contexts Device Detection extension.

## Overview

```
Tests/
├── Unit/           # Fast, isolated unit tests
└── Architecture/   # PHPat architecture tests
```

## Build & Tests

```bash
# Run all tests
composer ci:test:php:unit

# Coverage (requires PCOV or Xdebug)
composer test:coverage

# Mutation testing
composer test:mutation
```

## Code Style & Conventions

### Test Class Naming

```php
Tests\Unit\Context\Type\DeviceContextTest
Tests\Unit\Service\DeviceDetectionServiceTest
Tests\Architecture\LayerTest
```

### User Agent Mocking

```php
// Mock user agent for device detection tests
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)...';
```

## PR/Commit Checklist

- [ ] New functionality has corresponding tests
- [ ] All tests pass: `composer ci:test:php:unit`
- [ ] Architecture tests pass with PHPat
- [ ] User agent fixtures cover major device types

## House Rules

- Unit tests must not require database or TYPO3 bootstrap
- Mock HTTP_USER_AGENT for consistent testing
- Include fixtures for mobile, tablet, desktop, bot detection
