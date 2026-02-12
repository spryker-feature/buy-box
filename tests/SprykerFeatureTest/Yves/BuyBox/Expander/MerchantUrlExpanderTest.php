<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Yves\BuyBox\Expander;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\BuyBoxProductTransfer;
use Generated\Shared\Transfer\MerchantStorageTransfer;
use Generated\Shared\Transfer\UrlTransfer;
use SprykerFeature\Yves\BuyBox\Expander\MerchantUrlExpander;

/**
 * @group SprykerFeatureTest
 * @group Yves
 * @group BuyBox
 * @group Expander
 * @group MerchantUrlExpanderTest
 */
class MerchantUrlExpanderTest extends Unit
{
    /**
     * @dataProvider getMerchantUrlDataProvider
     *
     * @param array<\Generated\Shared\Transfer\BuyBoxProductTransfer> $buyBoxProductTransfers
     * @param string $localeName
     * @param array<string> $expectedUrls
     *
     * @return void
     */
    public function testExpandMerchantUrl(
        array $buyBoxProductTransfers,
        string $localeName,
        array $expectedUrls
    ): void {
        // Arrange
        $expander = new MerchantUrlExpander();

        // Act
        $result = $expander->expand($buyBoxProductTransfers, $localeName);

        // Assert
        $this->assertCount(count($expectedUrls), $result);

        foreach ($result as $index => $buyBoxProductTransfer) {
            $merchantUrl = $buyBoxProductTransfer->getMerchant()?->getMerchantUrl();
            $this->assertSame($expectedUrls[$index], $merchantUrl);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getMerchantUrlDataProvider(): array
    {
        return [
            'merchant URL resolved for matching English locale' => [
                'buyBoxProductTransfers' => [
                    (new BuyBoxProductTransfer())->setMerchant(
                        (new MerchantStorageTransfer())
                            ->addUrl((new UrlTransfer())->setUrl('/en/merchant-one'))
                            ->addUrl((new UrlTransfer())->setUrl('/de/merchant-one')),
                    ),
                ],
                'localeName' => 'en_US',
                'expectedUrls' => ['/en/merchant-one'],
            ],
            'merchant URL resolved for matching German locale' => [
                'buyBoxProductTransfers' => [
                    (new BuyBoxProductTransfer())->setMerchant(
                        (new MerchantStorageTransfer())
                            ->addUrl((new UrlTransfer())->setUrl('/en/merchant-two'))
                            ->addUrl((new UrlTransfer())->setUrl('/de/merchant-two')),
                    ),
                ],
                'localeName' => 'de_DE',
                'expectedUrls' => ['/de/merchant-two'],
            ],
            'empty URL with no matching locale' => [
                'buyBoxProductTransfers' => [
                    (new BuyBoxProductTransfer())->setMerchant(
                        (new MerchantStorageTransfer())
                            ->addUrl((new UrlTransfer())->setUrl('/fr/merchant-three'))
                            ->addUrl((new UrlTransfer())->setUrl('/es/merchant-three')),
                    ),
                ],
                'localeName' => 'en_US',
                'expectedUrls' => [''],
            ],
            'null URL without merchant' => [
                'buyBoxProductTransfers' => [
                    (new BuyBoxProductTransfer())->setMerchant(null),
                ],
                'localeName' => 'en_US',
                'expectedUrls' => [null],
            ],
            'mixed URL configurations for multiple products' => [
                'buyBoxProductTransfers' => [
                    (new BuyBoxProductTransfer())->setMerchant(
                        (new MerchantStorageTransfer())
                            ->addUrl((new UrlTransfer())->setUrl('/en/merchant-a'))
                            ->addUrl((new UrlTransfer())->setUrl('/de/merchant-a')),
                    ),
                    (new BuyBoxProductTransfer())->setMerchant(null),
                    (new BuyBoxProductTransfer())->setMerchant(
                        (new MerchantStorageTransfer())
                            ->addUrl((new UrlTransfer())->setUrl('/fr/merchant-b')),
                    ),
                    (new BuyBoxProductTransfer())->setMerchant(
                        (new MerchantStorageTransfer())
                            ->addUrl((new UrlTransfer())->setUrl('/en/merchant-c')),
                    ),
                ],
                'localeName' => 'en_US',
                'expectedUrls' => ['/en/merchant-a', null, '', '/en/merchant-c'],
            ],
            'empty array with empty product list' => [
                'buyBoxProductTransfers' => [],
                'localeName' => 'en_US',
                'expectedUrls' => [],
            ],
        ];
    }
}
