<?php
/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Mollie\HyvaCheckout\Test\Fakes\Payment\Api;

use Magento\Payment\Api\Data\PaymentMethodInterface;
use Magento\Payment\Api\PaymentMethodListInterface;

class PaymentMethodListFake implements PaymentMethodListInterface
{
    private array $methods = [];

    public function withMethod(PaymentMethodInterface $method): self
    {
        $this->methods[] = $method;

        return $this;
    }

    public function getList($storeId): array
    {
        return $this->methods;
    }

    public function getActiveList($storeId): array
    {
        return array_values(array_filter(
            $this->methods,
            static fn (PaymentMethodInterface $method): bool => $method->getIsActive()
        ));
    }
}
