<?php

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
 * Context type that matches based on browser name.
 *
 * Matches when the visitor's browser (detected via User-Agent analysis)
 * is any of the configured browser names. This uses the Matomo DeviceDetector
 * library for accurate browser identification.
 *
 * Configuration (via FlexForm):
 * - field_browsers: Comma-separated list of browser names to match
 *
 * The context matches if the detected browser name matches ANY of the
 * configured browser names (case-insensitive).
 *
 * Common browser names from Matomo DeviceDetector:
 * - Chrome, Firefox, Safari, Microsoft Edge, Opera, Internet Explorer
 * - Mobile Safari, Chrome Mobile, Firefox Mobile, Samsung Browser
 *
 * @author Netresearch DTT GmbH
 * @link https://www.netresearch.de
 */
class BrowserContext extends AbstractContext
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
     * Check if the context matches the current request.
     *
     * Matches if the detected browser is in the configured list of browsers.
     *
     * @param array<int|string, mixed> $arDependencies Array of dependent context objects
     * @return bool True if the visitor's browser matches any configured browser
     */
    public function match(array $arDependencies = []): bool
    {
        // Check session cache first
        [$bUseSession, $bMatch] = $this->getMatchFromSession();
        if ($bUseSession) {
            return $this->invert((bool) $bMatch);
        }

        // Get configured browsers
        $configuredBrowsers = $this->getConfiguredBrowsers();

        // If nothing is configured, context doesn't match
        if ($configuredBrowsers === []) {
            return $this->storeInSession($this->invert(false));
        }

        // Get device info
        $deviceInfo = $this->getDeviceInfo();

        if ($deviceInfo === null) {
            return $this->storeInSession($this->invert(false));
        }

        // Check if browser matches
        $bMatch = $this->matchesBrowser($deviceInfo, $configuredBrowsers);

        return $this->storeInSession($this->invert($bMatch));
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
     * Get the list of configured browser names.
     *
     * @return array<int, string> Array of browser names (lowercase, trimmed)
     */
    protected function getConfiguredBrowsers(): array
    {
        $browsersConfig = $this->getConfValue('field_browsers');

        if ($browsersConfig === '') {
            return [];
        }

        $browsers = explode(',', $browsersConfig);
        $browsers = array_map('trim', $browsers);
        $browsers = array_map('strtolower', $browsers);
        $browsers = array_filter($browsers, static fn(string $browser): bool => $browser !== '');

        return array_values($browsers);
    }

    /**
     * Check if the detected browser matches any configured browser.
     *
     * @param DeviceInfo $deviceInfo The device information
     * @param array<int, string> $configuredBrowsers List of browser names to match (lowercase)
     */
    private function matchesBrowser(DeviceInfo $deviceInfo, array $configuredBrowsers): bool
    {
        // If no browser was detected, no match
        if ($deviceInfo->browserName === null) {
            return false;
        }

        $detectedBrowser = strtolower($deviceInfo->browserName);

        return in_array($detectedBrowser, $configuredBrowsers, true);
    }
}
