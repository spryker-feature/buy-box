<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\BuyBox\Collector;

use Generated\Shared\Transfer\ProductViewTransfer;

interface BuyBoxProductCollectorInterface
{
    /**
     * @param array<string, mixed> $context
     *
     * @return array<\Generated\Shared\Transfer\BuyBoxProductTransfer>
     */
    public function collectBuyBoxProducts(ProductViewTransfer $productViewTransfer, string $localeName, array $context = []): array;
}
