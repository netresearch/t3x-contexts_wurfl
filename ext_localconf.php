<?php
/**
 * Contexts WURFL extension configuration.
 *
 * PHP version 5
 *
 * @category   Contexts
 * @package    WURFL
 * @subpackage Configuration
 * @author     Rico Sonntag <rico.sonntag@netresearch.de>
 */

defined('TYPO3_MODE') or die('Access denied.');

if (TYPO3_MODE !== 'BE') {
	// We load that file in ext_tables.php for the backend
	require_once t3lib_extMgm::extPath($_EXTKEY) . 'ext_contexts.php';
}

?>