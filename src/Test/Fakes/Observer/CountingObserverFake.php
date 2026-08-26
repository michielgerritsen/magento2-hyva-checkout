<?php
/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Mollie\HyvaCheckout\Test\Fakes\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use RuntimeException;

class CountingObserverFake implements ObserverInterface
{
    private ObserverInterface $delegate;
    private int $maximumDepth;
    private int $depth = 0;
    private int $deepestDepth = 0;

    public function __construct(ObserverInterface $delegate, int $maximumDepth = 5)
    {
        $this->delegate = $delegate;
        $this->maximumDepth = $maximumDepth;
    }

    public function execute(Observer $observer): void
    {
        $this->depth++;
        $this->deepestDepth = max($this->deepestDepth, $this->depth);

        try {
            if ($this->depth > $this->maximumDepth) {
                throw new RuntimeException(
                    sprintf('%s called itself more than %d times', get_class($this->delegate), $this->maximumDepth)
                );
            }

            $this->delegate->execute($observer);
        } finally {
            $this->depth--;
        }
    }

    public function getDeepestDepth(): int
    {
        return $this->deepestDepth;
    }
}
