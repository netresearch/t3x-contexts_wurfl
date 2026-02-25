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

namespace Netresearch\ContextsDevice\Tests\Architecture;

use PHPat\Selector\Selector;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;

/**
 * Architecture tests to enforce layer boundaries.
 *
 * @see https://github.com/carlosas/phpat
 */
final class LayerTest
{
    /**
     * Context type classes should extend the contexts extension AbstractContext.
     */
    public function testContextTypesExtendAbstract(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('Netresearch\ContextsDevice\Context\Type'))
            ->shouldExtend()
            ->classes(
                Selector::classname('Netresearch\Contexts\Context\AbstractContext'),
            )
            ->because('All context types should extend AbstractContext from the contexts extension');
    }

    /**
     * DTO classes should be readonly.
     */
    public function testDtosAreReadonly(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('Netresearch\ContextsDevice\Dto'))
            ->shouldBeReadonly()
            ->because('DTOs should be immutable');
    }

    /**
     * Service classes should be final.
     */
    public function testServicesAreFinal(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('Netresearch\ContextsDevice\Service'))
            ->shouldBeFinal()
            ->because('Service classes should be final for clarity');
    }
}
