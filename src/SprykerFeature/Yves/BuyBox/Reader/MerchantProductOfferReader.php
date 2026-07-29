<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\BuyBox\Reader;

use Generated\Shared\Transfer\BuyBoxProductTransfer;
use Generated\Shared\Transfer\ProductOfferStorageCriteriaTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Client\ProductOfferStorage\ProductOfferStorageClientInterface;

class MerchantProductOfferReader implements ProductReaderInterface
{
    public function __construct(
        protected ProductOfferStorageClientInterface $productOfferStorageClient,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<\Generated\Shared\Transfer\BuyBoxProductTransfer>
     */
    public function getBuyBoxProducts(ProductViewTransfer $productViewTransfer, string $localeName, array $context = []): array
    {
        $sku = $productViewTransfer->getSkuOrFail();
        $productOfferStorageCriteriaTransfer = (new ProductOfferStorageCriteriaTransfer())
            ->fromArray($context, true)
            ->addProductConcreteSku($sku);

        $productOfferStorageCollectionTransfer = $this->productOfferStorageClient->getProductOfferStoragesBySkus($productOfferStorageCriteriaTransfer);

        $buyBoxProductTransfers = [];

        foreach ($productOfferStorageCollectionTransfer->getProductOffers() as $productOfferStorageTransfer) {
            $buyBoxProductTransfers[] = (new BuyBoxProductTransfer())
                ->fromArray($productOfferStorageTransfer->toArray(), true)
                ->setBaseUnit($productViewTransfer->getBaseUnit())
                ->setMerchant($productOfferStorageTransfer->getMerchantStorage());
        }

        return $buyBoxProductTransfers;
    }
}
