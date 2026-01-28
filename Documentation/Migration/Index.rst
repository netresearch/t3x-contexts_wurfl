.. include:: /Includes.rst.txt

.. _migration:

==========================
Migration from Legacy WURFL
==========================

This guide helps you migrate from the legacy WURFL-based version (1.x) of the
contexts_wurfl extension to the new DeviceDetector-based version (2.x).

.. _migration-overview:

Overview of changes
===================

The 2.0 release is a complete rewrite with significant changes:

.. csv-table:: Version comparison
   :header: "Aspect", "1.x (Legacy WURFL)", "2.x (DeviceDetector)"
   :widths: 25, 35, 40

   "Detection library", "WURFL DB-API (TeraWurfl)", "Matomo DeviceDetector"
   "Database required", "Yes (MySQL tables)", "No"
   "Data updates", "Manual import command", "Composer update"
   "TYPO3 support", "4.5 - 6.2", "12.4 LTS, 13.4 LTS"
   "PHP support", "5.3 - 5.6", "8.2 - 8.5"
   "License", "WURFL (restrictive)", "MIT (permissive)"
   "Namespace", "Tx_ContextsWurfl_*", "Netresearch\\ContextsDevice\\*"

.. _migration-breaking:

Breaking changes
================

The following features are **NOT available** in version 2.x:

Screen dimension filters
------------------------

The legacy version allowed filtering by screen dimensions:

- ``screenWidthMin`` / ``screenWidthMax``
- ``screenHeightMin`` / ``screenHeightMax``

**These are no longer available.**

Modern WURFL requires a commercial license for device capability data, and
modern web development practices have moved to client-side responsive design
which handles screen dimensions via CSS media queries.

**Migration strategy**: Use CSS media queries for screen-size-dependent
layouts. If you need server-side screen detection, consider implementing
client-side detection that sends dimensions to the server.

Java/Flash detection
--------------------

The legacy WURFL capabilities for Java ME and Flash support are not available:

- ``j2me_midp_*`` capabilities
- ``full_flash_support`` capability

**Migration strategy**: These technologies are largely obsolete. Java ME and
Flash are no longer relevant for modern web development.

Detailed device capabilities
----------------------------

The exhaustive device capability database from WURFL is not available. This
includes specific hardware features, codec support, and other granular
capabilities.

**Migration strategy**: DeviceDetector provides device type, browser, OS,
brand, and model information which covers most use cases.

.. _migration-step-by-step:

Step-by-step migration
======================

Step 1: Update requirements
---------------------------

Ensure your environment meets the new requirements:

- TYPO3 v12.4 LTS or v13.4 LTS
- PHP 8.2 or higher
- Contexts extension v4.0 or higher

Step 2: Update via Composer
---------------------------

Update the extension to version 2.x:

.. code-block:: bash

   composer require "netresearch/contexts-wurfl:^2.0"

Step 3: Clear caches
--------------------

Clear all TYPO3 caches:

.. code-block:: bash

   vendor/bin/typo3 cache:flush

Step 4: Review existing contexts
--------------------------------

Review your existing device contexts in :guilabel:`Admin Tools > Contexts`.
The context type names may have changed:

.. csv-table:: Context type mapping
   :header: "Legacy type", "New type", "Notes"
   :widths: 30, 30, 40

   "WURFL Device", "Device Type", "Similar functionality"
   "WURFL Screen", "(removed)", "Use CSS media queries"

Step 5: Update context configuration
------------------------------------

For each device context, review and update the configuration:

**Legacy "is_wireless" option:**
   Use "Mobile devices" in the new version. This matches the same devices.

**Legacy "is_phone" option:**
   Still available as "Phones" in the new version. Matches mobile devices
   that are not tablets.

**Legacy "is_tablet" option:**
   Still available as "Tablets" in the new version.

**Legacy "is_smarttv" option:**
   Smart TV detection is available via DeviceDetector but not currently
   exposed as a checkbox. Contact the maintainers if you need this feature.

**Legacy screen dimension options:**
   Remove these contexts or replace with client-side solutions.

Step 6: Remove database tables (optional)
-----------------------------------------

The WURFL database tables are no longer used. You can safely remove them:

.. code-block:: sql

   -- These tables can be dropped (backup first if needed)
   DROP TABLE IF EXISTS tx_contextswurfl_wurfl;
   DROP TABLE IF EXISTS tx_contextswurfl_wurfl_merge;
   DROP TABLE IF EXISTS tx_contextswurfl_wurfl_index;

Step 7: Remove scheduled tasks
------------------------------

If you had scheduled tasks for WURFL database updates, remove them. The new
version updates via Composer:

.. code-block:: bash

   # No longer needed:
   # typo3 contexts:wurfl:import

   # New approach - update device patterns via Composer:
   composer update matomo/device-detector

Step 8: Test thoroughly
-----------------------

Test your site with various devices:

1. Desktop browsers (Chrome, Firefox, Safari, Edge)
2. Mobile phones (iPhone, Android phones)
3. Tablets (iPad, Android tablets)
4. Bot/crawler user agents

Use browser developer tools to emulate different devices and verify contexts
match as expected.

.. _migration-namespace:

Namespace changes
=================

If you have custom code that extends or uses extension classes:

.. csv-table:: Namespace mapping
   :header: "Legacy", "New"
   :widths: 50, 50

   "Tx_ContextsWurfl_Context_Type_Wurfl", "Netresearch\\ContextsDevice\\Context\\Type\\DeviceContext"
   "(not available)", "Netresearch\\ContextsDevice\\Context\\Type\\BrowserContext"
   "(not available)", "Netresearch\\ContextsDevice\\Service\\DeviceDetectionService"
   "(not available)", "Netresearch\\ContextsDevice\\Dto\\DeviceInfo"

Example migration:

.. code-block:: php

   // Legacy code
   use Tx_ContextsWurfl_Context_Type_Wurfl;

   // New code
   use Netresearch\ContextsDevice\Context\Type\DeviceContext;

.. _migration-new-features:

New features in 2.x
===================

The new version adds features not available in the legacy version:

Browser context type
   Match visitors based on their browser name. Configure a list of browsers
   to target (Chrome, Firefox, Safari, etc.).

Bot detection
   Built-in detection for search engine bots and crawlers. Show different
   content to bots or exclude them from certain features.

Desktop detection
   Explicit "Desktop/Laptop" option to match non-mobile devices. The legacy
   version required inverting mobile detection.

No database maintenance
   Device patterns are bundled with the library. No database imports,
   scheduled tasks, or maintenance required.

.. _migration-help:

Getting help
============

If you encounter issues during migration:

1. Check the `GitHub issues <https://github.com/netresearch/t3x-contexts_wurfl/issues>`__
   for known problems and solutions.
2. Open a new issue with details about your migration scenario.
3. Contact `Netresearch <https://www.netresearch.de/>`__ for commercial support.
