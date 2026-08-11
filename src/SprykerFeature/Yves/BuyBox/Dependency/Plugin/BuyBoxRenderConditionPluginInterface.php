<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\BuyBox\Dependency\Plugin;

use Generated\Shared\Transfer\ProductViewTransfer;

interface BuyBoxRenderConditionPluginInterface
{
    /**
     * Specification:
     * - Determines whether the buy box should be rendered for the given product.
     * - Returns `true` when the condition is satisfied and the buy box should be rendered for the product.
     * - Returns `false` when the condition is not satisfied and the buy box must not be rendered for the product.
     *
     * @api
     */
    public function checkCondition(ProductViewTransfer $productViewTransfer): bool;
}
