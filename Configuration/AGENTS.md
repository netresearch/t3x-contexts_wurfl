<!-- Managed by agent: keep sections & order; edit content, not structure. Last updated: 2026-01-28 -->

# AGENTS.md — Configuration/

TYPO3 configuration files for the Contexts Device Detection extension.

## Overview

```
Configuration/
├── TCA/                  # Table Configuration Array
│   └── Overrides/        # TCA overrides for context registration
├── FlexForms/            # Dynamic form configurations
│   └── ContextType/      # Device context configuration forms
├── Services.yaml         # Symfony DI configuration
└── Icons.php             # Icon registry
```

## Code Style & Conventions

### Registering Device Context Types

```php
// TCA/Overrides/tx_contexts_contexts.php
$GLOBALS['TCA']['tx_contexts_contexts']['columns']['type']['config']['items'][] = [
    'label' => 'LLL:EXT:contexts_wurfl/Resources/Private/Language/locallang_db.xlf:...',
    'value' => \Netresearch\ContextsDevice\Context\Type\DeviceContext::class,
];
```

### FlexForm for Device Selection

```xml
<field_device_type>
    <label>Device Type</label>
    <config>
        <type>select</type>
        <renderType>selectCheckBox</renderType>
        <items>
            <item><label>Mobile</label><value>mobile</value></item>
            <item><label>Tablet</label><value>tablet</value></item>
            <item><label>Desktop</label><value>desktop</value></item>
        </items>
    </config>
</field_device_type>
```

## PR/Commit Checklist

- [ ] Device context types registered in TCA/Overrides
- [ ] FlexForms have language file references
- [ ] Services.yaml changes tested

## House Rules

- Device types: mobile, tablet, desktop, bot, tv, console
- Support OS detection: iOS, Android, Windows, macOS, Linux
- Support browser detection: Chrome, Safari, Firefox, Edge
