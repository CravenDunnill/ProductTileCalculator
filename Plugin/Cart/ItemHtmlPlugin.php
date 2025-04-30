<?php
/**
 * CravenDunnill ProductTileCalculator Extension
 *
 * @category  CravenDunnill
 * @package   CravenDunnill_ProductTileCalculator
 */

namespace CravenDunnill\ProductTileCalculator\Plugin\Cart;

use Magento\Checkout\Block\Cart\Item\Renderer;
use CravenDunnill\ProductTileCalculator\Helper\Calculator;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Eav\Api\AttributeSetRepositoryInterface;

class ItemHtmlPlugin
{
	/**
	 * @var Calculator
	 */
	private $calculatorHelper;
	
	/**
	 * @var LayoutInterface
	 */
	private $layout;
	
	/**
	 * @var AttributeSetRepositoryInterface
	 */
	private $attributeSetRepository;
	
	/**
	 * Constructor
	 *
	 * @param Calculator $calculatorHelper
	 * @param LayoutInterface $layout
	 * @param AttributeSetRepositoryInterface $attributeSetRepository
	 */
	public function __construct(
		Calculator $calculatorHelper,
		LayoutInterface $layout,
		AttributeSetRepositoryInterface $attributeSetRepository
	) {
		$this->calculatorHelper = $calculatorHelper;
		$this->layout = $layout;
		$this->attributeSetRepository = $attributeSetRepository;
	}
	
	/**
	 * After get item html - add tile details
	 *
	 * @param Renderer $subject
	 * @param string $result
	 * @param Item $item
	 * @return string
	 */
	public function afterGetItemHtml(Renderer $subject, $result, $item)
	{
		$product = $item->getProduct();
		
		if (!$this->calculatorHelper->canUseCalculator($product)) {
			return $result;
		}
		
		try {
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
			
			// Get box quantity
			$tilesPerBox = $this->calculatorHelper->getBoxQuantity($product);
			
			// Get total quantity
			$totalTiles = $item->getQty();
			
			// Calculate boxes
			$boxes = $this->calculatorHelper->calculateBoxesFromTiles($totalTiles, $product);
			
			// Calculate m²
			$m2 = $this->calculatorHelper->calculateM2FromBoxes($boxes, $product);
			
			// Create tile details HTML
			$tileDetailsHtml = '<div class="tile-details-cart">';
			
			// Add attribute set
			$tileDetailsHtml .= '<div class="tile-details-item"><span class="tile-details-label">Product Type:</span>';
			$tileDetailsHtml .= '<span class="tile-details-value">' . ($attributeSetName) . '</span></div>';
			
			$tileDetailsHtml .= '<div class="tile-details-item"><span class="tile-details-label">Colour:</span>';
			$tileDetailsHtml .= '<span class="tile-details-value">' . ($colorValue ?: 'n/a') . '</span></div>';
			
			$tileDetailsHtml .= '<div class="tile-details-item"><span class="tile-details-label">Size:</span>';
			$tileDetailsHtml .= '<span class="tile-details-value">' . ($sizeValue ?: 'n/a') . '</span></div>';
			
			$tileDetailsHtml .= '<div class="tile-details-item"><span class="tile-details-label">Tiles per Box:</span>';
			$tileDetailsHtml .= '<span class="tile-details-value">' . $tilesPerBox . '</span></div>';
			
			$tileDetailsHtml .= '<div class="tile-details-item"><span class="tile-details-label">Total Qty of Tiles:</span>';
			$tileDetailsHtml .= '<span class="tile-details-value">' . $totalTiles . '</span></div>';
			
			$tileDetailsHtml .= '<div class="tile-details-item"><span class="tile-details-label">Box Quantity:</span>';
			$tileDetailsHtml .= '<span class="tile-details-value">' . $this->calculatorHelper->formatBoxQuantity($boxes) . '</span></div>';
			
			$tileDetailsHtml .= '<div class="tile-details-item"><span class="tile-details-label">Area Coverage:</span>';
			$tileDetailsHtml .= '<span class="tile-details-value">' . number_format($m2, 2) . ' m²</span></div>';
			
			$tileDetailsHtml .= '</div>';
			
			// Append tile details to the result
			$result .= $tileDetailsHtml;
		} catch (\Exception $e) {
			// Silently fail - do nothing
		}
		
		return $result;
	}
}