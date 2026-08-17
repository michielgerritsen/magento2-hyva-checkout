<?php declare(strict_types=1);

namespace Mollie\HyvaCheckout\Plugin;

use Hyva\Checkout\Model\MethodMetaData;
use Magento\Payment\Model\MethodInterface;
use Mollie\Payment\Helper\General as MollieHelper;

class RenderPaymentMethodIcons
{
    private MollieHelper $mollieHelper;

    public function __construct(MollieHelper $mollieHelper)
    {
        $this->mollieHelper = $mollieHelper;
    }

    /**
     * Mollie payments do not have meta-data icons, so we need to make sure the icons are rendered without the
     * meta-data being set. The actual data will be populated by the PopulateIconData plugin.
     * @see \Mollie\HyvaCheckout\Plugin\PopulateIconData
     */
    public function afterCanRenderIcon(MethodMetaData $subject, bool $result): bool
    {
        if (!$this->mollieHelper->useImage()) {
            return $result;
        }

        /** @var MethodInterface $method */
        $method = $subject->getMethod();

        if (strpos($method->getCode(), 'mollie_methods_') === 0) {
            return true;
        }

        return $result;
    }
}
