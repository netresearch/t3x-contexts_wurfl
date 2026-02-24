<?php

declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title' => 'Contexts: Device Detection',
    'description' => 'Device detection context types (device type, OS, browser) for the contexts extension. Uses Matomo DeviceDetector for user-agent parsing - by Netresearch.',
    'category' => 'misc',
    'author' => 'Netresearch DTT GmbH',
    'author_email' => '',
    'author_company' => 'Netresearch DTT GmbH',
    'license' => 'AGPL-3.0-or-later',
    'state' => 'stable',
    'version' => '2.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.4.99',
            'php' => '8.2.0-8.5.99',
            'contexts' => '4.0.0-4.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
