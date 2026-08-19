<!-- Managed by agent: keep sections & order; edit content, not structure. Last updated: 2026-08-19 -->

# AGENTS.md

**Project:** `netresearch/contexts-wurfl` — device detection context types for TYPO3 (extension key `contexts_wurfl`, PHP namespace `Netresearch\ContextsDevice`)
**Type:** TYPO3 CMS Extension (PHP ^8.2, TYPO3 12.4/13.4 — version: see `ext_emconf.php`)

Device detection uses `matomo/device-detector` — the historical WURFL library was replaced in v2.0.0 (see `docs/adr/0001-replace-wurfl-with-device-detector.md` and `Documentation/Migration/Index.rst`). Component map: `docs/ARCHITECTURE.md`.

## Precedence

The **closest AGENTS.md** to changed files wins. This root file holds global defaults only.

## Global Rules

- Keep PRs small (~300 net LOC)
- Conventional Commits: `type(scope): subject`
- Ask before: heavy dependencies, architecture changes, new context types
- Never commit secrets, credentials, or PII
- Update AGENTS.md/docs in the same PR when commands, CI, or structure change (`Build/Scripts/verify-harness.sh` checks this in CI)

## Commands

```bash
composer ci:test:php:unit        # PHPUnit unit tests
composer ci:test:php:functional  # PHPUnit functional tests (needs DB)
composer ci:test:php:phpstan     # PHPStan (Build/phpstan.neon)
composer ci:test:php:cgl         # PHP-CS-Fixer check (dry-run)
composer ci:test:php:rector      # Rector check (dry-run, Build/rector.php)
composer ci:cgl                  # Fix code style
composer ci:rector               # Apply Rector rules
composer test:coverage           # Coverage report (needs Xdebug)
composer test:mutation           # Infection mutation testing
```

The `Makefile` mirrors these: `make cgl | cgl-fix | phpstan | rector | rector-fix | test | test-unit | test-functional`.

## Development Environment

```bash
ddev start
ddev install-all          # Install TYPO3 v12 + v13 test instances

# Backends (credentials: admin / joh316)
https://v12.contexts-wurfl.ddev.site/typo3/
https://v13.contexts-wurfl.ddev.site/typo3/
```

## CI Workflows

| Workflow | Trigger | Purpose |
|----------|---------|---------|
| `ci.yml` | push/PR/merge_group/weekly | Unit+functional matrix (PHP 8.3–8.5 × TYPO3 ^12.4/^13.4) via `netresearch/typo3-ci-workflows` |
| `checks.yml` | push/PR/merge_group/weekly | Security/quality: CodeQL, gitleaks, zizmor, fuzz, license check, Scorecard, dependency review, PR quality gate |
| `harness-verify.yml` | push/PR | Agent-harness consistency (`Build/Scripts/verify-harness.sh`) |
| `release.yml` | tag `v*` | Release to TER/Packagist/docs via `release-typo3-extension.yml` |
| `republish.yml` | manual | Re-run publish targets (ter/docs/packagist) for an existing tag |

## Project Structure

```
Classes/
├── Context/
│   ├── DeviceDetectionAwareTrait.php  # Request/device-info resolution shared by context types
│   └── Type/                          # DeviceContext, BrowserContext
├── Dto/                               # DeviceInfo (readonly)
└── Service/                           # DeviceDetectionService (matomo/device-detector wrapper)
Configuration/          # TCA/Overrides, FlexForms, Services.yaml, Icons.php
Tests/                  # Unit/, Functional/, Architecture/ (PHPat)
Documentation/          # RST docs for docs.typo3.org
docs/                   # ADRs, ARCHITECTURE.md, exec-plans/
Build/                  # phpunit/phpstan/rector configs, Scripts/
```

## Index of Scoped AGENTS.md

| Path | Purpose |
|------|---------|
| [Classes/AGENTS.md](./Classes/AGENTS.md) | PHP source: context types, detection service, DTOs |
| [Configuration/AGENTS.md](./Configuration/AGENTS.md) | TCA overrides, FlexForms, DI configuration |
| [Tests/AGENTS.md](./Tests/AGENTS.md) | Unit, functional, and architecture tests |
| [Documentation/AGENTS.md](./Documentation/AGENTS.md) | RST documentation for docs.typo3.org |

## Dependencies

- `netresearch/contexts` `^3.1.1 || ^4.0` — base contexts extension (`AbstractContext`)
- `matomo/device-detector` `^6.0` — user agent parsing (regex-based, no database)

## Key Concepts

- `DeviceContext` matches by device type (mobile, tablet, desktop, …); `BrowserContext` matches by browser name. Both extend `AbstractContext` from `netresearch/contexts`.
- `DeviceDetectionService` wraps Matomo DeviceDetector; results travel as the readonly `DeviceInfo` DTO.
- WURFL capability mapping and its limitations (e.g. no screen dimensions) are documented in `Documentation/Migration/Index.rst`.

## When Instructions Conflict

Nearest AGENTS.md wins. User prompts override files.

## Resources

- [Matomo DeviceDetector](https://github.com/matomo-org/device-detector)
- [Base Extension](https://github.com/netresearch/t3x-contexts)
- [TYPO3 Coding Guidelines](https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/CodingGuidelines/Index.html)
- [GitHub Issues](https://github.com/netresearch/t3x-contexts_wurfl/issues)

## Commit Signing

Signed commits are required: `git commit -S --signoff`. The `require-signed-commits` ruleset on the default branch rejects unsigned commits at merge time, and the DCO check additionally requires the `Signed-off-by` trailer. Quickest setup is SSH signing — register your SSH key as a *signing key* on your GitHub account, then `git config --global gpg.format ssh && git config --global user.signingkey ~/.ssh/<key>.pub`.
