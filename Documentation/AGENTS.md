<!-- Managed by agent: keep sections & order; edit content, not structure. Last updated: 2026-01-28 -->

# AGENTS.md — Documentation/

RST documentation for docs.typo3.org publication.

## Overview

```
Documentation/
├── Index.rst              # Main entry point
├── guides.xml             # PHP-based rendering config
├── Introduction/          # Overview, device detection features
├── Installation/          # Setup instructions
├── Configuration/         # Device matching options
├── Migration/             # Upgrade from WURFL to device-detector
└── ContextTypes/          # Device context reference
```

## Build & Tests

```bash
# Render locally with Docker
docker run --rm \
    -v ./Documentation:/project/docs \
    ghcr.io/typo3-documentation/render-guides:latest
```

## Code Style & Conventions

### Device Detection Documentation

```rst
.. confval:: deviceType
   :type: array
   :Default: []

   Device types to match: mobile, tablet, desktop, bot.

.. confval:: operatingSystem
   :type: array
   :Default: []

   Operating systems to match: iOS, Android, Windows.
```

### Migration Notes

Document the transition from WURFL to Matomo device-detector:

```rst
.. versionchanged:: 2.0.0
   Replaced WURFL with matomo/device-detector for license compliance.
```

## PR/Commit Checklist

- [ ] RST renders without warnings
- [ ] Device detection options documented
- [ ] Migration guide complete for WURFL users

## House Rules

- Output directory: `Documentation-GENERATED-temp/`
- Keep README.md synchronized with docs
- Document all supported device/OS/browser types
