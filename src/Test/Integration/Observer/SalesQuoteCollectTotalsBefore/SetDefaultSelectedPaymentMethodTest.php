<?php
/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Mollie\HyvaCheckout\Test\Integration\Observer\SalesQuoteCollectTotalsBefore;

use Magento\Framework\Event\Observer;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Model\ResourceModel\Quote\Payment as PaymentResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Mollie\HyvaCheckout\Observer\SalesQuoteCollectTotalsBefore\SetDefaultSelectedPaymentMethod;
use Mollie\HyvaCheckout\Test\Fakes\Observer\CountingObserverFake;
use Mollie\HyvaCheckout\Test\Fakes\Payment\Api\PaymentMethodListFake;
use Mollie\HyvaCheckout\Test\Fakes\Quote\Api\PaymentMethodManagementFake;
use PHPUnit\Framework\TestCase;

class SetDefaultSelectedPaymentMethodTest extends TestCase
{
    private ObjectManager $objectManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->objectManager->removeSharedInstance(SetDefaultSelectedPaymentMethod::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoConfigFixture default_store payment/mollie_general/enabled 1
     * @magentoConfigFixture default_store payment/mollie_general/type test
     * @magentoConfigFixture default_store payment/mollie_general/apikey_test test_dummyapikeywhichmustbe30characterslong
     * @magentoConfigFixture default_store payment/mollie_general/default_selected_method mollie_methods_ideal
     * @magentoConfigFixture default_store payment/mollie_methods_ideal/active 1
     * @magentoConfigFixture default_store hyva_themes_checkout/general/checkout default
     */
    public function testSetsTheDefaultMethodOnAQuoteThatHasToRecollectItsTotals(): void
    {
        $quote = $this->loadQuote();
        $this->removePaymentMethod($quote);
        $this->useObserverThatFailsWhenItCallsItself();

        $this->markQuoteToRecollectTotals($quote);

        $reloadedQuote = $this->objectManager->get(CartRepositoryInterface::class)->get((int)$quote->getId());

        $this->assertEquals(0, $reloadedQuote->getTriggerRecollect());
        $this->assertEquals('mollie_methods_ideal', $reloadedQuote->getPayment()->getMethod());
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoConfigFixture default_store payment/mollie_general/enabled 1
     * @magentoConfigFixture default_store payment/mollie_general/type test
     * @magentoConfigFixture default_store payment/mollie_general/apikey_test test_dummyapikeywhichmustbe30characterslong
     * @magentoConfigFixture default_store payment/mollie_general/default_selected_method mollie_methods_ideal
     * @magentoConfigFixture default_store payment/mollie_methods_ideal/active 1
     * @magentoConfigFixture default_store hyva_themes_checkout/general/checkout default
     */
    public function testSetsTheDefaultMethodOnTheQuote(): void
    {
        $quote = $this->loadQuote();
        $quote->getPayment()->setMethod(null);

        $paymentMethodManagement = $this->objectManager->create(PaymentMethodManagementFake::class);

        $this->createObserver($paymentMethodManagement)->execute($this->createEvent($quote));

        $this->assertEquals(1, $paymentMethodManagement->getNumberOfTimesSetWasCalled());
        $this->assertEquals('mollie_methods_ideal', $paymentMethodManagement->getLastMethodThatWasSet());
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoConfigFixture default_store payment/mollie_general/enabled 1
     * @magentoConfigFixture default_store payment/mollie_general/type test
     * @magentoConfigFixture default_store payment/mollie_general/apikey_test test_dummyapikeywhichmustbe30characterslong
     * @magentoConfigFixture default_store payment/mollie_general/default_selected_method first_mollie_method
     * @magentoConfigFixture default_store payment/mollie_methods_ideal/active 1
     * @magentoConfigFixture default_store hyva_themes_checkout/general/checkout default
     */
    public function testSetsTheFirstAvailableMollieMethodOnTheQuote(): void
    {
        $quote = $this->loadQuote();
        $quote->getPayment()->setMethod(null);

        $paymentMethodManagement = $this->objectManager->create(PaymentMethodManagementFake::class);

        $this->createObserver($paymentMethodManagement)->execute($this->createEvent($quote));

        $this->assertEquals(1, $paymentMethodManagement->getNumberOfTimesSetWasCalled());
        $this->assertStringStartsWith('mollie_methods_', $paymentMethodManagement->getLastMethodThatWasSet());
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoConfigFixture default_store payment/mollie_general/enabled 1
     * @magentoConfigFixture default_store payment/mollie_general/type test
     * @magentoConfigFixture default_store payment/mollie_general/apikey_test test_dummyapikeywhichmustbe30characterslong
     * @magentoConfigFixture default_store payment/mollie_general/default_selected_method first_mollie_method
     * @magentoConfigFixture default_store hyva_themes_checkout/general/checkout default
     */
    public function testLeavesTheQuoteAloneWhenNoMollieMethodIsAvailable(): void
    {
        $quote = $this->loadQuote();
        $quote->getPayment()->setMethod(null);

        $paymentMethodManagement = $this->objectManager->create(PaymentMethodManagementFake::class);

        $this->createObserver($paymentMethodManagement, new PaymentMethodListFake())
            ->execute($this->createEvent($quote));

        $this->assertEquals(0, $paymentMethodManagement->getNumberOfTimesSetWasCalled());
        $this->assertNull($quote->getPayment()->getMethod());
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoConfigFixture default_store payment/mollie_general/enabled 1
     * @magentoConfigFixture default_store payment/mollie_general/type test
     * @magentoConfigFixture default_store payment/mollie_general/apikey_test test_dummyapikeywhichmustbe30characterslong
     * @magentoConfigFixture default_store payment/mollie_general/default_selected_method mollie_methods_ideal
     * @magentoConfigFixture default_store payment/mollie_methods_ideal/active 1
     * @magentoConfigFixture default_store hyva_themes_checkout/general/checkout default
     */
    public function testDoesNotPersistTheMethodWhenTheQuoteHasNoShippingCountry(): void
    {
        $quote = $this->loadQuote();
        $quote->getPayment()->setMethod(null);
        $quote->getShippingAddress()->setCountryId(null);

        $paymentMethodManagement = $this->objectManager->create(PaymentMethodManagementFake::class);

        $this->createObserver($paymentMethodManagement)->execute($this->createEvent($quote));

        $this->assertEquals(0, $paymentMethodManagement->getNumberOfTimesSetWasCalled());
        $this->assertEquals('mollie_methods_ideal', $quote->getPayment()->getMethod());
    }

    private function createObserver(
        PaymentMethodManagementFake $paymentMethodManagement,
        ?PaymentMethodListFake $paymentMethodList = null
    ): SetDefaultSelectedPaymentMethod {
        $arguments = ['paymentMethodManagement' => $paymentMethodManagement];

        if ($paymentMethodList !== null) {
            $arguments['paymentMethodList'] = $paymentMethodList;
        }

        return $this->objectManager->create(SetDefaultSelectedPaymentMethod::class, $arguments);
    }

    private function createEvent(Quote $quote): Observer
    {
        $event = $this->objectManager->create(Observer::class);
        $event->setData('quote', $quote);

        return $event;
    }

    private function useObserverThatFailsWhenItCallsItself(): CountingObserverFake
    {
        $fake = $this->objectManager->create(CountingObserverFake::class, [
            'delegate' => $this->objectManager->create(SetDefaultSelectedPaymentMethod::class),
        ]);

        $this->objectManager->addSharedInstance($fake, SetDefaultSelectedPaymentMethod::class);

        return $fake;
    }

    private function loadQuote(): Quote
    {
        $quote = $this->objectManager->create(Quote::class);
        $this->objectManager->get(QuoteResource::class)->load($quote, 'test_order_1', 'reserved_order_id');

        return $quote;
    }

    private function removePaymentMethod(Quote $quote): void
    {
        $payment = $quote->getPayment();
        $payment->setMethod(null);

        $this->objectManager->get(PaymentResource::class)->save($payment);
    }

    private function markQuoteToRecollectTotals(Quote $quote): void
    {
        $productIds = array_map(
            function ($item): int {
                return (int)$item->getProductId();
            },
            $quote->getAllItems()
        );

        $this->objectManager->get(QuoteResource::class)->markQuotesRecollect($productIds);
    }
}
