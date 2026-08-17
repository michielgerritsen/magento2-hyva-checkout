<?php
/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Mollie\HyvaCheckout\Block\Checkout\Payment;

use Magento\Framework\View\Element\Template;
use Mollie\Payment\Config;

class Issuer extends Template
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        Template\Context $context,
        private readonly Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getTemplate(): string
    {
        $method = $this->getData('mollie_method');
        $listType = $this->config->getIssuerListType(is_string($method) ? $method : '');

        if ($listType == 'none') {
            return 'Mollie_HyvaCheckout::component/payment/method/issuer/none.phtml';
        }

        if ($listType == 'dropdown') {
            return 'Mollie_HyvaCheckout::component/payment/method/issuer/dropdown.phtml';
        }

        return 'Mollie_HyvaCheckout::component/payment/method/issuer/list.phtml';
    }
}
