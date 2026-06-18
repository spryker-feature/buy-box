<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Yves\BuyBox\Widget;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\BuyBoxProductTransfer;
use Generated\Shared\Transfer\CurrentProductPriceTransfer;
use Generated\Shared\Transfer\ProductConfigurationInstanceTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use ReflectionClass;
use SprykerFeature\Yves\BuyBox\Widget\BuyBoxWidget;

/**
 * @group SprykerFeatureTest
 * @group Yves
 * @group BuyBox
 * @group Widget
 * @group BuyBoxWidgetTest
 */
class BuyBoxWidgetTest extends Unit
{
    protected const int CONFIGURED_PRICE = 1500;

    protected const int PRESELECTED_PRICE = 900;

    protected const string PRODUCT_OFFER_REFERENCE = 'offer-reference-1';

    public function testGivenProductViewWithoutConfigurationInstanceWhenUpdatedThenCurrentPriceTakenFromPreselectedProduct(): void
    {
        // Arrange
        $preSelectedPriceTransfer = (new CurrentProductPriceTransfer())->setPrice(static::PRESELECTED_PRICE);
        $productViewTransfer = new ProductViewTransfer();
        $preSelectedProduct = (new BuyBoxProductTransfer())
            ->setIsAvailable(true)
            ->setProductOfferReference(static::PRODUCT_OFFER_REFERENCE)
            ->setPrice($preSelectedPriceTransfer);

        // Act
        $this->updateProductViewWithPreselectedData($productViewTransfer, $preSelectedProduct);

        // Assert
        $this->assertSame($preSelectedPriceTransfer, $productViewTransfer->getCurrentProductPrice());
        $this->assertTrue($productViewTransfer->getAvailable());
        $this->assertSame(static::PRODUCT_OFFER_REFERENCE, $productViewTransfer->getProductOfferReference());
    }

    public function testGivenProductViewWithConfigurationInstanceWhenUpdatedThenCurrentPriceIsPreservedButAvailabilityAndOfferUpdated(): void
    {
        // Arrange
        $configuredPriceTransfer = (new CurrentProductPriceTransfer())->setPrice(static::CONFIGURED_PRICE);
        $productViewTransfer = (new ProductViewTransfer())
            ->setCurrentProductPrice($configuredPriceTransfer)
            ->setProductConfigurationInstance(new ProductConfigurationInstanceTransfer());
        $preSelectedProduct = (new BuyBoxProductTransfer())
            ->setIsAvailable(true)
            ->setProductOfferReference(static::PRODUCT_OFFER_REFERENCE)
            ->setPrice((new CurrentProductPriceTransfer())->setPrice(static::PRESELECTED_PRICE));

        // Act
        $this->updateProductViewWithPreselectedData($productViewTransfer, $preSelectedProduct);

        // Assert
        $this->assertSame($configuredPriceTransfer, $productViewTransfer->getCurrentProductPrice());
        $this->assertTrue($productViewTransfer->getAvailable());
        $this->assertSame(static::PRODUCT_OFFER_REFERENCE, $productViewTransfer->getProductOfferReference());
    }

    /**
     * Invokes the protected widget method without booting the widget constructor
     * (which performs factory and locale work unrelated to the behavior under test).
     */
    protected function updateProductViewWithPreselectedData(
        ProductViewTransfer $productViewTransfer,
        BuyBoxProductTransfer $preSelectedProduct
    ): void {
        $reflectionClass = new ReflectionClass(BuyBoxWidget::class);
        $buyBoxWidget = $reflectionClass->newInstanceWithoutConstructor();
        $reflectionMethod = $reflectionClass->getMethod('updateProductViewWithPreselectedData');
        $reflectionMethod->invoke($buyBoxWidget, $productViewTransfer, $preSelectedProduct);
    }
}
