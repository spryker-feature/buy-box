<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Yves\BuyBox\Expander;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\BuyBoxProductTransfer;
use SprykerFeature\Yves\BuyBox\BuyBoxConfig;
use SprykerFeature\Yves\BuyBox\Expander\InventoryStatusExpander;

/**
 * @group SprykerFeatureTest
 * @group Yves
 * @group BuyBox
 * @group Expander
 * @group InventoryStatusExpanderTest
 */
class InventoryStatusExpanderTest extends Unit
{
    /**
     * @dataProvider getInventoryStatusDataProvider
     *
     * @param array<\Generated\Shared\Transfer\BuyBoxProductTransfer> $buyBoxProductTransfers
     * @param array<string> $expectedStatuses
     *
     * @return void
     */
    public function testExpandInventoryStatus(
        array $buyBoxProductTransfers,
        array $expectedStatuses
    ): void {
        // Arrange
        $expander = new InventoryStatusExpander();

        // Act
        $result = $expander->expand($buyBoxProductTransfers, 'en_US');

        // Assert
        $this->assertCount(count($expectedStatuses), $result);

        foreach ($result as $index => $buyBoxProductTransfer) {
            $this->assertSame($expectedStatuses[$index], $buyBoxProductTransfer->getInventoryStatus());
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getInventoryStatusDataProvider(): array
    {
        return [
            'in stock status with never out of stock flag' => [
                'buyBoxProductTransfers' => [
                    (new BuyBoxProductTransfer())
                        ->setIsNeverOutOfStock(true)
                        ->setStockQuantity(0),
                ],
                'expectedStatuses' => [BuyBoxConfig::INVENTORY_STATUS_IN_STOCK],
            ],
            'in stock status with positive quantity' => [
                'buyBoxProductTransfers' => [
                    (new BuyBoxProductTransfer())
                        ->setIsNeverOutOfStock(false)
                        ->setStockQuantity(5),
                ],
                'expectedStatuses' => [BuyBoxConfig::INVENTORY_STATUS_IN_STOCK],
            ],
            'out of stock status with zero quantity' => [
                'buyBoxProductTransfers' => [
                    (new BuyBoxProductTransfer())
                        ->setIsNeverOutOfStock(false)
                        ->setStockQuantity(0),
                ],
                'expectedStatuses' => [BuyBoxConfig::INVENTORY_STATUS_OUT_OF_STOCK],
            ],
            'mixed inventory statuses for multiple products' => [
                'buyBoxProductTransfers' => [
                    (new BuyBoxProductTransfer())
                        ->setIsNeverOutOfStock(true)
                        ->setStockQuantity(0),
                    (new BuyBoxProductTransfer())
                        ->setIsNeverOutOfStock(false)
                        ->setStockQuantity(10),
                    (new BuyBoxProductTransfer())
                        ->setIsNeverOutOfStock(false)
                        ->setStockQuantity(0),
                    (new BuyBoxProductTransfer())
                        ->setIsNeverOutOfStock(true)
                        ->setStockQuantity(100),
                ],
                'expectedStatuses' => [
                    BuyBoxConfig::INVENTORY_STATUS_IN_STOCK,
                    BuyBoxConfig::INVENTORY_STATUS_IN_STOCK,
                    BuyBoxConfig::INVENTORY_STATUS_OUT_OF_STOCK,
                    BuyBoxConfig::INVENTORY_STATUS_IN_STOCK,
                ],
            ],
            'empty array with empty product list' => [
                'buyBoxProductTransfers' => [],
                'expectedStatuses' => [],
            ],
        ];
    }
}
