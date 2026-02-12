<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\BuyBox\Sorter;

use Generated\Shared\Transfer\BuyBoxProductTransfer;

class PriceSorter implements BuyBoxProductSorterInterface
{
    /**
     * @param array<\Generated\Shared\Transfer\BuyBoxProductTransfer> $buyBoxProducts
     *
     * @return array<\Generated\Shared\Transfer\BuyBoxProductTransfer>
     */
    public function sort(array $buyBoxProducts): array
    {
        usort($buyBoxProducts, function (BuyBoxProductTransfer $productA, BuyBoxProductTransfer $productB) {
            $priceA = $productA->getPrice()?->getPrice();
            $priceB = $productB->getPrice()?->getPrice();

            if ($priceA === null && $priceB === null) {
                return 0;
            }

            if ($priceA === null) {
                return 1;
            }

            if ($priceB === null) {
                return -1;
            }

            return $priceA <=> $priceB;
        });

        return $buyBoxProducts;
    }
}
