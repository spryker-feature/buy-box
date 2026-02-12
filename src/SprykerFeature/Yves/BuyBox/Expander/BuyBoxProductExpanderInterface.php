<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\BuyBox\Expander;

interface BuyBoxProductExpanderInterface
{
    /**
     * @param array<\Generated\Shared\Transfer\BuyBoxProductTransfer> $buyBoxProductTransfers
     * @param string $localeName
     *
     * @return array<\Generated\Shared\Transfer\BuyBoxProductTransfer>
     */
    public function expand(array $buyBoxProductTransfers, string $localeName): array;
}
