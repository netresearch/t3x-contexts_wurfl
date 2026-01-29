<?php

/**
 * This file is part of the package netresearch/contexts-wurfl.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

use Netresearch\Contexts\Api\Configuration;
use Netresearch\ContextsDevice\Context\Type\BrowserContext;
use Netresearch\ContextsDevice\Context\Type\DeviceContext;

defined('TYPO3') || die('Access denied.');

/**
 * Register device detection context types with the base contexts extension.
 *
 * These context types allow content targeting based on visitor's device and browser:
 * - Device: Match visitors based on their device type (mobile, tablet, desktop, bot)
 * - Browser: Match visitors based on their browser type (Chrome, Firefox, Safari, etc.)
 */

Configuration::registerContextType(
    'device',
    'LLL:EXT:contexts_wurfl/Resources/Private/Language/locallang.xlf:context.device.title',
    DeviceContext::class,
    'FILE:EXT:contexts_wurfl/Configuration/FlexForms/Device.xml',
);

Configuration::registerContextType(
    'browser',
    'LLL:EXT:contexts_wurfl/Resources/Private/Language/locallang.xlf:context.browser.title',
    BrowserContext::class,
    'FILE:EXT:contexts_wurfl/Configuration/FlexForms/Browser.xml',
);
