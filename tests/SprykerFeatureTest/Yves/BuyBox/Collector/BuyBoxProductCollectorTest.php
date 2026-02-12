<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Yves\BuyBox\Collector;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\BuyBoxProductTransfer;
use Generated\Shared\Transfer\CurrentProductPriceTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Shared\Kernel\StrategyResolverInterface;
use SprykerFeature\Yves\BuyBox\BuyBoxConfig;
use SprykerFeature\Yves\BuyBox\Collector\BuyBoxProductCollector;
use SprykerFeature\Yves\BuyBox\Expander\BuyBoxProductExpanderInterface;
use SprykerFeature\Yves\BuyBox\Reader\ProductReaderInterface;
use SprykerFeature\Yves\BuyBox\Sorter\PriceSorter;
use SprykerFeature\Yves\BuyBox\Sorter\StockSorter;

/**
 * @group SprykerFeatureTest
 * @group Yves
 * @group BuyBox
 * @group Collector
 * @group BuyBoxProductCollectorTest
 */
class BuyBoxProductCollectorTest extends Unit
{
    /**
     * @dataProvider getCollectBuyBoxProductsDataProvider
     *
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     * @param array<array<\Generated\Shared\Transfer\BuyBoxProductTransfer>> $readerResults
     * @param int $expanderCallCount
     * @param int $expectedCount
     * @param string|null $sortingStrategy
     * @param array<\Generated\Shared\Transfer\BuyBoxProductTransfer>|null $expectedProductOrder
     *
     * @return void
     */
    public function testCollectBuyBoxProducts(
        ProductViewTransfer $productViewTransfer,
        array $readerResults,
        int $expanderCallCount,
        int $expectedCount,
        ?string $sortingStrategy = null,
        ?array $expectedProductOrder = null
    ): void {
        // Arrange
        $readers = $this->createReaderMocks($readerResults);
        $expanders = [];

        if ($expanderCallCount > 0) {
            $expanderMock = $this->createMock(BuyBoxProductExpanderInterface::class);
            $expanderMock->expects($this->exactly($expanderCallCount))
                ->method('expand')
                ->willReturnCallback(fn ($products) => $products);
            $expanders = [$expanderMock];
        }

        $sorterResolver = $this->createSorterResolverMock($sortingStrategy ?? BuyBoxConfig::SORT_BY_PRICE);
        $configMock = $this->createConfigMock($sortingStrategy ?? BuyBoxConfig::SORT_BY_PRICE);

        $collector = new BuyBoxProductCollector($readers, $expanders, $sorterResolver, $configMock);

        // Act
        $result = $collector->collectBuyBoxProducts($productViewTransfer, 'en_US');

        // Assert
        $this->assertCount($expectedCount, $result);

        if ($expectedProductOrder !== null) {
            foreach ($expectedProductOrder as $index => $expectedProduct) {
                $this->assertSame($expectedProduct, $result[$index]);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getCollectBuyBoxProductsDataProvider(): array
    {
        $lowPriceProduct = (new BuyBoxProductTransfer())
            ->setPrice((new CurrentProductPriceTransfer())->setPrice(1000));
        $midPriceProduct = (new BuyBoxProductTransfer())
            ->setPrice((new CurrentProductPriceTransfer())->setPrice(2000));
        $highPriceProduct = (new BuyBoxProductTransfer())
            ->setPrice((new CurrentProductPriceTransfer())->setPrice(3000));
        $nullPriceProduct = (new BuyBoxProductTransfer())
            ->setPrice(null);

        $neverOutOfStockProduct = (new BuyBoxProductTransfer())
            ->setIsNeverOutOfStock(true)
            ->setStockQuantity(0.0);
        $highStockProduct = (new BuyBoxProductTransfer())
            ->setIsNeverOutOfStock(false)
            ->setStockQuantity(100.0);
        $lowStockProduct = (new BuyBoxProductTransfer())
            ->setIsNeverOutOfStock(false)
            ->setStockQuantity(5.0);
        $outOfStockProduct = (new BuyBoxProductTransfer())
            ->setIsNeverOutOfStock(false)
            ->setStockQuantity(0.0);

        return [
            'empty array without concrete product ID' => [
                'productViewTransfer' => new ProductViewTransfer(),
                'readerResults' => [
                    [new BuyBoxProductTransfer()],
                ],
                'expanderCallCount' => 0,
                'expectedCount' => 0,
            ],
            'single buy box product from one reader' => [
                'productViewTransfer' => (new ProductViewTransfer())->setIdProductConcrete(1),
                'readerResults' => [
                    [new BuyBoxProductTransfer()],
                ],
                'expanderCallCount' => 1,
                'expectedCount' => 1,
            ],
            'multiple buy box products from multiple readers' => [
                'productViewTransfer' => (new ProductViewTransfer())->setIdProductConcrete(1),
                'readerResults' => [
                    [new BuyBoxProductTransfer(), new BuyBoxProductTransfer()],
                    [new BuyBoxProductTransfer()],
                ],
                'expanderCallCount' => 1,
                'expectedCount' => 3,
            ],
            'empty array when all readers return empty' => [
                'productViewTransfer' => (new ProductViewTransfer())->setIdProductConcrete(1),
                'readerResults' => [
                    [],
                    [],
                ],
                'expanderCallCount' => 1,
                'expectedCount' => 0,
            ],
            'buy box products merged from multiple readers' => [
                'productViewTransfer' => (new ProductViewTransfer())->setIdProductConcrete(1),
                'readerResults' => [
                    [new BuyBoxProductTransfer()],
                    [],
                    [new BuyBoxProductTransfer(), new BuyBoxProductTransfer()],
                ],
                'expanderCallCount' => 1,
                'expectedCount' => 3,
            ],
            'products sorted by price ascending' => [
                'productViewTransfer' => (new ProductViewTransfer())->setIdProductConcrete(1),
                'readerResults' => [
                    [$highPriceProduct, $lowPriceProduct, $midPriceProduct],
                ],
                'expanderCallCount' => 1,
                'expectedCount' => 3,
                'sortingStrategy' => BuyBoxConfig::SORT_BY_PRICE,
                'expectedProductOrder' => [$lowPriceProduct, $midPriceProduct, $highPriceProduct],
            ],
            'products with null prices appear at end when sorting by price' => [
                'productViewTransfer' => (new ProductViewTransfer())->setIdProductConcrete(1),
                'readerResults' => [
                    [$nullPriceProduct, $lowPriceProduct, $highPriceProduct],
                ],
                'expanderCallCount' => 1,
                'expectedCount' => 3,
                'sortingStrategy' => BuyBoxConfig::SORT_BY_PRICE,
                'expectedProductOrder' => [$lowPriceProduct, $highPriceProduct, $nullPriceProduct],
            ],
            'products sorted by stock with never out of stock first' => [
                'productViewTransfer' => (new ProductViewTransfer())->setIdProductConcrete(1),
                'readerResults' => [
                    [$outOfStockProduct, $lowStockProduct, $neverOutOfStockProduct, $highStockProduct],
                ],
                'expanderCallCount' => 1,
                'expectedCount' => 4,
                'sortingStrategy' => BuyBoxConfig::SORT_BY_STOCK,
                'expectedProductOrder' => [$neverOutOfStockProduct, $highStockProduct, $lowStockProduct, $outOfStockProduct],
            ],
            'products sorted by price from multiple readers' => [
                'productViewTransfer' => (new ProductViewTransfer())->setIdProductConcrete(1),
                'readerResults' => [
                    [$highPriceProduct, $midPriceProduct],
                    [$lowPriceProduct],
                ],
                'expanderCallCount' => 1,
                'expectedCount' => 3,
                'sortingStrategy' => BuyBoxConfig::SORT_BY_PRICE,
                'expectedProductOrder' => [$lowPriceProduct, $midPriceProduct, $highPriceProduct],
            ],
            'products sorted by stock from multiple readers' => [
                'productViewTransfer' => (new ProductViewTransfer())->setIdProductConcrete(1),
                'readerResults' => [
                    [$lowStockProduct, $outOfStockProduct],
                    [$neverOutOfStockProduct, $highStockProduct],
                ],
                'expanderCallCount' => 1,
                'expectedCount' => 4,
                'sortingStrategy' => BuyBoxConfig::SORT_BY_STOCK,
                'expectedProductOrder' => [$neverOutOfStockProduct, $highStockProduct, $lowStockProduct, $outOfStockProduct],
            ],
        ];
    }

    /**
     * @param array<array<\Generated\Shared\Transfer\BuyBoxProductTransfer>> $readerResults
     *
     * @return array<\SprykerFeature\Yves\BuyBox\Reader\ProductReaderInterface|\PHPUnit\Framework\MockObject\MockObject>
     */
    protected function createReaderMocks(array $readerResults): array
    {
        $readers = [];

        foreach ($readerResults as $result) {
            $readerMock = $this->createMock(ProductReaderInterface::class);
            $readerMock->method('getBuyBoxProducts')->willReturn($result);
            $readers[] = $readerMock;
        }

        return $readers;
    }

    /**
     * @param string $sortingStrategy
     *
     * @return \Spryker\Shared\Kernel\StrategyResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createSorterResolverMock(string $sortingStrategy): StrategyResolverInterface
    {
        $sorter = $sortingStrategy === BuyBoxConfig::SORT_BY_STOCK
            ? new StockSorter()
            : new PriceSorter();

        $resolverMock = $this->createMock(StrategyResolverInterface::class);
        $resolverMock->method('get')->willReturn($sorter);

        return $resolverMock;
    }

    /**
     * @param string $sortingStrategy
     *
     * @return \SprykerFeature\Yves\BuyBox\BuyBoxConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createConfigMock(string $sortingStrategy): BuyBoxConfig
    {
        $configMock = $this->createMock(BuyBoxConfig::class);
        $configMock->method('getSortingStrategy')->willReturn($sortingStrategy);

        return $configMock;
    }
}
