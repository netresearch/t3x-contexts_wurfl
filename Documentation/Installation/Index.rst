.. include:: /Includes.rst.txt

.. _installation:

============
Installation
============

.. _installation-requirements:

Requirements
============

.. csv-table:: Version compatibility
   :header: "Extension Version", "TYPO3", "PHP"
   :widths: 20, 30, 30

   "2.x", "12.4 LTS, 13.4 LTS", "8.2 - 8.5"
   "1.x (legacy)", "4.5 - 6.2", "5.3 - 5.6"

The recommended way to install this extension is via Composer.

.. _installation-composer:

Installation via Composer
=========================

.. code-block:: bash

   composer require netresearch/contexts-wurfl

After installation, activate the extension in the TYPO3 Extension Manager or
via CLI:

.. code-block:: bash

   vendor/bin/typo3 extension:activate contexts_wurfl

.. note::

   This extension requires the base ``contexts`` extension which will be
   installed automatically as a dependency.

.. _installation-no-database:

No database setup required
==========================

Unlike the legacy WURFL version, the new DeviceDetector-based implementation
does not require any database tables or import commands.

The device detection patterns are bundled with the ``matomo/device-detector``
library and are automatically updated when you update the library via Composer.

To update device detection patterns:

.. code-block:: bash

   composer update matomo/device-detector

.. tip::

   Run ``composer update matomo/device-detector`` periodically to get the latest
   device signatures. Matomo releases updates approximately weekly.

.. _installation-verification:

Verification
============

After installation, you should see:

1. New context types "Device Type" and "Browser" in the context creation wizard.
2. The extension should be listed in :guilabel:`Admin Tools > Extensions`.

To test device detection is working:

1. Go to :guilabel:`Admin Tools > Contexts`.
2. Create a new context of type "Device Type".
3. Enable "Mobile devices" checkbox.
4. Save the context.
5. Access your site from a mobile device (or use browser developer tools to
   emulate a mobile user agent).
6. Verify the context matches as expected.

.. _installation-upgrading:

Upgrading from legacy WURFL
===========================

If you're upgrading from the legacy WURFL-based version (1.x), see the
:ref:`Migration Guide <migration>` for detailed instructions.

Key points:

- The WURFL database tables are no longer used and can be removed.
- Some configuration options have changed.
- Screen dimension filters are no longer available.
