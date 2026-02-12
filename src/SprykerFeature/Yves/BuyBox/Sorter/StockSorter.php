<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\BuyBox\Sorter;

use Generated\Shared\Transfer\BuyBoxProductTransfer;

class StockSorter implements BuyBoxProductSorterInterface
{
    /**
     * @param array<\Generated\Shared\Transfer\BuyBoxProductTransfer> $buyBoxProducts
     *
     * @return array<\Generated\Shared\Transfer\BuyBoxProductTransfer>
     */
    public function sort(array $buyBoxProducts): array
    {
        usort($buyBoxProducts, function (BuyBoxProductTransfer $productA, BuyBoxProductTransfer $productB) {
            $isNeverOutOfStockA = $productA->getIsNeverOutOfStock();
            $isNeverOutOfStockB = $productB->getIsNeverOutOfStock();

            if ($isNeverOutOfStockA !== $isNeverOutOfStockB) {
                return $isNeverOutOfStockA ? -1 : 1;
            }

            if ($isNeverOutOfStockA) {
                return 0;
            }

            $stockQuantityA = (float)$productA->getStockQuantity();
            $stockQuantityB = (float)$productB->getStockQuantity();

            return $stockQuantityB <=> $stockQuantityA;
        });

        return $buyBoxProducts;
    }
}
