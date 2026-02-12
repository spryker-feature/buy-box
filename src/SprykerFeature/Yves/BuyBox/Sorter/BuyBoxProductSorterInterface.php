<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\BuyBox\Sorter;

interface BuyBoxProductSorterInterface
{
    /**
     * @param array<\Generated\Shared\Transfer\BuyBoxProductTransfer> $buyBoxProducts
     *
     * @return array<\Generated\Shared\Transfer\BuyBoxProductTransfer>
     */
    public function sort(array $buyBoxProducts): array;
}
