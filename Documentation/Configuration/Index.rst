.. include:: /Includes.rst.txt

.. _configuration:

=============
Configuration
=============

The Contexts Device Detection extension requires minimal configuration. Device
detection works out of the box with sensible defaults.

.. _configuration-services:

Service configuration
=====================

The extension uses TYPO3's dependency injection for service configuration.
The default configuration in :file:`Configuration/Services.yaml` sets up
the services automatically.

The main service is ``DeviceDetectionService`` which wraps the Matomo
DeviceDetector library:

.. code-block:: yaml
   :caption: Configuration/Services.yaml

   services:
     _defaults:
       autowire: true
       autoconfigure: true
       public: false

     Netresearch\ContextsDevice\:
       resource: '../Classes/*'

     DeviceDetector\DeviceDetector:
       public: true

For advanced use cases, you can override the service configuration in your
own extension:

.. code-block:: yaml
   :caption: Configuration/Services.yaml (custom)

   services:
     Netresearch\ContextsDevice\Service\DeviceDetectionService:
       public: true
       arguments:
         $deviceDetector: '@DeviceDetector\DeviceDetector'

.. _configuration-caching:

Caching behavior
================

Device detection results are cached at two levels:

Session caching
   Each context match result is cached in the user session, following the
   standard Contexts extension caching behavior. This prevents repeated
   detection during a single visit.

Service-level caching
   The ``DeviceDetectionService`` maintains an in-memory cache of parsed
   user agents. When the same user agent is detected multiple times within
   a single request, the cached result is returned.

.. _configuration-bot-detection:

Bot detection
=============

The extension includes built-in bot detection. Bots are identified by their
User-Agent strings and include:

- Search engine crawlers (Googlebot, Bingbot, etc.)
- Social media crawlers (Facebook, Twitter, etc.)
- SEO tools and link checkers
- Monitoring services

To target bots, use the "Bots/Crawlers" option in the Device Type context.

.. important::

   When a bot is detected, the device type flags (mobile, tablet, desktop)
   may not be accurate. Bots typically don't have a meaningful device type.
   Use the bot detection option specifically when you need to handle bots.

.. _configuration-user-agent:

User-Agent handling
===================

The extension reads the User-Agent from the HTTP request headers via
TYPO3's PSR-7 request handling:

.. code-block:: php

   $userAgent = $request->getHeaderLine('User-Agent');

If no User-Agent is present (empty string), the context will not match
any device type.

.. _configuration-proxy:

Proxy considerations
====================

If your TYPO3 installation is behind a reverse proxy, ensure the proxy
forwards the original User-Agent header correctly. Most proxies do this
by default, but some may modify or strip headers.

Common proxy configurations:

.. csv-table:: Proxy header handling
   :header: "Proxy/CDN", "Default behavior"
   :widths: 30, 50

   "nginx", "Forwards User-Agent automatically"
   "Varnish", "Forwards User-Agent automatically"
   "Cloudflare", "Forwards User-Agent automatically"
   "AWS ALB/ELB", "Forwards User-Agent automatically"

.. note::

   Unlike IP-based detection (geolocation), device detection does not need
   to trust proxy headers for client IP detection. The User-Agent header
   is forwarded directly.
