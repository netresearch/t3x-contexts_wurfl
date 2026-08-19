<!-- Managed by agent: keep sections & order; edit content, not structure. Last updated: 2026-08-19 -->

# AGENTS.md — Documentation/

RST documentation for docs.typo3.org publication.

## Overview

```
Documentation/
├── Index.rst              # Main entry point
├── Sitemap.rst            # Sitemap
├── guides.xml             # PHP-based rendering config
├── Includes.rst.txt       # Shared includes
├── Introduction/          # Overview, device detection features
├── Installation/          # Setup instructions
├── Configuration/         # Device matching options
├── Migration/             # Upgrade from WURFL to device-detector
└── ContextTypes/          # Device context reference
```

## Setup

Rendering needs only Docker — no local toolchain. `CLAUDE.md` in this directory is a regular file (not a symlink): the docs renderer's file layer rejects symlinks inside `Documentation/`.

## Build & Tests

```bash
# Render locally with Docker
docker run --rm \
    -v ./Documentation:/project/docs \
    ghcr.io/typo3-documentation/render-guides:latest
```

Publication to docs.typo3.org happens via `release.yml`/`republish.yml` (target `docs`).

## Code Style & Conventions

- Use `.. confval::` for configuration options and `.. versionchanged::`/`.. versionadded::` for behavior changes (e.g. the 2.0.0 WURFL → device-detector switch).
- Heading underline characters follow the TYPO3 docs convention; keep `Includes.rst.txt` at the top of each file.
- Reference PHP symbols with their full namespace `Netresearch\ContextsDevice\...`.

## Security

- Never document real credentials; DDEV demo credentials (`admin`/`joh316`) are the only permitted example login.
- Do not embed third-party tracking or external scripts in doc pages.

## Examples

```rst
.. confval:: deviceType
   :type: array
   :Default: []

   Device types to match: mobile, tablet, desktop, bot.

.. versionchanged:: 2.0.0
   Replaced WURFL with matomo/device-detector for license compliance.
```

## PR/Commit Checklist

- [ ] RST renders without warnings (Docker render above)
- [ ] Device detection options documented
- [ ] Migration guide covers behavior changes for WURFL users
- [ ] README.md kept in sync with docs

## When Stuck

- TYPO3 docs authoring guide: https://docs.typo3.org/m/typo3/docs-how-to-document/main/en-us/
- RST directive reference: https://docs.typo3.org/m/typo3/docs-how-to-document/main/en-us/WritingReST/Index.html
- Rendering config lives in `guides.xml`
- Open a GitHub issue: https://github.com/netresearch/t3x-contexts_wurfl/issues

## House Rules

- Output directory: `Documentation-GENERATED-temp/` (git-ignored, never commit)
- Keep README.md synchronized with docs
- Document all supported device/OS/browser types
