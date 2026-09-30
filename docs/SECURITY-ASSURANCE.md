<!-- SPDX-License-Identifier: AGPL-3.0-or-later -->
<!-- SPDX-FileCopyrightText: Netresearch DTT GmbH -->
# Security assurance

What users of `contexts_wurfl` can and cannot expect in terms of security, and the argument for it: the data the extension processes, where its detection data comes from, the threat model, trust boundaries, the design principles applied and how common weaknesses are countered. Every claim names the file that implements it. Components and data flow: [ARCHITECTURE.md](ARCHITECTURE.md). Vulnerability reporting: [SECURITY.md](../SECURITY.md).

The document describes the code on `main`. Statements about dependencies refer to the versions a `composer install` resolved on 2026-09-30: `matomo/device-detector` 6.5.2, `netresearch/contexts` 4.0.0, TYPO3 13.4.35.

## What the extension does, security-wise

It adds two context types to [netresearch/contexts](https://github.com/netresearch/t3x-contexts): `device` and `browser` (`Configuration/TCA/Overrides/tx_contexts_contexts.php`). When the base extension evaluates a context of these types during a frontend request, the extension reads the visitor's `User-Agent` header, classifies it with the `matomo/device-detector` library and answers whether the configured device types or browser names match. The base extension then shows or hides pages and content elements accordingly.

Despite its extension key, the extension no longer uses WURFL. Version 2.0.0 replaced it with Matomo DeviceDetector ([ADR-0001](adr/0001-replace-wurfl-with-device-detector.md)); no WURFL code, data or table definition remains (the extension has no `ext_tables.sql`).

## Detection data: source and license

- **Source.** The device, browser, operating-system and bot patterns are YAML regex files shipped inside the Composer package `matomo/device-detector` (required as `^6.0` in `composer.json`). The library reads them from its own package directory (`regexes/`, loaded by `Parser/AbstractParser.php` of the library). The extension adds no patterns of its own and downloads nothing at runtime; there is no database and no import command.
- **Updates.** The patterns change only when the package is updated through Composer. Renovate proposes those updates (`renovate.json`, extending `netresearch/renovate-config`); the site operator installs them with `composer update`.
- **License.** `matomo/device-detector` is licensed under LGPL-3.0-or-later (its `composer.json` and `LICENSE`); every regex file carries the same license in its header. The extension itself is AGPL-3.0-or-later (`composer.json`, `LICENSE`).

## Request data processed

- **One header.** `DeviceDetectionService::detectFromRequest()` reads only `User-Agent` from the PSR-7 request (`Classes/Service/DeviceDetectionService.php`, line 67). The request comes from `$GLOBALS['TYPO3_REQUEST']` (`Classes/Context/DeviceDetectionAwareTrait.php`, line 56). No other header, cookie, query parameter or IP address is read. User-Agent Client Hints are not passed to the library: `DeviceDetector` is built by the container without arguments (`Configuration/Services.yaml`, lines 18-19) and the service calls `setUserAgent()` but never `setClientHints()`.
- **No storage, no logging, no transmission.** `Classes/` contains no database query, file write, logger, network call or output of the header. The parsed result is a `final readonly` value object (`Classes/Dto/DeviceInfo.php`) that stays in memory.
- **In-memory cache.** The service keeps parsed results in an array keyed by the User-Agent string for the lifetime of the service object (`DeviceDetectionService.php`, lines 41 and 86-99). The object lives in the TYPO3 dependency-injection container of the running PHP process; nothing is written to a TYPO3 cache backend.
- **Session.** When a context record has "use session" enabled, the base extension stores the match result — a boolean, not the User-Agent — in the frontend user session under `contexts-<uid>-<tstamp>` (`AbstractContext::storeInSession()` in `netresearch/contexts`).

## Security expectations

Users can expect:

- **The extension reads only the User-Agent header** and keeps it in memory only, as described above.
- **No match without data.** A context does not match when nothing is configured (`Classes/Context/Type/DeviceContext.php`, line 81; `Classes/Context/Type/BrowserContext.php`, line 78), when no request is available or the User-Agent is empty (`DeviceContext.php`, line 88; `BrowserContext.php`, line 85; `DeviceDetectionService.php`, line 82). The context's "invert" option turns that result around by design (`AbstractContext::invert()`).
- **Bot matching is opt-in.** Bots match only when the editor ticks `field_is_bot` (`Configuration/FlexForms/Device.xml`, `DeviceContext.php`, line 78).
- **Editor input is compared, not interpreted.** The browser list is split at commas, trimmed, lower-cased and compared with `in_array(..., true)` against the detected browser name (`BrowserContext.php`, lines 100-131). It is never used as a regular expression, in a query or in output.

Users cannot expect:

- **Access control.** The User-Agent header is chosen by the client. Any visitor can send a string that makes a device or browser context match or not match. Use these contexts for presentation and targeting only; never to hide content that must stay confidential.
- **Exact detection.** Classification is only as good as the patterns in `matomo/device-detector`. Unknown or new devices may be classified wrongly or not at all.
- **Protection of the User-Agent beyond this extension.** The extension does not store the header, but TYPO3, the web server or other extensions may log it.

## Threat model and trust boundaries

| Boundary | Untrusted input | Control |
|----------|-----------------|---------|
| Visitor → frontend request | `User-Agent` header | Read once per request (`DeviceDetectionService.php`, line 67), passed as data to the library, never output or persisted by the extension |
| Backend editor → context record | FlexForm values (`field_is_*` check boxes, `field_browsers` text) | TYPO3 backend authentication and record permissions of the base extension's `tx_contexts_contexts` table; check boxes are compared with `'1'` (`DeviceContext.php`, line 110), the browser list is compared literally (`BrowserContext.php`, lines 100-131) |
| Package registry → installation | `matomo/device-detector` code and regex data, `netresearch/contexts` | Composer Audit, Dependency Review and the PHP license check on every pull request (`.github/workflows/checks.yml`); Renovate updates |
| Extension → base extension | Match result | Returned as a boolean; the base extension decides visibility and session storage |

Attackers considered: a visitor who spoofs the User-Agent to receive another variant of a page (accepted, see "Users cannot expect"), a visitor who sends a crafted User-Agent to reach code paths other than classification, and a compromised or vulnerable dependency. TYPO3 administrators, backend editors with write access to context records, the server and its PHP configuration are trusted.

`Build/Scripts/router.php` is a development router for the PHP built-in web server and is not loaded by the extension. It serves static files only when their resolved path lies inside `.Build/Web` (lines 41-50).

## Secure design principles applied

- **Least privilege:** the extension reads one request header and has no database table, file access, network access or backend module. Services are private by default (`Configuration/Services.yaml`, line 7); only `DeviceDetectionService` is public, because context types built by the base extension resolve it from the container (lines 21-27).
- **Fail-safe defaults:** an unconfigured context, a missing request and an empty User-Agent all yield "no match"; bot matching is off unless selected.
- **Economy of mechanism:** all parsing goes through one service (`DeviceDetectionService`) and one trait (`DeviceDetectionAwareTrait`); both context types reuse them.
- **Immutability:** detection results travel as a `final readonly` DTO. PHPat rules in `Tests/Architecture/LayerTest.php`, evaluated by PHPStan, require DTOs to be readonly and services to be final.

## Countering common weaknesses

| Weakness (CWE / OWASP) | Counter |
|------------------------|---------|
| Reliance on untrusted inputs in a security decision (CWE-807) | Documented above: device and browser contexts are not an access control |
| Cross-site scripting (CWE-79, A03:2021) | The extension renders no output; the User-Agent and the detection result are not written to any response |
| SQL injection (CWE-89, A03:2021) | `Classes/` issues no database queries |
| Inefficient regular expression from configuration (CWE-1333) | Editor input is compared with `in_array`, never compiled as a pattern (`BrowserContext.php`, lines 122-131) |
| Hard-coded credentials (CWE-798) | None in the code; Betterleaks scans every pull request (`.github/workflows/checks.yml`) |
| Vulnerable and outdated components (A06:2021) | Composer Audit and Dependency Review on every pull request (`checks.yml`), Renovate updates (`renovate.json`) |

Static checks on every pull request (Opengrep and PHPStan level 10 on `Classes/`, `Configuration/` and `Tests/`) and the unit and functional suites (`Tests/`) back these claims; for example `DeviceDetectionServiceTest::detectFromUserAgentReturnsNullForEmptyUserAgent` and `BrowserContextTest::matchReturnsFalseWhenNoRequestAvailable`. See "Governance and policies" in [CONTRIBUTING.md](../CONTRIBUTING.md).
