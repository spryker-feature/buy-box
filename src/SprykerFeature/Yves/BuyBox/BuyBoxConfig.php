<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\BuyBox;

use Spryker\Yves\Kernel\AbstractBundleConfig;

class BuyBoxConfig extends AbstractBundleConfig
{
    public const string INVENTORY_STATUS_IN_STOCK = 'in-stock';

    public const string INVENTORY_STATUS_OUT_OF_STOCK = 'out-of-stock';

    public const string SORT_BY_PRICE = 'price';

    /**
     * Requires Spryker\Client\ProductOfferAvailabilityStorage\Plugin\ProductOfferStorage\ProductOfferAvailabilityProductOfferStorageBulkExpanderPlugin
     * to be wired, so that stock information is available in product offers.
     */
    public const string SORT_BY_STOCK = 'stock';

    /**
     * Specification:
     * - Returns the sorting strategy for buy box products.
     * - Default sorting is by price ascending.
     *
     * @api
     */
    public function getSortingStrategy(): string
    {
        return static::SORT_BY_PRICE;
    }
}
