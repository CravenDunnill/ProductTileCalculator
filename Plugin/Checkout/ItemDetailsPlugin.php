<?php
/**
 * CravenDunnill ProductTileCalculator Extension
 *
 * @category  CravenDunnill
 * @package   CravenDunnill_ProductTileCalculator
 */

namespace CravenDunnill\ProductTileCalculator\Plugin\Checkout;

use Magento\Checkout\Block\Cart\Item\Renderer;
use CravenDunnill\ProductTileCalculator\Helper\Calculator;
use Magento\Eav\Api\AttributeSetRepositoryInterface;

class ItemDetailsPlugin
{
	/**
	 * @var Calculator
	 */
	protected $calculatorHelper;

	/**
	 * @var AttributeSetRepositoryInterface
	 */
	protected $attributeSetRepository;

	/**
	 * Constructor
	 * 
	 * @param Calculator $calculatorHelper
	 * @param AttributeSetRepositoryInterface $attributeSetRepository
	 */
	public function __construct(
		Calculator $calculatorHelper,
		AttributeSetRepositoryInterface $attributeSetRepository
	) {
		$this->calculatorHelper = $calculatorHelper;
		$this->attributeSetRepository = $attributeSetRepository;
	}

	/**
	 * Add box quantity information to checkout item details
	 *
	 * @param \Magento\Checkout\CustomerData\AbstractItem $subject
	 * @param array $result
	 * @param \Magento\Quote\Model\Quote\Item $item
	 * @return array
	 */
	public function afterGetItemData(
		\Magento\Checkout\CustomerData\AbstractItem $subject,
		array $result,
		\Magento\Quote\Model\Quote\Item $item
	) {
		$product = $item->getProduct();
		
		if (!$product || !$this->calculatorHelper->canUseCalculator($product)) {
			return $result;
		}
		
		$boxQuantity = $this->calculatorHelper->getBoxQuantity($product);
		
		// Add box quantity to product data
		if (!isset($result['product'])) {
			$result['product'] = [];
		}
		
		// Add all relevant product attributes to the result
		$result['product']['box_quantity'] = $boxQuantity;
		
		// Get attribute set name
		$attributeSetName = 'Default';
		try {
			$attributeSetId = $product->getAttributeSetId();
			if ($attributeSetId) {
				$attributeSet = $this->attributeSetRepository->get($attributeSetId);
				$attributeSetName = $attributeSet->getAttributeSetName();
			}
		} catch (\Exception $e) {
			// If there's an error, use default value
			$attributeSetName = 'Default';
		}
		$result['product']['attribute_set_name'] = $attributeSetName;
		
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
		
		// Add these to the product data for use in the template
		$result['product']['tile_colour_name'] = $colorValue ?: 'n/a';
		$result['product']['tile_size'] = $sizeValue ?: 'n/a';
		
		// Add box information to options display
		if (!isset($result['options'])) {
			$result['options'] = [];
		}
		
		$tilesQty = $item->getQty();
		$boxes = $this->calculatorHelper->calculateBoxesFromTiles($tilesQty, $product);
		$m2 = $this->calculatorHelper->calculateM2FromBoxes($boxes, $product);
		
		// Add all the details
		$result['options'][] = [
			'label' => __('Product Type'),
			'value' => $attributeSetName
		];
		
		$result['options'][] = [
			'label' => __('Colour'),
			'value' => $colorValue ?: __('n/a')
		];
		
		$result['options'][] = [
			'label' => __('Size'),
			'value' => $sizeValue ?: __('n/a')
		];
		
		$result['options'][] = [
			'label' => __('Tiles per Box'),
			'value' => $boxQuantity
		];
		
		$result['options'][] = [
			'label' => __('Total Qty of Tiles'),
			'value' => $tilesQty
		];
		
		$result['options'][] = [
			'label' => __('Quantity in %1', $this->calculatorHelper->getBoxText($boxes)),
			'value' => $this->calculatorHelper->formatBoxQuantity($boxes)
		];
		
		$result['options'][] = [
			'label' => __('Area Covered'),
			'value' => number_format($m2, 3) . ' m²'
		];
		
		return $result;
	}
}