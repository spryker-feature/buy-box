<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Yves\BuyBox\Reader;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\MerchantStorageTransfer;
use Generated\Shared\Transfer\ProductOfferStorageCollectionTransfer;
use Generated\Shared\Transfer\ProductOfferStorageTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use Spryker\Client\ProductOfferStorage\ProductOfferStorageClientInterface;
use SprykerFeature\Yves\BuyBox\Reader\MerchantProductOfferReader;

/**
 * @group SprykerFeatureTest
 * @group Yves
 * @group BuyBox
 * @group Reader
 * @group MerchantProductOfferReaderTest
 */
class MerchantProductOfferReaderTest extends Unit
{
    /**
     * @dataProvider getBuyBoxProductsDataProvider
     *
     * @param array<\Generated\Shared\Transfer\ProductOfferStorageTransfer> $productOffers
     * @param int $expectedCount
     * @param array<array<string, mixed>> $expectedProducts
     *
     * @return void
     */
    public function testGetBuyBoxProducts(
        array $productOffers,
        int $expectedCount,
        array $expectedProducts = []
    ): void {
        // Arrange
        $reader = $this->createReaderWithMockedClient($productOffers);
        $productViewTransfer = $this->createProductViewTransfer();

        // Act
        $result = $reader->getBuyBoxProducts($productViewTransfer, 'en_US');

        // Assert
        $this->assertCount($expectedCount, $result);

        foreach ($expectedProducts as $index => $expectedProduct) {
            $buyBoxProduct = $result[$index];

            $this->assertSame($expectedProduct['productOfferReference'], $buyBoxProduct->getProductOfferReference());
            $this->assertSame($expectedProduct['merchantName'], $buyBoxProduct->getMerchant()->getName());
            $this->assertEquals($expectedProduct['stockQuantity'], $buyBoxProduct->getStockQuantity());
            $this->assertSame($expectedProduct['isNeverOutOfStock'], $buyBoxProduct->getIsNeverOutOfStock());
            $this->assertInstanceOf(MerchantStorageTransfer::class, $buyBoxProduct->getMerchant());
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getBuyBoxProductsDataProvider(): array
    {
        return [
            'empty array with no product offers' => [
                'productOffers' => [],
                'expectedCount' => 0,
                'expectedProducts' => [],
            ],
            'single buy box product with one product offer' => [
                'productOffers' => [
                    $this->createProductOfferStorageTransfer('offer-1', 'Merchant A', 10, false),
                ],
                'expectedCount' => 1,
                'expectedProducts' => [
                    [
                        'productOfferReference' => 'offer-1',
                        'merchantName' => 'Merchant A',
                        'stockQuantity' => 10,
                        'isNeverOutOfStock' => false,
                    ],
                ],
            ],
            'multiple buy box products with multiple product offers' => [
                'productOffers' => [
                    $this->createProductOfferStorageTransfer('offer-1', 'Merchant A', 5, false),
                    $this->createProductOfferStorageTransfer('offer-2', 'Merchant B', 0, true),
                    $this->createProductOfferStorageTransfer('offer-3', 'Merchant C', 20, false),
                ],
                'expectedCount' => 3,
                'expectedProducts' => [
                    [
                        'productOfferReference' => 'offer-1',
                        'merchantName' => 'Merchant A',
                        'stockQuantity' => 5,
                        'isNeverOutOfStock' => false,
                    ],
                    [
                        'productOfferReference' => 'offer-2',
                        'merchantName' => 'Merchant B',
                        'stockQuantity' => 0,
                        'isNeverOutOfStock' => true,
                    ],
                    [
                        'productOfferReference' => 'offer-3',
                        'merchantName' => 'Merchant C',
                        'stockQuantity' => 20,
                        'isNeverOutOfStock' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array<\Generated\Shared\Transfer\ProductOfferStorageTransfer> $productOffers
     *
     * @return \SprykerFeature\Yves\BuyBox\Reader\MerchantProductOfferReader
     */
    protected function createReaderWithMockedClient(array $productOffers): MerchantProductOfferReader
    {
        $clientMock = $this->createProductOfferStorageClientMock($productOffers);

        return new MerchantProductOfferReader($clientMock);
    }

    /**
     * @param array<\Generated\Shared\Transfer\ProductOfferStorageTransfer> $productOffers
     *
     * @return \Spryker\Client\ProductOfferStorage\ProductOfferStorageClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createProductOfferStorageClientMock(array $productOffers): ProductOfferStorageClientInterface|MockObject
    {
        $collectionTransfer = new ProductOfferStorageCollectionTransfer();

        foreach ($productOffers as $productOffer) {
            $collectionTransfer->addProductOffer($productOffer);
        }

        $clientMock = $this->createMock(ProductOfferStorageClientInterface::class);
        $clientMock->method('getProductOfferStoragesBySkus')->willReturn($collectionTransfer);

        return $clientMock;
    }

    protected function createProductViewTransfer(): ProductViewTransfer
    {
        return (new ProductViewTransfer())->setSku('TEST-SKU-123');
    }

    protected function createProductOfferStorageTransfer(
        string $reference,
        string $merchantName,
        int $stockQuantity = 0,
        bool $isNeverOutOfStock = false
    ): ProductOfferStorageTransfer {
        $merchantStorageTransfer = (new MerchantStorageTransfer())->setName($merchantName);

        return (new ProductOfferStorageTransfer())
            ->setProductOfferReference($reference)
            ->setMerchantStorage($merchantStorageTransfer)
            ->setStockQuantity($stockQuantity)
            ->setIsNeverOutOfStock($isNeverOutOfStock);
    }
}
