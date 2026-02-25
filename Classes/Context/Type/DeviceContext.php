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

namespace Netresearch\ContextsDevice\Context\Type;

use Netresearch\Contexts\Context\AbstractContext;
use Netresearch\ContextsDevice\Dto\DeviceInfo;
use Netresearch\ContextsDevice\Service\DeviceDetectionService;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Context type that matches based on device type (mobile/tablet/desktop/bot).
 *
 * Matches when the visitor's device type (detected via User-Agent analysis)
 * is any of the configured device types. This replaces the legacy WURFL-based
 * device detection with the modern Matomo DeviceDetector library.
 *
 * Configuration (via FlexForm):
 * - field_is_mobile: Match mobile devices (phones and tablets)
 * - field_is_phone: Match phones specifically (mobile but not tablet)
 * - field_is_tablet: Match tablets specifically
 * - field_is_desktop: Match desktop/laptop devices
 * - field_is_bot: Match bots/crawlers
 *
 * The context matches if ANY of the selected device types matches the current device.
 *
 * @author Netresearch DTT GmbH
 * @link https://www.netresearch.de
 */
class DeviceContext extends AbstractContext
{
    protected ?DeviceDetectionService $deviceDetectionService = null;

    /**
     * @param array<string, mixed> $arRow Database context row
     */
    public function __construct(array $arRow = [], ?DeviceDetectionService $deviceDetectionService = null)
    {
        parent::__construct($arRow);

        $this->deviceDetectionService = $deviceDetectionService;
    }

    /**
     * Check if the context matches the current request.
     *
     * Matches if ANY of the configured device types matches the detected device.
     *
     * @param array<int|string, mixed> $arDependencies Array of dependent context objects
     * @return bool True if the visitor's device type matches any configured type
     */
    public function match(array $arDependencies = []): bool
    {
        // Check session cache first
        [$bUseSession, $bMatch] = $this->getMatchFromSession();
        if ($bUseSession) {
            return $this->invert((bool) $bMatch);
        }

        // Get configured device types
        $matchMobile = $this->isConfigured('field_is_mobile');
        $matchPhone = $this->isConfigured('field_is_phone');
        $matchTablet = $this->isConfigured('field_is_tablet');
        $matchDesktop = $this->isConfigured('field_is_desktop');
        $matchBot = $this->isConfigured('field_is_bot');

        // If nothing is configured, context doesn't match
        if (!$matchMobile && !$matchPhone && !$matchTablet && !$matchDesktop && !$matchBot) {
            return $this->storeInSession($this->invert(false));
        }

        // Get device info
        $deviceInfo = $this->getDeviceInfo();

        if ($deviceInfo === null) {
            return $this->storeInSession($this->invert(false));
        }

        // Check each configured device type (OR logic)
        $bMatch = $this->matchesAnyConfiguredType(
            $deviceInfo,
            $matchMobile,
            $matchPhone,
            $matchTablet,
            $matchDesktop,
            $matchBot,
        );

        return $this->storeInSession($this->invert($bMatch));
    }

    /**
     * Get the device detection service, with lazy initialization fallback.
     */
    protected function getDeviceDetectionService(): DeviceDetectionService
    {
        if ($this->deviceDetectionService === null) {
            $this->deviceDetectionService = GeneralUtility::makeInstance(DeviceDetectionService::class);
        }

        return $this->deviceDetectionService;
    }

    /**
     * Get the current HTTP request.
     */
    protected function getRequest(): ?ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? null;
    }

    /**
     * Get device information from the current request.
     */
    protected function getDeviceInfo(): ?DeviceInfo
    {
        $request = $this->getRequest();

        if ($request === null) {
            return null;
        }

        return $this->getDeviceDetectionService()->detectFromRequest($request);
    }

    /**
     * Check if a configuration field is enabled (value "1").
     */
    protected function isConfigured(string $fieldName): bool
    {
        return $this->getConfValue($fieldName) === '1';
    }

    /**
     * Check if device matches any of the configured types.
     */
    private function matchesAnyConfiguredType(
        DeviceInfo $deviceInfo,
        bool $matchMobile,
        bool $matchPhone,
        bool $matchTablet,
        bool $matchDesktop,
        bool $matchBot,
    ): bool {
        // Check mobile (includes both phones and tablets)
        if ($matchMobile && $deviceInfo->isMobile) {
            return true;
        }

        // Check phone specifically (mobile but not tablet)
        if ($matchPhone && $deviceInfo->isPhone()) {
            return true;
        }

        // Check tablet specifically
        if ($matchTablet && $deviceInfo->isTablet) {
            return true;
        }

        // Check desktop
        if ($matchDesktop && $deviceInfo->isDesktop) {
            return true;
        }

        // Check bot
        return $matchBot && $deviceInfo->isBot;
    }
}
