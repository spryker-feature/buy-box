<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\BuyBox\Reader;

use Generated\Shared\Transfer\ProductViewTransfer;

interface ProductReaderInterface
{
    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     * @param string $localeName
     *
     * @return array<\Generated\Shared\Transfer\BuyBoxProductTransfer>
     */
    public function getBuyBoxProducts(ProductViewTransfer $productViewTransfer, string $localeName): array;
}
