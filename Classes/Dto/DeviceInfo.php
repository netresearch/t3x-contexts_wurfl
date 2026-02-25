<?php

/*
 * Copyright (c) 2025-2026 Netresearch DTT GmbH
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

/**
 * This file is part of the package netresearch/contexts-wurfl.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Netresearch\ContextsDevice\Dto;

/**
 * Immutable value object for device detection results.
 *
 * This DTO encapsulates all device detection information extracted from
 * the Matomo DeviceDetector library. It replaces the legacy WURFL capability
 * lookups with a modern, type-safe API.
 *
 * @see https://github.com/matomo-org/device-detector
 */
final readonly class DeviceInfo
{
    public function __construct(
        /**
         * Whether the device is mobile (phone or tablet).
         */
        public bool $isMobile = false,

        /**
         * Whether the device is a tablet.
         */
        public bool $isTablet = false,

        /**
         * Whether the device is a desktop/laptop.
         */
        public bool $isDesktop = false,

        /**
         * Whether the user agent is a bot/crawler.
         */
        public bool $isBot = false,

        /**
         * Browser name (e.g., "Chrome", "Safari", "Firefox").
         */
        public ?string $browserName = null,

        /**
         * Browser version string (e.g., "120.0", "17.2").
         */
        public ?string $browserVersion = null,

        /**
         * Operating system name (e.g., "Windows", "iOS", "Android").
         */
        public ?string $osName = null,

        /**
         * Operating system version (e.g., "10", "17.2", "14").
         */
        public ?string $osVersion = null,

        /**
         * Device manufacturer/brand (e.g., "Apple", "Samsung", "Google").
         */
        public ?string $deviceBrand = null,

        /**
         * Device model name (e.g., "iPhone 15", "Galaxy S24", "Pixel 8").
         */
        public ?string $deviceModel = null,
    ) {}

    /**
     * Check if the device is a phone (mobile but not tablet).
     *
     * This is the WURFL "can_assign_phone_number" equivalent.
     */
    public function isPhone(): bool
    {
        return $this->isMobile && !$this->isTablet;
    }

    /**
     * Check if browser information is available.
     */
    public function hasBrowserInfo(): bool
    {
        return $this->browserName !== null;
    }

    /**
     * Check if operating system information is available.
     */
    public function hasOsInfo(): bool
    {
        return $this->osName !== null;
    }

    /**
     * Check if device brand/model information is available.
     */
    public function hasDeviceInfo(): bool
    {
        return $this->deviceBrand !== null || $this->deviceModel !== null;
    }
}
