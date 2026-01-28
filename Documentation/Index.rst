.. include:: /Includes.rst.txt

.. _start:

=========================
Contexts: Device Detection
=========================

:Extension key:
   contexts_wurfl

:Package name:
   netresearch/contexts-wurfl

:Version:
   |release|

:Language:
   en

:Author:
   Netresearch DTT GmbH

:License:
   This document is published under the
   `Creative Commons BY 4.0 <https://creativecommons.org/licenses/by/4.0/>`__
   license.

:Rendered:
   |today|

----

Device detection context types for TYPO3. Show pages and content elements
based on the visitor's device type (mobile, tablet, desktop), browser, or
operating system. Uses Matomo DeviceDetector for accurate user-agent parsing.

.. versionadded:: 2.0.0
   Complete rewrite for TYPO3 12.4/13.4 LTS. Now uses Matomo DeviceDetector
   library instead of the legacy WURFL database.

----

.. card-grid::
   :columns: 1
   :columns-md: 2
   :gap: 4
   :class: pb-4
   :card-height: 100

   .. card:: :ref:`Introduction <introduction>`

      Learn what the Device Detection extension does and how it enables
      device-based content targeting in TYPO3.

   .. card:: :ref:`Installation <installation>`

      Install the extension and get started with device detection.

   .. card:: :ref:`Configuration <configuration>`

      Understand the extension's configuration options.

   .. card:: :ref:`Context types <context-types>`

      Explore the two device detection context types: Device Type and Browser.

   .. card:: :ref:`Migration from WURFL <migration>`

      Migrate from the legacy WURFL-based version to the new DeviceDetector
      implementation.

.. toctree::
   :maxdepth: 2
   :titlesonly:
   :hidden:

   Introduction/Index
   Installation/Index
   Configuration/Index
   ContextTypes/Index
   Migration/Index

----

**Credits**

Developed and maintained by `Netresearch DTT GmbH <https://www.netresearch.de/>`__.

.. Meta Menu

.. toctree::
   :hidden:

   Sitemap
