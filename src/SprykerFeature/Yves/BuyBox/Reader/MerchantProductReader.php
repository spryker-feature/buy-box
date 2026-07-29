<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\BuyBox\Reader;

use Generated\Shared\Transfer\BuyBoxProductTransfer;
use Generated\Shared\Transfer\CurrentProductPriceTransfer;
use Generated\Shared\Transfer\MerchantStorageCriteriaTransfer;
use Generated\Shared\Transfer\PriceProductFilterTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Client\MerchantStorage\MerchantStorageClientInterface;
use Spryker\Client\PriceProduct\PriceProductClientInterface;
use Spryker\Client\PriceProductStorage\PriceProductStorageClientInterface;
use Spryker\Client\ProductStorage\ProductStorageClientInterface;

class MerchantProductReader implements ProductReaderInterface
{
    protected const string CONTEXT_KEY_IS_MERCHANT_PRODUCT_LOADING_SKIPPED = 'is_merchant_product_loading_skipped';

    public function __construct(
        protected MerchantStorageClientInterface $merchantStorageClient,
        protected ProductStorageClientInterface $productStorageClient,
        protected PriceProductClientInterface $priceProductClient,
        protected PriceProductStorageClientInterface $priceProductStorageClient,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<\Generated\Shared\Transfer\BuyBoxProductTransfer>
     */
    public function getBuyBoxProducts(ProductViewTransfer $productViewTransfer, string $localeName, array $context = []): array
    {
        $isMerchantProductLoadingSkipped = $context[static::CONTEXT_KEY_IS_MERCHANT_PRODUCT_LOADING_SKIPPED] ?? false;

        if (!$productViewTransfer->getIdProductConcrete() || $isMerchantProductLoadingSkipped) {
            return [];
        }

        $idProductAbstract = $productViewTransfer->getIdProductAbstractOrFail();
        $productAbstractStorageData = $this->productStorageClient->findProductAbstractStorageData(
            $idProductAbstract,
            $localeName,
        );

        if (!$productAbstractStorageData) {
            return [];
        }

        $currentProductPriceTransfer = $this->getCurrentProductPriceTransfer($productViewTransfer);
        $buyBoxProductTransfer = (new BuyBoxProductTransfer())->fromArray($productAbstractStorageData, true);
        $merchantReference = $buyBoxProductTransfer->getMerchantReference();

        if (!$merchantReference) {
            return [];
        }

        $merchantStorageTransfer = $this->merchantStorageClient->findOne(
            (new MerchantStorageCriteriaTransfer())->addMerchantReference($merchantReference),
        );

        if (!$merchantStorageTransfer) {
            return [];
        }

        $buyBoxProductTransfer->setMerchant($merchantStorageTransfer);
        $buyBoxProductTransfer->setPrice($currentProductPriceTransfer);
        $buyBoxProductTransfer->setIdMerchant($merchantStorageTransfer->getIdMerchant());
        $buyBoxProductTransfer->setIsNeverOutOfStock($productViewTransfer->getIsNeverOutOfStock());
        $buyBoxProductTransfer->setStockQuantity($productViewTransfer->getStockQuantity());
        $buyBoxProductTransfer->setBaseUnit($productViewTransfer->getBaseUnit());

        return [$buyBoxProductTransfer];
    }

    protected function getCurrentProductPriceTransfer(ProductViewTransfer $productViewTransfer): CurrentProductPriceTransfer
    {
        $priceProductTransfers = $this->priceProductStorageClient->getResolvedPriceProductConcreteTransfers(
            $productViewTransfer->getIdProductConcreteOrFail(),
            $productViewTransfer->getIdProductAbstractOrFail(),
        );
        $priceProductFilterTransfer = (new PriceProductFilterTransfer())
            ->setQuantity($productViewTransfer->getQuantity());

        return $this->priceProductClient->resolveProductPriceTransferByPriceProductFilter(
            $priceProductTransfers,
            $priceProductFilterTransfer,
        );
    }
}
