<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\BuyBox\Expander;

use SprykerFeature\Yves\BuyBox\BuyBoxConfig;

class InventoryStatusExpander implements BuyBoxProductExpanderInterface
{
    /**
     * @param array<\Generated\Shared\Transfer\BuyBoxProductTransfer> $buyBoxProductTransfers
     * @param string $localeName
     *
     * @return array<\Generated\Shared\Transfer\BuyBoxProductTransfer>
     */
    public function expand(array $buyBoxProductTransfers, string $localeName): array
    {
        foreach ($buyBoxProductTransfers as $buyBoxProductTransfer) {
            if ($buyBoxProductTransfer->getIsNeverOutOfStock() || $buyBoxProductTransfer->getStockQuantity() > 0) {
                $buyBoxProductTransfer
                    ->setIsAvailable(true)
                    ->setInventoryStatus(BuyBoxConfig::INVENTORY_STATUS_IN_STOCK);

                continue;
            }

            $buyBoxProductTransfer
                ->setIsAvailable(false)
                ->setInventoryStatus(BuyBoxConfig::INVENTORY_STATUS_OUT_OF_STOCK);
        }

        return $buyBoxProductTransfers;
    }
}
