<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\BuyBox\Widget;

use Generated\Shared\Transfer\BuyBoxProductTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Yves\Kernel\Widget\AbstractWidget;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \SprykerFeature\Yves\BuyBox\BuyBoxFactory getFactory()
 * @method \SprykerFeature\Yves\BuyBox\BuyBoxConfig getConfig()
 */
class BuyBoxWidget extends AbstractWidget
{
    protected const string PARAMETER_PRODUCTS = 'products';

    protected const string PARAMETER_PRODUCT_VIEW = 'productView';

    protected const string REQUEST_PARAM_ATTRIBUTE = 'attribute';

    protected const string ATTRIBUTE_SELECTED_MERCHANT_REFERENCE_TYPE = 'selected_merchant_reference_type';

    protected const string ATTRIBUTE_SELECTED_MERCHANT_REFERENCE = 'selected_merchant_reference';

    public function __construct(ProductViewTransfer $productViewTransfer, Request $request)
    {
        if (!$this->shouldRenderBuyBox($productViewTransfer)) {
            $this->addProductsParameter([]);
            $this->addProductViewParameter($productViewTransfer);

            return;
        }

        $buyBoxProducts = $this->collectBuyBoxProducts($productViewTransfer);
        $this->expandProductViewTransfer($productViewTransfer, $buyBoxProducts, $request);
        $this->addProductsParameter($buyBoxProducts);
        $this->addProductViewParameter($productViewTransfer);
    }

    public static function getName(): string
    {
        return 'BuyBoxWidget';
    }

    public static function getTemplate(): string
    {
        return '@BuyBox/views/buy-box-widget/buy-box-widget.twig';
    }

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     *
     * @return array<\Generated\Shared\Transfer\BuyBoxProductTransfer>
     */
    protected function collectBuyBoxProducts(ProductViewTransfer $productViewTransfer): array
    {
        return $this->getFactory()
            ->createBuyBoxProductCollector()
            ->collectBuyBoxProducts($productViewTransfer, $this->getLocale());
    }

    /**
     * @param array<\Generated\Shared\Transfer\BuyBoxProductTransfer> $buyBoxProducts
     *
     * @return void
     */
    protected function addProductsParameter(array $buyBoxProducts): void
    {
        $this->addParameter(static::PARAMETER_PRODUCTS, $buyBoxProducts);
    }

    /**
     * Overrides $productViewTransfer with productOfferReference, availability and currentProductPrice from the preselected product offer or merchant product.
     *
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     * @param array<\Generated\Shared\Transfer\BuyBoxProductTransfer> $buyBoxProducts
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return void
     */
    protected function expandProductViewTransfer(
        ProductViewTransfer $productViewTransfer,
        array $buyBoxProducts,
        Request $request
    ): void {
        $preSelectedProduct = $buyBoxProducts[0] ?? null;

        if ($preSelectedProduct === null || !$this->shouldPreSelectProduct($request, $buyBoxProducts)) {
            return;
        }

        $this->updateProductViewWithPreselectedData($productViewTransfer, $preSelectedProduct);
    }

    protected function updateProductViewWithPreselectedData(
        ProductViewTransfer $productViewTransfer,
        BuyBoxProductTransfer $preSelectedProduct
    ): void {
        $productViewTransfer
            ->setAvailable($preSelectedProduct->getIsAvailable())
            ->setProductOfferReference($preSelectedProduct->getProductOfferReference())
            ->setCurrentProductPrice($preSelectedProduct->getPrice());
    }

    protected function addProductViewParameter(ProductViewTransfer $productViewTransfer): void
    {
        $this->addParameter(static::PARAMETER_PRODUCT_VIEW, $productViewTransfer);
    }

    /**
     * @param array<\Generated\Shared\Transfer\BuyBoxProductTransfer> $buyBoxProducts
     */
    protected function shouldPreSelectProduct(Request $request, array $buyBoxProducts): bool
    {
        if (count($buyBoxProducts) <= 1) {
            return false;
        }

        $selectedAttributes = $request->query->all(static::REQUEST_PARAM_ATTRIBUTE);

        return !isset($selectedAttributes[static::ATTRIBUTE_SELECTED_MERCHANT_REFERENCE_TYPE])
            && !isset($selectedAttributes[static::ATTRIBUTE_SELECTED_MERCHANT_REFERENCE]);
    }

    protected function shouldRenderBuyBox(ProductViewTransfer $productViewTransfer): bool
    {
        foreach ($this->getFactory()->getBuyBoxRenderConditionPlugins() as $buyBoxRenderConditionPlugin) {
            if (!$buyBoxRenderConditionPlugin->checkCondition($productViewTransfer)) {
                return false;
            }
        }

        return true;
    }
}
