<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\BuyBox\Plugin\BuyBox;

use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Yves\Kernel\AbstractPlugin;
use SprykerFeature\Yves\BuyBox\Dependency\Plugin\BuyBoxRenderConditionPluginInterface;

class ConfigurableProductBuyBoxRenderConditionPlugin extends AbstractPlugin implements BuyBoxRenderConditionPluginInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function checkCondition(ProductViewTransfer $productViewTransfer): bool
    {
        return $productViewTransfer->getProductConfigurationInstance() === null;
    }
}
