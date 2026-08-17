<?php
/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Mollie\HyvaCheckout\Magewire\Checkout\Payment\Method;

use Magento\Checkout\Model\Session as SessionCheckout;
use Magento\Quote\Api\CartRepositoryInterface;
use Magewirephp\Magewire\Component\Form;
use Mollie\Payment\Service\Mollie\GetIssuers;
use Rakit\Validation\Validator;

/**
 * @method static getMagewire()
 */
class WithIssuer extends Form
{
    /**
     * @var array<string, string>
     */
    protected $rules = [
        'mollie_issuer' => 'required',
    ];

    /**
     * @var list<array{id: string, name: string, image: array{size1x: string, size2x: string, svg: string}}>
     */
    public array $issuers = [];

    public string $selectedIssuer = '';

    private SessionCheckout $sessionCheckout;

    private CartRepositoryInterface $quoteRepository;

    private GetIssuers $getIssuers;

    private string $method;

    public function __construct(
        Validator $validator,
        SessionCheckout $sessionCheckout,
        CartRepositoryInterface $quoteRepository,
        GetIssuers $getIssuers,
        string $method
    ) {
        parent::__construct($validator);
        $this->sessionCheckout = $sessionCheckout;
        $this->getIssuers = $getIssuers;
        $this->quoteRepository = $quoteRepository;
        $this->method = $method;
    }

    public function mount(): void
    {
        $quote = $this->sessionCheckout->getQuote();

        $this->issuers = $this->getIssuers->execute($this->method, 'list') ?? [];

        $selectedIssuer = $quote->getPayment()->getAdditionalInformation('selected_issuer');

        if (is_string($selectedIssuer)) {
            $this->selectedIssuer = $selectedIssuer;
        }
    }

    public function updatedSelectedIssuer(string $value): ?string
    {
        $quote = $this->sessionCheckout->getQuote();
        $quote->getPayment()->setAdditionalInformation('selected_issuer', $value);

        $this->quoteRepository->save($quote);

        return $value;
    }
}
