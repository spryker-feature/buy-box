<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Yves\BuyBox\Reader;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CurrentProductPriceTransfer;
use Generated\Shared\Transfer\MerchantStorageTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use Spryker\Client\MerchantStorage\MerchantStorageClientInterface;
use Spryker\Client\PriceProduct\PriceProductClientInterface;
use Spryker\Client\PriceProductStorage\PriceProductStorageClientInterface;
use Spryker\Client\ProductStorage\ProductStorageClientInterface;
use Spryker\Shared\Kernel\Transfer\Exception\NullValueException;
use SprykerFeature\Yves\BuyBox\Reader\MerchantProductReader;

/**
 * @group SprykerFeatureTest
 * @group Yves
 * @group BuyBox
 * @group Reader
 * @group MerchantProductReaderTest
 */
class MerchantProductReaderTest extends Unit
{
    /**
     * @dataProvider getBuyBoxProductsDataProvider
     *
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     * @param array<string, mixed>|null $productAbstractStorageData
     * @param \Generated\Shared\Transfer\MerchantStorageTransfer|null $merchantStorageTransfer
     * @param int $expectedCount
     * @param array<string, mixed>|null $expectedData
     *
     * @return void
     */
    public function testGetBuyBoxProducts(
        ProductViewTransfer $productViewTransfer,
        ?array $productAbstractStorageData,
        ?MerchantStorageTransfer $merchantStorageTransfer,
        int $expectedCount,
        ?array $expectedData = null
    ): void {
        // Arrange
        $reader = $this->createReader($productAbstractStorageData, $merchantStorageTransfer);

        // Act
        $result = $reader->getBuyBoxProducts($productViewTransfer, 'en_US');

        // Assert
        $this->assertCount($expectedCount, $result);

        if ($expectedData) {
            $buyBoxProduct = $result[0];

            $this->assertSame($expectedData['merchantName'], $buyBoxProduct->getMerchant()->getName());
            $this->assertSame($expectedData['idMerchant'], $buyBoxProduct->getIdMerchant());
            $this->assertSame($expectedData['isNeverOutOfStock'], $buyBoxProduct->getIsNeverOutOfStock());
            $this->assertEquals($expectedData['stockQuantity'], $buyBoxProduct->getStockQuantity());
            $this->assertSame($expectedData['merchantReference'], $buyBoxProduct->getMerchantReference());
            $this->assertInstanceOf(CurrentProductPriceTransfer::class, $buyBoxProduct->getPrice());
            $this->assertInstanceOf(MerchantStorageTransfer::class, $buyBoxProduct->getMerchant());
        }
    }

    public function testGetBuyBoxProductsWithMissingIdProductAbstractNegative(): void
    {
        // Arrange
        $reader = $this->createReader(['name' => 'Test'], new MerchantStorageTransfer());
        $productViewTransfer = (new ProductViewTransfer())->setIdProductConcrete(1);

        // Expect
        $this->expectException(NullValueException::class);

        // Act
        $reader->getBuyBoxProducts($productViewTransfer, 'en_US');
    }

    /**
     * @return array<string, mixed>
     */
    protected function getBuyBoxProductsDataProvider(): array
    {
        return [
            'empty array without concrete product ID' => [
                'productViewTransfer' => (new ProductViewTransfer())
                    ->setIdProductAbstract(1),
                'productAbstractStorageData' => ['name' => 'Test Product', 'merchantReference' => 'MR-1'],
                'merchantStorageTransfer' => (new MerchantStorageTransfer())->setIdMerchant(1),
                'expectedCount' => 0,
            ],
            'empty array with no abstract storage data' => [
                'productViewTransfer' => (new ProductViewTransfer())
                    ->setIdProductConcrete(1)
                    ->setIdProductAbstract(1),
                'productAbstractStorageData' => null,
                'merchantStorageTransfer' => (new MerchantStorageTransfer())->setIdMerchant(1),
                'expectedCount' => 0,
            ],
            'empty array with no merchant reference' => [
                'productViewTransfer' => (new ProductViewTransfer())
                    ->setIdProductConcrete(1)
                    ->setIdProductAbstract(1),
                'productAbstractStorageData' => ['name' => 'Test Product'],
                'merchantStorageTransfer' => (new MerchantStorageTransfer())->setIdMerchant(1),
                'expectedCount' => 0,
            ],
            'empty array with no merchant storage' => [
                'productViewTransfer' => (new ProductViewTransfer())
                    ->setIdProductConcrete(1)
                    ->setIdProductAbstract(1),
                'productAbstractStorageData' => ['name' => 'Test Product', 'merchantReference' => 'MR-1'],
                'merchantStorageTransfer' => null,
                'expectedCount' => 0,
            ],
            'buy box product with complete product data' => [
                'productViewTransfer' => (new ProductViewTransfer())
                    ->setIdProductConcrete(1)
                    ->setIdProductAbstract(1)
                    ->setQuantity(5)
                    ->setIsNeverOutOfStock(false)
                    ->setStockQuantity(10),
                'productAbstractStorageData' => [
                    'name' => 'Test Product',
                    'merchantReference' => 'MR-1',
                ],
                'merchantStorageTransfer' => (new MerchantStorageTransfer())
                    ->setIdMerchant(1)
                    ->setName('Test Merchant'),
                'expectedCount' => 1,
                'expectedData' => [
                    'merchantName' => 'Test Merchant',
                    'idMerchant' => 1,
                    'isNeverOutOfStock' => false,
                    'stockQuantity' => 10,
                    'merchantReference' => 'MR-1',
                ],
            ],
            'never out of stock flag preserved' => [
                'productViewTransfer' => (new ProductViewTransfer())
                    ->setIdProductConcrete(2)
                    ->setIdProductAbstract(2)
                    ->setQuantity(1)
                    ->setIsNeverOutOfStock(true)
                    ->setStockQuantity(0),
                'productAbstractStorageData' => [
                    'name' => 'Another Product',
                    'merchantReference' => 'MR-2',
                ],
                'merchantStorageTransfer' => (new MerchantStorageTransfer())
                    ->setIdMerchant(2)
                    ->setName('Another Merchant'),
                'expectedCount' => 1,
                'expectedData' => [
                    'merchantName' => 'Another Merchant',
                    'idMerchant' => 2,
                    'isNeverOutOfStock' => true,
                    'stockQuantity' => 0,
                    'merchantReference' => 'MR-2',
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed>|null $productAbstractStorageData
     * @param \Generated\Shared\Transfer\MerchantStorageTransfer|null $merchantStorageTransfer
     *
     * @return \SprykerFeature\Yves\BuyBox\Reader\MerchantProductReader
     */
    protected function createReader(
        ?array $productAbstractStorageData,
        ?MerchantStorageTransfer $merchantStorageTransfer
    ): MerchantProductReader {
        $merchantStorageClient = $this->createMerchantStorageClientMock($merchantStorageTransfer);
        $productStorageClient = $this->createProductStorageClientMock($productAbstractStorageData);
        $priceProductClient = $this->createPriceProductClientMock();
        $priceProductStorageClient = $this->createPriceProductStorageClientMock();

        return new MerchantProductReader(
            $merchantStorageClient,
            $productStorageClient,
            $priceProductClient,
            $priceProductStorageClient,
        );
    }

    /**
     * @param \Generated\Shared\Transfer\MerchantStorageTransfer|null $merchantStorageTransfer
     *
     * @return \Spryker\Client\MerchantStorage\MerchantStorageClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createMerchantStorageClientMock(?MerchantStorageTransfer $merchantStorageTransfer): MerchantStorageClientInterface|MockObject
    {
        $mock = $this->createMock(MerchantStorageClientInterface::class);
        $mock->method('findOne')->willReturn($merchantStorageTransfer);

        return $mock;
    }

    /**
     * @param array<string, mixed>|null $productAbstractStorageData
     *
     * @return \Spryker\Client\ProductStorage\ProductStorageClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createProductStorageClientMock(?array $productAbstractStorageData): ProductStorageClientInterface|MockObject
    {
        $mock = $this->createMock(ProductStorageClientInterface::class);
        $mock->method('findProductAbstractStorageData')->willReturn($productAbstractStorageData);

        return $mock;
    }

    protected function createPriceProductClientMock(): PriceProductClientInterface|MockObject
    {
        $currentPriceTransfer = new CurrentProductPriceTransfer();

        $mock = $this->createMock(PriceProductClientInterface::class);
        $mock->method('resolveProductPriceTransferByPriceProductFilter')->willReturn($currentPriceTransfer);

        return $mock;
    }

    protected function createPriceProductStorageClientMock(): PriceProductStorageClientInterface|MockObject
    {
        $mock = $this->createMock(PriceProductStorageClientInterface::class);
        $mock->method('getResolvedPriceProductConcreteTransfers')->willReturn([]);

        return $mock;
    }
}
