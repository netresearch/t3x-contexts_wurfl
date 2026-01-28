.. include:: /Includes.rst.txt

.. _introduction:

============
Introduction
============

What does it do?
================

The Contexts Device Detection extension provides device-based context types for
the TYPO3 Contexts extension. It enables you to show or hide pages and content
elements based on the visitor's device type, browser, or operating system.

The extension analyzes the visitor's User-Agent header using the
`Matomo DeviceDetector <https://github.com/matomo-org/device-detector>`__ library,
which is the industry standard for PHP user-agent parsing.

Key features
============

Device type targeting
   Show content only to visitors on specific device types: mobile phones, tablets,
   desktop computers, or bots/crawlers. Perfect for responsive content strategies.

Browser targeting
   Target specific browsers like Chrome, Firefox, Safari, or Microsoft Edge.
   Useful for browser-specific features or compatibility notices.

No database required
   Unlike the legacy WURFL implementation, DeviceDetector uses bundled regex
   patterns. No external database, import commands, or scheduled updates needed.

Accurate detection
   Matomo DeviceDetector is actively maintained and updated frequently to support
   new devices, browsers, and operating systems. It powers millions of Matomo
   Analytics installations.

Bot detection
   Built-in detection for search engine bots and web crawlers. Show different
   content to bots or exclude them from certain features.

Use cases
=========

- **Responsive content**: Show simplified content to mobile users.
- **App promotion**: Display "Download our app" banners only on mobile devices.
- **Desktop features**: Show advanced interactive features only on desktop.
- **Browser compatibility**: Display browser upgrade notices for outdated browsers.
- **Bot handling**: Serve lightweight content to search engine crawlers.
- **Device-specific navigation**: Show mobile-optimized menus on phones.

What changed from WURFL?
========================

The 2.0 release is a complete rewrite that replaces the WURFL library with
Matomo DeviceDetector. Key changes:

No database required
   The legacy WURFL version required MySQL database tables and periodic data
   imports. The new version works out of the box with no database setup.

Simplified configuration
   Screen dimension filters (width/height) have been removed as modern responsive
   design handles this client-side. The configuration is now simpler and focused
   on device type and browser detection.

Better detection
   DeviceDetector is more frequently updated and has better coverage for modern
   devices and browsers.

MIT licensed
   DeviceDetector is MIT licensed with no commercial dependencies, unlike
   modern WURFL which requires a commercial license.

.. tip::

   If you're upgrading from the legacy WURFL version, see the
   :ref:`Migration Guide <migration>` for step-by-step instructions.

Requirements
============

- TYPO3 v12.4 LTS or v13.4 LTS.
- PHP 8.2 or higher.
- The `contexts <https://github.com/netresearch/t3x-contexts>`__ extension (v4.0+).

Technical details
=================

The extension uses Matomo DeviceDetector which:

- Parses User-Agent strings using bundled YAML regex patterns
- Detects 200+ browsers and 100+ operating systems
- Identifies 2000+ device models and brands
- Recognizes 400+ bot/crawler user agents
- Is updated weekly with new device signatures

Detection results are cached in the user session for performance.
