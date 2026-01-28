<?php

/**
 * This file is part of the package netresearch/contexts-wurfl.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Netresearch\ContextsDevice\Service;

use DeviceDetector\DeviceDetector;
use Netresearch\ContextsDevice\Dto\DeviceInfo;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Service for device detection using Matomo DeviceDetector.
 *
 * This service wraps the Matomo DeviceDetector library and provides
 * a simplified API for device detection in TYPO3. It handles:
 * - User-Agent extraction from HTTP requests
 * - Result caching for performance
 * - Conversion to DeviceInfo DTO
 *
 * @see https://github.com/matomo-org/device-detector
 */
final class DeviceDetectionService
{
    /**
     * Cache for parsed user agents.
     *
     * @var array<string, DeviceInfo>
     */
    private array $cache = [];

    public function __construct(
        private readonly DeviceDetector $deviceDetector,
    ) {
    }

    /**
     * Detect device information from the current TYPO3 request.
     *
     * Uses the global TYPO3_REQUEST if available.
     */
    public function detectForCurrentRequest(): ?DeviceInfo
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (!$request instanceof ServerRequestInterface) {
            return null;
        }

        return $this->detectFromRequest($request);
    }

    /**
     * Detect device information from a PSR-7 server request.
     */
    public function detectFromRequest(ServerRequestInterface $request): ?DeviceInfo
    {
        $userAgent = $request->getHeaderLine('User-Agent');

        return $this->detectFromUserAgent($userAgent);
    }

    /**
     * Detect device information from a User-Agent string.
     *
     * Results are cached for performance optimization.
     *
     * @param string $userAgent The User-Agent string to analyze
     * @return DeviceInfo|null Device information or null if user agent is empty
     */
    public function detectFromUserAgent(string $userAgent): ?DeviceInfo
    {
        if ($userAgent === '') {
            return null;
        }

        // Return cached result if available
        if (isset($this->cache[$userAgent])) {
            return $this->cache[$userAgent];
        }

        // Parse the user agent
        $this->deviceDetector->setUserAgent($userAgent);
        $this->deviceDetector->parse();

        // Extract device information
        $deviceInfo = $this->createDeviceInfo();

        // Cache the result
        $this->cache[$userAgent] = $deviceInfo;

        return $deviceInfo;
    }

    /**
     * Clear the internal cache.
     *
     * Useful for testing or when memory optimization is needed.
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }

    /**
     * Create a DeviceInfo DTO from the current DeviceDetector state.
     */
    private function createDeviceInfo(): DeviceInfo
    {
        $client = $this->deviceDetector->getClient();
        $os = $this->deviceDetector->getOs();

        // Normalize client and os to arrays (DeviceDetector returns string on failure)
        $clientArray = is_array($client) ? $client : null;
        $osArray = is_array($os) ? $os : null;

        return new DeviceInfo(
            isMobile: $this->deviceDetector->isMobile(),
            isTablet: $this->deviceDetector->isTablet(),
            isDesktop: $this->deviceDetector->isDesktop(),
            isBot: $this->deviceDetector->isBot(),
            browserName: $this->extractString($clientArray, 'name'),
            browserVersion: $this->extractString($clientArray, 'version'),
            osName: $this->extractString($osArray, 'name'),
            osVersion: $this->extractString($osArray, 'version'),
            deviceBrand: $this->normalizeString($this->deviceDetector->getBrandName()),
            deviceModel: $this->normalizeString($this->deviceDetector->getModel()),
        );
    }

    /**
     * Extract a string value from an array, returning null if not found or empty.
     *
     * @param array<array-key, mixed>|null $data The data array
     * @param string $key The key to extract
     */
    private function extractString(?array $data, string $key): ?string
    {
        if ($data === null || !isset($data[$key])) {
            return null;
        }

        $value = $data[$key];

        if (!is_string($value) || $value === '') {
            return null;
        }

        return $value;
    }

    /**
     * Normalize a string value, returning null for empty strings.
     */
    private function normalizeString(string $value): ?string
    {
        return $value === '' ? null : $value;
    }
}
