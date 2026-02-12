<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\BuyBox;

use Spryker\Client\MerchantStorage\MerchantStorageClientInterface;
use Spryker\Client\PriceProduct\PriceProductClientInterface;
use Spryker\Client\PriceProductStorage\PriceProductStorageClientInterface;
use Spryker\Client\ProductOfferStorage\ProductOfferStorageClientInterface;
use Spryker\Client\ProductStorage\ProductStorageClientInterface;
use Spryker\Shared\Kernel\StrategyResolver;
use Spryker\Shared\Kernel\StrategyResolverInterface;
use Spryker\Yves\Kernel\AbstractFactory;
use SprykerFeature\Yves\BuyBox\Collector\BuyBoxProductCollector;
use SprykerFeature\Yves\BuyBox\Collector\BuyBoxProductCollectorInterface;
use SprykerFeature\Yves\BuyBox\Expander\BuyBoxProductExpanderInterface;
use SprykerFeature\Yves\BuyBox\Expander\InventoryStatusExpander;
use SprykerFeature\Yves\BuyBox\Expander\MerchantUrlExpander;
use SprykerFeature\Yves\BuyBox\Reader\MerchantProductOfferReader;
use SprykerFeature\Yves\BuyBox\Reader\MerchantProductReader;
use SprykerFeature\Yves\BuyBox\Reader\ProductReaderInterface;
use SprykerFeature\Yves\BuyBox\Sorter\BuyBoxProductSorterInterface;
use SprykerFeature\Yves\BuyBox\Sorter\PriceSorter;
use SprykerFeature\Yves\BuyBox\Sorter\StockSorter;

/**
 * @method \SprykerFeature\Yves\BuyBox\BuyBoxConfig getConfig()
 */
class BuyBoxFactory extends AbstractFactory
{
    public function createBuyBoxProductCollector(): BuyBoxProductCollectorInterface
    {
        return new BuyBoxProductCollector(
            $this->getProductReaders(),
            $this->getBuyBoxProductExpanders(),
            $this->createBuyBoxProductSorterResolver(),
            $this->getConfig(),
        );
    }

    /**
     * @return array<\SprykerFeature\Yves\BuyBox\Reader\ProductReaderInterface>
     */
    public function getProductReaders(): array
    {
        return [
            $this->createMerchantProductReader(),
            $this->createMerchantProductOfferReader(),
        ];
    }

    public function createMerchantProductReader(): ProductReaderInterface
    {
        return new MerchantProductReader(
            $this->getMerchantStorageClient(),
            $this->getProductStorageClient(),
            $this->getPriceProductClient(),
            $this->getPriceProductStorageClient(),
        );
    }

    public function createMerchantProductOfferReader(): ProductReaderInterface
    {
        return new MerchantProductOfferReader(
            $this->getProductOfferStorageClient(),
        );
    }

    public function getProductOfferStorageClient(): ProductOfferStorageClientInterface
    {
        return $this->getProvidedDependency(BuyBoxDependencyProvider::CLIENT_PRODUCT_OFFER_STORAGE);
    }

    public function getMerchantStorageClient(): MerchantStorageClientInterface
    {
        return $this->getProvidedDependency(BuyBoxDependencyProvider::CLIENT_MERCHANT_STORAGE);
    }

    public function getProductStorageClient(): ProductStorageClientInterface
    {
        return $this->getProvidedDependency(BuyBoxDependencyProvider::CLIENT_PRODUCT_STORAGE);
    }

    public function getPriceProductClient(): PriceProductClientInterface
    {
        return $this->getProvidedDependency(BuyBoxDependencyProvider::CLIENT_PRICE_PRODUCT);
    }

    public function getPriceProductStorageClient(): PriceProductStorageClientInterface
    {
        return $this->getProvidedDependency(BuyBoxDependencyProvider::CLIENT_PRICE_PRODUCT_STORAGE);
    }

    /**
     * @return array<\SprykerFeature\Yves\BuyBox\Expander\BuyBoxProductExpanderInterface>
     */
    public function getBuyBoxProductExpanders(): array
    {
        return [
            $this->createInventoryStatusExpander(),
            $this->createMerchantUrlExpander(),
        ];
    }

    public function createInventoryStatusExpander(): BuyBoxProductExpanderInterface
    {
        return new InventoryStatusExpander();
    }

    public function createMerchantUrlExpander(): BuyBoxProductExpanderInterface
    {
        return new MerchantUrlExpander();
    }

    /**
     * @return \Spryker\Shared\Kernel\StrategyResolverInterface<\SprykerFeature\Yves\BuyBox\Sorter\BuyBoxProductSorterInterface>
     */
    protected function createBuyBoxProductSorterResolver(): StrategyResolverInterface
    {
        return new StrategyResolver(
            [
                BuyBoxConfig::SORT_BY_PRICE => $this->createPriceSorter(),
                BuyBoxConfig::SORT_BY_STOCK => $this->createStockSorter(),
            ],
            BuyBoxConfig::SORT_BY_PRICE,
        );
    }

    protected function createPriceSorter(): BuyBoxProductSorterInterface
    {
        return new PriceSorter();
    }

    protected function createStockSorter(): BuyBoxProductSorterInterface
    {
        return new StockSorter();
    }
}
