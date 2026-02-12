<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\BuyBox\Expander;

use Generated\Shared\Transfer\MerchantStorageTransfer;

class MerchantUrlExpander implements BuyBoxProductExpanderInterface
{
    /**
     * @param array<\Generated\Shared\Transfer\BuyBoxProductTransfer> $buyBoxProductTransfers
     * @param string $localeName
     *
     * @return array<\Generated\Shared\Transfer\BuyBoxProductTransfer>
     */
    public function expand(array $buyBoxProductTransfers, string $localeName): array
    {
        foreach ($buyBoxProductTransfers as $buyBoxProductTransfer) {
            $merchant = $buyBoxProductTransfer->getMerchant();

            if (!$merchant) {
                continue;
            }

            $merchant->setMerchantUrl($this->getResolvedUrl($merchant, $localeName));
        }

        return $buyBoxProductTransfers;
    }

    protected function getResolvedUrl(MerchantStorageTransfer $merchantStorageTransfer, string $localeName): string
    {
        $locale = strstr($localeName, '_', true);

        foreach ($merchantStorageTransfer->getUrlCollection() as $urlTransfer) {
            /** @var string $url */
            $url = $urlTransfer->getUrl();

            $urlLocale = mb_substr($url, 1, 2);
            if ($locale === $urlLocale) {
                return $url;
            }
        }

        return '';
    }
}
