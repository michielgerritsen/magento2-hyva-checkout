<?php
/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Mollie\HyvaCheckout\Block\Checkout\Payment;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template;
use Mollie\Payment\Config;
use Mollie\Payment\Service\Mollie\GetCustomerMandates;
use Mollie\Payment\Service\Mollie\SavedCardConsentText;

class Creditcard extends Template
{
    private const CARD_LABEL_SLUG_MAP = [
        'Visa'             => 'visa',
        'Mastercard'       => 'mastercard',
        'American Express' => 'amex',
        'Maestro'          => 'maestro',
        'Carte Bancaire'   => 'cartebancaire',
        'V PAY'            => 'vpay',
    ];

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        Template\Context $context,
        private readonly Config $config,
        private readonly GetCustomerMandates $getCustomerMandates,
        private readonly SavedCardConsentText $savedCardConsentText,
        private readonly CustomerSession $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function isComponentsEnabled(): bool
    {
        return $this->config->creditcardUseComponents() && $this->config->getProfileId();
    }

    public function isSavedCardsEnabled(): bool
    {
        return $this->config->creditcardEnableCustomersApi() && $this->config->isProductionMode();
    }

    public function isCustomerLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @return list<array{mandate_id: string, card_label: string, card_number_last4: string, card_expiry_date: string|null, card_holder: string|null}>
     */
    public function getSavedMandates(): array
    {
        if (!$this->isSavedCardsEnabled() || !$this->isCustomerLoggedIn()) {
            return [];
        }

        /** @var list<array{mandate_id: string, card_label: string, card_number_last4: string, card_expiry_date: string|null, card_holder: string|null}> $mandates */
        $mandates = $this->getCustomerMandates->execute((int)$this->customerSession->getCustomerId());

        return $mandates;
    }

    public function getCardLogoUrl(string $cardLabel): string
    {
        $slug = self::CARD_LABEL_SLUG_MAP[$cardLabel] ?? null;

        if ($slug === null) {
            return '';
        }

        return $this->getViewFileUrl('Mollie_Payment::images/cards/' . $slug . '.svg');
    }

    public function getConsentText(): string
    {
        return $this->savedCardConsentText->execute();
    }
}
