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

namespace Netresearch\ContextsDevice\Context;

use Netresearch\ContextsDevice\Dto\DeviceInfo;
use Netresearch\ContextsDevice\Service\DeviceDetectionService;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Resolves device information for the current request.
 *
 * Shared by every context type in this extension that decides on detected
 * device properties. The service is injected via the constructor in normal
 * operation; the lazy fallback covers the code paths where TYPO3 instantiates
 * a context from a database row without going through the container.
 *
 * @author Netresearch DTT GmbH
 * @link https://www.netresearch.de
 */
trait DeviceDetectionAwareTrait
{
    protected ?DeviceDetectionService $deviceDetectionService = null;

    /**
     * Get the device detection service, with lazy initialization fallback.
     */
    protected function getDeviceDetectionService(): DeviceDetectionService
    {
        if (!$this->deviceDetectionService instanceof DeviceDetectionService) {
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

        if (!$request instanceof ServerRequestInterface) {
            return null;
        }

        return $this->getDeviceDetectionService()->detectFromRequest($request);
    }
}
