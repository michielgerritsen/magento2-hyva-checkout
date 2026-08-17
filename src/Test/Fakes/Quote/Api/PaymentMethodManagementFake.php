<?php
/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Mollie\HyvaCheckout\Test\Fakes\Quote\Api;

use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;

class PaymentMethodManagementFake implements PaymentMethodManagementInterface
{
    private array $methodsThatWereSet = [];

    public function set($cartId, PaymentInterface $method): int
    {
        $this->methodsThatWereSet[] = $method->getMethod();

        return count($this->methodsThatWereSet);
    }

    public function get($cartId): ?PaymentInterface
    {
        return null;
    }

    public function getList($cartId): array
    {
        return [];
    }

    public function getNumberOfTimesSetWasCalled(): int
    {
        return count($this->methodsThatWereSet);
    }

    public function getLastMethodThatWasSet(): ?string
    {
        if ($this->methodsThatWereSet === []) {
            return null;
        }

        return end($this->methodsThatWereSet);
    }
}
