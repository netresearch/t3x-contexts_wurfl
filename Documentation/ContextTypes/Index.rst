.. include:: /Includes.rst.txt

.. _context-types:

=============
Context types
=============

The Contexts Device Detection extension provides two context types for
device-based content targeting.

.. _context-types-device:

Device Type context
===================

Matches visitors based on their device type (mobile, tablet, desktop, or bot).

Configuration
-------------

.. confval:: field_is_mobile
   :name: confval-device-field-is-mobile
   :type: boolean
   :Default: false

   Match all mobile devices. This includes both phones and tablets.

   Use this option for broad mobile targeting when you don't need to
   distinguish between phones and tablets.

.. confval:: field_is_phone
   :name: confval-device-field-is-phone
   :type: boolean
   :Default: false

   Match phones specifically. This matches mobile devices that are NOT tablets.

   Equivalent to the legacy WURFL ``can_assign_phone_number`` capability.

.. confval:: field_is_tablet
   :name: confval-device-field-is-tablet
   :type: boolean
   :Default: false

   Match tablets specifically. This matches devices identified as tablets
   (iPad, Android tablets, etc.).

.. confval:: field_is_desktop
   :name: confval-device-field-is-desktop
   :type: boolean
   :Default: false

   Match desktop and laptop computers. This includes Windows PCs, Macs,
   and Linux desktops.

.. confval:: field_is_bot
   :name: confval-device-field-is-bot
   :type: boolean
   :Default: false

   Match bots and crawlers. This includes search engine crawlers (Googlebot),
   social media crawlers, monitoring services, and other automated agents.

Match logic
-----------

The context matches if **ANY** of the selected device types matches the
detected device. This is OR logic:

- If you select both "Mobile devices" and "Desktop/Laptop", the context
  matches visitors on either mobile or desktop devices.
- If you only select "Tablets", the context only matches tablet users.
- If you select nothing, the context never matches.

Example: Mobile-only content
----------------------------

To create a context that matches visitors on mobile phones (not tablets):

1. Go to :guilabel:`Admin Tools > Contexts`.
2. Create a new context.
3. Select type "Device Type".
4. Enable only the "Phones" checkbox.
5. Save the context.

Example: Desktop with fallback
------------------------------

To show content to desktop users OR tablet users (larger screens):

1. Go to :guilabel:`Admin Tools > Contexts`.
2. Create a new context.
3. Select type "Device Type".
4. Enable "Desktop/Laptop" and "Tablets" checkboxes.
5. Save the context.

.. tip::

   Use the "Invert match" option from the base Contexts extension to match
   the opposite. For example, enable "Mobile devices" with invert to match
   non-mobile visitors.

.. _context-types-browser:

Browser context
===============

Matches visitors based on their browser name.

Configuration
-------------

.. confval:: field_browsers
   :name: confval-browser-field-browsers
   :type: string
   :Default: (empty)

   Comma-separated list of browser names to match.

   The matching is case-insensitive. Browser names must match the names
   used by Matomo DeviceDetector.

Common browser names
--------------------

The following browser names are commonly detected:

.. csv-table:: Desktop browsers
   :header: "Browser name", "Notes"
   :widths: 30, 50

   "Chrome", "Google Chrome desktop"
   "Firefox", "Mozilla Firefox"
   "Safari", "Apple Safari on macOS"
   "Microsoft Edge", "Microsoft Edge (Chromium-based)"
   "Opera", "Opera browser"
   "Internet Explorer", "Legacy IE (rare now)"

.. csv-table:: Mobile browsers
   :header: "Browser name", "Notes"
   :widths: 30, 50

   "Mobile Safari", "Safari on iOS devices"
   "Chrome Mobile", "Chrome on Android/iOS"
   "Firefox Mobile", "Firefox on Android"
   "Samsung Browser", "Samsung's Android browser"
   "Opera Mobile", "Opera on mobile devices"

.. note::

   Browser names from DeviceDetector may differ from user expectations.
   For example, Safari on iPhone is detected as "Mobile Safari", not "Safari".

Match logic
-----------

The context matches if the detected browser name matches **ANY** of the
configured browser names (case-insensitive).

Example: Chrome users
---------------------

To target all Chrome users (desktop and mobile):

1. Go to :guilabel:`Admin Tools > Contexts`.
2. Create a new context.
3. Select type "Browser".
4. Enter: ``Chrome, Chrome Mobile``
5. Save the context.

Example: Legacy browser notice
------------------------------

To show a notice to Internet Explorer users:

1. Go to :guilabel:`Admin Tools > Contexts`.
2. Create a new context.
3. Select type "Browser".
4. Enter: ``Internet Explorer``
5. Save the context.
6. Create a content element with the notice.
7. Enable visibility for this context.

.. _context-types-common:

Common features
===============

All device detection context types share these features:

Invert match
   All context types support the "Invert match" option from the base Contexts
   extension. When enabled, the context matches when the condition is NOT met.

Session caching
   Detection results are cached in the user session to avoid repeated
   user-agent parsing during a single visit.

Combining contexts
   Device contexts can be combined with other contexts using the "Combination"
   context type from the base extension. For example, create a context that
   matches "Mobile visitors from Germany" by combining a Device context with
   a Geolocation context.

Empty User-Agent handling
   If the request has no User-Agent header, device contexts will not match.
   This is typically rare and may indicate bot traffic or unusual clients.
