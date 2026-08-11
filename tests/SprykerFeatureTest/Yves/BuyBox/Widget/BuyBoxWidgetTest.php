<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Yves\BuyBox\Widget;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\BuyBoxProductTransfer;
use Generated\Shared\Transfer\CurrentProductPriceTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use ReflectionClass;
use SprykerFeature\Yves\BuyBox\BuyBoxFactory;
use SprykerFeature\Yves\BuyBox\Dependency\Plugin\BuyBoxRenderConditionPluginInterface;
use SprykerFeature\Yves\BuyBox\Widget\BuyBoxWidget;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group SprykerFeatureTest
 * @group Yves
 * @group BuyBox
 * @group Widget
 * @group BuyBoxWidgetTest
 */
class BuyBoxWidgetTest extends Unit
{
    protected const int PRESELECTED_PRICE = 900;

    protected const string PRODUCT_OFFER_REFERENCE = 'offer-reference-1';

    protected const string REQUEST_PARAM_ATTRIBUTE = 'attribute';

    protected const string ATTRIBUTE_SELECTED_MERCHANT_REFERENCE_TYPE = 'selected_merchant_reference_type';

    protected const string ATTRIBUTE_SELECTED_MERCHANT_REFERENCE = 'selected_merchant_reference';

    public function testShouldRenderBuyBoxReturnsFalseWhenAnyRenderConditionPluginVetoes(): void
    {
        // Arrange
        $widget = $this->createWidgetWithRenderConditionPlugins(
            $this->createRenderConditionPluginMocks([true, false]),
        );

        // Act
        $result = $this->invokeShouldRenderBuyBox($widget, new ProductViewTransfer());

        // Assert
        $this->assertFalse($result);
    }

    public function testShouldRenderBuyBoxReturnsTrueWhenAllRenderConditionPluginsAllow(): void
    {
        // Arrange
        $widget = $this->createWidgetWithRenderConditionPlugins(
            $this->createRenderConditionPluginMocks([true, true]),
        );

        // Act
        $result = $this->invokeShouldRenderBuyBox($widget, new ProductViewTransfer());

        // Assert
        $this->assertTrue($result);
    }

    public function testShouldRenderBuyBoxReturnsTrueWhenStackIsEmpty(): void
    {
        // Arrange
        $widget = $this->createWidgetWithRenderConditionPlugins([]);

        // Act
        $result = $this->invokeShouldRenderBuyBox($widget, new ProductViewTransfer());

        // Assert
        $this->assertTrue($result);
    }

    public function testUpdateProductViewWithPreselectedDataAppliesPriceAvailabilityAndOffer(): void
    {
        // Arrange
        $preSelectedPriceTransfer = (new CurrentProductPriceTransfer())->setPrice(static::PRESELECTED_PRICE);
        $productViewTransfer = new ProductViewTransfer();
        $preSelectedProduct = (new BuyBoxProductTransfer())
            ->setIsAvailable(true)
            ->setProductOfferReference(static::PRODUCT_OFFER_REFERENCE)
            ->setPrice($preSelectedPriceTransfer);

        // Act
        $this->invokeUpdateProductViewWithPreselectedData($productViewTransfer, $preSelectedProduct);

        // Assert
        $this->assertSame($preSelectedPriceTransfer, $productViewTransfer->getCurrentProductPrice());
        $this->assertTrue($productViewTransfer->getAvailable());
        $this->assertSame(static::PRODUCT_OFFER_REFERENCE, $productViewTransfer->getProductOfferReference());
    }

    public function testShouldPreSelectProductReturnsFalseForSingleOffer(): void
    {
        // Act
        $result = $this->invokeShouldPreSelectProduct(new Request(), [new BuyBoxProductTransfer()]);

        // Assert
        $this->assertFalse($result);
    }

    public function testShouldPreSelectProductReturnsTrueForMultipleOffersWithoutSelection(): void
    {
        // Act
        $result = $this->invokeShouldPreSelectProduct(
            new Request(),
            [new BuyBoxProductTransfer(), new BuyBoxProductTransfer()],
        );

        // Assert
        $this->assertTrue($result);
    }

    public function testShouldPreSelectProductReturnsFalseWhenMerchantReferenceSelected(): void
    {
        // Arrange
        $request = new Request([
            static::REQUEST_PARAM_ATTRIBUTE => [
                static::ATTRIBUTE_SELECTED_MERCHANT_REFERENCE_TYPE => 'merchant',
                static::ATTRIBUTE_SELECTED_MERCHANT_REFERENCE => 'mer-1',
            ],
        ]);

        // Act
        $result = $this->invokeShouldPreSelectProduct(
            $request,
            [new BuyBoxProductTransfer(), new BuyBoxProductTransfer()],
        );

        // Assert
        $this->assertFalse($result);
    }

    /**
     * @param array<\SprykerFeature\Yves\BuyBox\Dependency\Plugin\BuyBoxRenderConditionPluginInterface> $buyBoxRenderConditionPlugins
     */
    protected function createWidgetWithRenderConditionPlugins(array $buyBoxRenderConditionPlugins): BuyBoxWidget
    {
        $factoryMock = $this->createMock(BuyBoxFactory::class);
        $factoryMock->method('getBuyBoxRenderConditionPlugins')->willReturn($buyBoxRenderConditionPlugins);

        return new class ($factoryMock) extends BuyBoxWidget {
            public function __construct(protected BuyBoxFactory $buyBoxFactory)
            {
            }

            protected function getFactory(): BuyBoxFactory
            {
                return $this->buyBoxFactory;
            }
        };
    }

    /**
     * @param array<bool> $checkConditionResults
     *
     * @return array<\SprykerFeature\Yves\BuyBox\Dependency\Plugin\BuyBoxRenderConditionPluginInterface>
     */
    protected function createRenderConditionPluginMocks(array $checkConditionResults): array
    {
        $plugins = [];

        foreach ($checkConditionResults as $checkConditionResult) {
            $pluginMock = $this->createMock(BuyBoxRenderConditionPluginInterface::class);
            $pluginMock->method('checkCondition')->willReturn($checkConditionResult);
            $plugins[] = $pluginMock;
        }

        return $plugins;
    }

    protected function invokeShouldRenderBuyBox(BuyBoxWidget $buyBoxWidget, ProductViewTransfer $productViewTransfer): bool
    {
        $reflectionMethod = (new ReflectionClass($buyBoxWidget))->getMethod('shouldRenderBuyBox');

        return $reflectionMethod->invoke($buyBoxWidget, $productViewTransfer);
    }

    protected function invokeUpdateProductViewWithPreselectedData(
        ProductViewTransfer $productViewTransfer,
        BuyBoxProductTransfer $preSelectedProduct
    ): void {
        $reflectionClass = new ReflectionClass(BuyBoxWidget::class);
        $buyBoxWidget = $reflectionClass->newInstanceWithoutConstructor();
        $reflectionMethod = $reflectionClass->getMethod('updateProductViewWithPreselectedData');
        $reflectionMethod->invoke($buyBoxWidget, $productViewTransfer, $preSelectedProduct);
    }

    /**
     * @param array<\Generated\Shared\Transfer\BuyBoxProductTransfer> $buyBoxProducts
     */
    protected function invokeShouldPreSelectProduct(Request $request, array $buyBoxProducts): bool
    {
        $reflectionClass = new ReflectionClass(BuyBoxWidget::class);
        $buyBoxWidget = $reflectionClass->newInstanceWithoutConstructor();
        $reflectionMethod = $reflectionClass->getMethod('shouldPreSelectProduct');

        return $reflectionMethod->invoke($buyBoxWidget, $request, $buyBoxProducts);
    }
}
