<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\BuyBox\Collector;

use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Shared\Kernel\StrategyResolverInterface;
use SprykerFeature\Yves\BuyBox\BuyBoxConfig;

class BuyBoxProductCollector implements BuyBoxProductCollectorInterface
{
    /**
     * @param array<\SprykerFeature\Yves\BuyBox\Reader\ProductReaderInterface> $buyBoxProductReaders
     * @param array<\SprykerFeature\Yves\BuyBox\Expander\BuyBoxProductExpanderInterface> $buyBoxProductExpanders
     * @param \Spryker\Shared\Kernel\StrategyResolverInterface<\SprykerFeature\Yves\BuyBox\Sorter\BuyBoxProductSorterInterface> $sortStrategyResolver
     * @param \SprykerFeature\Yves\BuyBox\BuyBoxConfig $config
     */
    public function __construct(
        protected array $buyBoxProductReaders,
        protected array $buyBoxProductExpanders,
        protected StrategyResolverInterface $sortStrategyResolver,
        protected BuyBoxConfig $config,
    ) {
    }

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     * @param string $localeName
     *
     * @return array<\Generated\Shared\Transfer\BuyBoxProductTransfer>
     */
    public function collectBuyBoxProducts(ProductViewTransfer $productViewTransfer, string $localeName): array
    {
        if (!$productViewTransfer->getIdProductConcrete()) {
            return [];
        }

        $buyBoxProducts = [];
        foreach ($this->buyBoxProductReaders as $productReader) {
            $buyBoxProducts = array_merge($buyBoxProducts, $productReader->getBuyBoxProducts($productViewTransfer, $localeName));
        }

        foreach ($this->buyBoxProductExpanders as $buyBoxProductExpander) {
            $buyBoxProducts = $buyBoxProductExpander->expand($buyBoxProducts, $localeName);
        }

        $sorter = $this->sortStrategyResolver->get($this->config->getSortingStrategy());

        return $sorter->sort($buyBoxProducts);
    }
}
