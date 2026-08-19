<!-- Managed by agent: keep sections & order; edit content, not structure. Last updated: 2026-08-19 -->

# AGENTS.md — Configuration/

TYPO3 configuration files for the Contexts Device Detection extension.

## Overview

```
Configuration/
├── TCA/Overrides/
│   └── tx_contexts_contexts.php   # Registers Device/Browser context types
├── FlexForms/
│   ├── Device.xml                 # Device context configuration form
│   └── Browser.xml                # Browser context configuration form
├── Services.yaml                  # Symfony DI (autowire; DeviceDetectionService is public)
└── Icons.php                      # Icon registry
```

## Setup

No build step — TYPO3 reads these files directly. After changing `Services.yaml`, flush the DI cache (`ddev exec vendor/bin/typo3 cache:flush` in a DDEV instance, or reinstall via `ddev install-all`).

## Build & Tests

```bash
composer ci:test:php:functional   # functional tests cover TCA registration + context matching
composer ci:test:php:cgl          # style check also covers PHP files here
```

## Code Style & Conventions

- Context types are registered in `TCA/Overrides/tx_contexts_contexts.php` via `Netresearch\Contexts\Api\Configuration::registerContextType()` with an `LLL:EXT:contexts_wurfl/...` label and a `FILE:EXT:...` FlexForm reference.
- FlexForm option values (e.g. device types `mobile`, `tablet`, `desktop`) must match what `DeviceInfo`/the context types evaluate.
- `DeviceDetectionService` must stay `public: true` in `Services.yaml` — context types are built via `GeneralUtility::makeInstance()` by the base extension and resolve it from the container themselves (see the comment in `Services.yaml`).

## Security

- FlexForm values are user-supplied backend input; context types must validate/whitelist them, never eval or interpolate them into SQL.
- Keep `public: false` as the service default; expose only what `makeInstance` consumers need.
- No credentials or environment-specific values in configuration files.

## Examples

```php
// Good: TCA/Overrides/tx_contexts_contexts.php — register via the base extension API
Configuration::registerContextType(
    'device',
    'LLL:EXT:contexts_wurfl/Resources/Private/Language/locallang.xlf:context.device.title',
    DeviceContext::class,
    'FILE:EXT:contexts_wurfl/Configuration/FlexForms/Device.xml',
);

// Bad: appending to $GLOBALS['TCA'] directly, hardcoded English labels,
// or registering a context type without a FlexForm.
```

## PR/Commit Checklist

- [ ] Device context types registered in TCA/Overrides
- [ ] FlexForms have language file references
- [ ] `Services.yaml` changes covered by functional tests

## When Stuck

- Base extension registration pattern: `.Build/vendor/netresearch/contexts/Configuration/` (after `composer install`)
- TYPO3 FlexForm reference: https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/FlexForms/Index.html
- Open a GitHub issue: https://github.com/netresearch/t3x-contexts_wurfl/issues

## House Rules

- Device types: mobile, tablet, desktop, bot, tv, console
- Support OS detection: iOS, Android, Windows, macOS, Linux
- Support browser detection: Chrome, Safari, Firefox, Edge
