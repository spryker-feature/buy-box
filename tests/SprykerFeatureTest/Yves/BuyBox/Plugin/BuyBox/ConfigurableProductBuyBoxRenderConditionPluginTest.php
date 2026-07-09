<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Yves\BuyBox\Plugin\BuyBox;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ProductConfigurationInstanceTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use SprykerFeature\Yves\BuyBox\Plugin\BuyBox\ConfigurableProductBuyBoxRenderConditionPlugin;

/**
 * @group SprykerFeatureTest
 * @group Yves
 * @group BuyBox
 * @group Plugin
 * @group ConfigurableProductBuyBoxRenderConditionPluginTest
 */
class ConfigurableProductBuyBoxRenderConditionPluginTest extends Unit
{
    public function testCheckConditionReturnsFalseForConfigurableProduct(): void
    {
        // Arrange
        $productViewTransfer = (new ProductViewTransfer())
            ->setProductConfigurationInstance(new ProductConfigurationInstanceTransfer());

        // Act
        $result = (new ConfigurableProductBuyBoxRenderConditionPlugin())->checkCondition($productViewTransfer);

        // Assert
        $this->assertFalse($result);
    }

    public function testCheckConditionReturnsTrueForRegularProduct(): void
    {
        // Act
        $result = (new ConfigurableProductBuyBoxRenderConditionPlugin())->checkCondition(new ProductViewTransfer());

        // Assert
        $this->assertTrue($result);
    }
}
