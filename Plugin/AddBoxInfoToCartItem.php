<?php
/**
 * CravenDunnill ProductTileCalculator Extension
 *
 * @category  CravenDunnill
 * @package   CravenDunnill_ProductTileCalculator
 */

namespace CravenDunnill\ProductTileCalculator\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart\ItemOptionsProcessor;
use Magento\Quote\Api\Data\CartItemInterface;
use CravenDunnill\ProductTileCalculator\Helper\Calculator;

class AddBoxInfoToCartItem
{
	/**
	 * @var Calculator
	 */
	protected $calculatorHelper;

	/**
	 * Constructor
	 * 
	 * @param Calculator $calculatorHelper
	 */
	public function __construct(
		Calculator $calculatorHelper
	) {
		$this->calculatorHelper = $calculatorHelper;
	}

	/**
	 * Add box quantity information to quote item options
	 *
	 * @param ItemOptionsProcessor $subject
	 * @param CartItemInterface $cartItem
	 * @return CartItemInterface
	 */
	public function afterProcess(
		ItemOptionsProcessor $subject,
		CartItemInterface $cartItem
	) {
		$productInfo = $cartItem->getProductOption();
		if ($productInfo === null) {
			return $cartItem;
		}
		
		$product = $cartItem->getProduct();
		if (!$product) {
			return $cartItem;
		}
		
		// Check if this product can use calculator
		if (!$this->calculatorHelper->canUseCalculator($product)) {
			return $cartItem;
		}
		
		// Calculate box info
		$tilesQty = $cartItem->getQty();
		$boxQuantity = $this->calculatorHelper->getBoxQuantity($product);
		$boxes = $this->calculatorHelper->calculateBoxesFromTiles($tilesQty, $product);
		$m2 = $this->calculatorHelper->calculateM2FromBoxes($boxes, $product);
		
		// Get colour information
		$colorValue = '';
		try {
			$colorValue = $product->getAttributeText('tile_colour_name');
			if (empty($colorValue)) {
				$colorValue = $product->getData('tile_colour_name');
			}
		} catch (\Exception $e) {
			$colorValue = $product->getData('tile_colour_name');
		}
		
		// Get size information
		$sizeValue = '';
		try {
			$sizeValue = $product->getAttributeText('tile_size');
			if (empty($sizeValue)) {
				$sizeValue = $product->getData('tile_size');
			}
		} catch (\Exception $e) {
			$sizeValue = $product->getData('tile_size');
		}
		
		// Set extension attributes or additional options
		$additionalOptions = [];
		
		// Add all the details
		$additionalOptions[] = [
			'label' => __('Colour'),
			'value' => $colorValue ?: __('n/a')
		];
		
		$additionalOptions[] = [
			'label' => __('Size'),
			'value' => $sizeValue ?: __('n/a')
		];
		
		$additionalOptions[] = [
			'label' => __('Tiles per Box'),
			'value' => $boxQuantity
		];
		
		$additionalOptions[] = [
			'label' => __('Total Qty of Tiles'),
			'value' => $tilesQty
		];
		
		$boxText = $this->calculatorHelper->getBoxText($boxes);
		$additionalOptions[] = [
			'label' => __('Quantity in %1', $boxText),
			'value' => $this->calculatorHelper->formatBoxQuantity($boxes)
		];
		
		$additionalOptions[] = [
			'label' => __('Area Covered'),
			'value' => number_format($m2, 2) . ' m²'
		];
		
		// Get existing options
		$existingOptions = [];
		if ($productInfo->getExtensionAttributes() && 
			$productInfo->getExtensionAttributes()->getCustomOptions()) {
			$existingOptions = $productInfo->getExtensionAttributes()->getCustomOptions();
		}
		
		// Add our options
		$allOptions = array_merge($existingOptions, $additionalOptions);
		
		// Set back to quote item
		if ($productInfo->getExtensionAttributes()) {
			$productInfo->getExtensionAttributes()->setCustomOptions($allOptions);
		}
		
		return $cartItem;
	}
}