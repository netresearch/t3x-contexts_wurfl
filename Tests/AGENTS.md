<!-- Managed by agent: keep sections & order; edit content, not structure. Last updated: 2026-08-19 -->

# AGENTS.md — Tests/

Test suite for the Contexts Device Detection extension.

## Overview

```
Tests/
├── Unit/           # Fast, isolated unit tests (no DB, no TYPO3 bootstrap)
├── Functional/     # typo3/testing-framework functional tests (DB, Fixtures/)
└── Architecture/   # PHPat architecture tests (LayerTest)
```

## Setup

```bash
composer install                  # unit tests run out of the box
# functional tests need a database; in CI the reusable workflow provides MySQL 8.4
```

## Build & Tests

```bash
composer ci:test:php:unit        # unit tests (Build/phpunit/UnitTests.xml)
composer ci:test:php:functional  # functional tests (Build/phpunit/FunctionalTests.xml, needs DB)
composer test:coverage           # coverage (requires Xdebug)
composer test:mutation           # Infection mutation testing
```

## Code Style & Conventions

- Test namespace mirrors the source: `Netresearch\ContextsDevice\Tests\{Unit,Functional,Architecture}\...`, class name `<Subject>Test`.
- Mock the user agent via `$_SERVER['HTTP_USER_AGENT']` (or a PSR-7 request attribute) — never depend on the real environment.
- Functional fixtures live in `Functional/Fixtures/` (CSV, e.g. `tx_contexts_contexts.csv`).
- Architecture rules in `Architecture/LayerTest.php`: context types extend `AbstractContext`, DTOs readonly, services final.

## Security

- Include hostile user-agent fixtures (oversized strings, control characters) — the UA header is attacker-controlled input.
- Never commit real visitor data or PII as fixtures; use synthetic user agents.
- Expected error paths are asserted, not suppressed — test output stays clean.

## Examples

```php
// Good: deterministic UA fixture per test
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)...';

// Bad: asserting on the machine's own user agent, or sharing one service
// instance across tests (its per-UA cache bleeds state between tests).
```

## PR/Commit Checklist

- [ ] New functionality has corresponding tests
- [ ] All tests pass: `composer ci:test:php:unit` (+ functional if touched)
- [ ] Architecture tests pass with PHPat
- [ ] User agent fixtures cover major device types

## When Stuck

- typo3/testing-framework docs: https://github.com/TYPO3/testing-framework
- PHPat selectors/rules: https://github.com/carlosas/phpat
- CI matrix definition: `.github/workflows/ci.yml` (PHP 8.3–8.5 × TYPO3 ^12.4/^13.4)
- Open a GitHub issue: https://github.com/netresearch/t3x-contexts_wurfl/issues

## House Rules

- Unit tests must not require database or TYPO3 bootstrap
- Mock HTTP_USER_AGENT for consistent testing
- Include fixtures for mobile, tablet, desktop, bot detection
