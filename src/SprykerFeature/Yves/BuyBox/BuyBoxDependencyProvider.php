<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\BuyBox;

use Spryker\Yves\Kernel\AbstractBundleDependencyProvider;
use Spryker\Yves\Kernel\Container;

class BuyBoxDependencyProvider extends AbstractBundleDependencyProvider
{
    public const string CLIENT_MERCHANT_STORAGE = 'CLIENT_MERCHANT_STORAGE';

    public const string CLIENT_PRODUCT_OFFER_STORAGE = 'CLIENT_PRODUCT_OFFER_STORAGE';

    public const string CLIENT_PRODUCT_STORAGE = 'CLIENT_PRODUCT_STORAGE';

    public const string CLIENT_PRICE_PRODUCT_STORAGE = 'CLIENT_PRICE_PRODUCT_STORAGE';

    public const string CLIENT_PRICE_PRODUCT = 'CLIENT_PRICE_PRODUCT';

    public function provideDependencies(Container $container): Container
    {
        $container = parent::provideDependencies($container);

        $container = $this->addMerchantProductOfferStorageClient($container);
        $container = $this->addMerchantStorageClient($container);
        $container = $this->addProductStorageClient($container);
        $container = $this->addPriceProductClient($container);
        $container = $this->addPriceProductStorageClient($container);

        return $container;
    }

    protected function addMerchantProductOfferStorageClient(Container $container): Container
    {
        $container->set(static::CLIENT_PRODUCT_OFFER_STORAGE, function (Container $container) {
            return $container->getLocator()->productOfferStorage()->client();
        });

        return $container;
    }

    protected function addMerchantStorageClient(Container $container): Container
    {
        $container->set(static::CLIENT_MERCHANT_STORAGE, function (Container $container) {
            return $container->getLocator()->merchantStorage()->client();
        });

        return $container;
    }

    protected function addProductStorageClient(Container $container): Container
    {
        $container->set(static::CLIENT_PRODUCT_STORAGE, function (Container $container) {
            return $container->getLocator()->productStorage()->client();
        });

        return $container;
    }

    protected function addPriceProductClient(Container $container): Container
    {
        $container->set(static::CLIENT_PRICE_PRODUCT, function (Container $container) {
            return $container->getLocator()->priceProduct()->client();
        });

        return $container;
    }

    protected function addPriceProductStorageClient(Container $container): Container
    {
        $container->set(static::CLIENT_PRICE_PRODUCT_STORAGE, function (Container $container) {
            return $container->getLocator()->priceProductStorage()->client();
        });

        return $container;
    }
}
