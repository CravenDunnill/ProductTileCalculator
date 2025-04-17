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
	 * Constructor
	 *
	 * @param Calculator $calculatorHelper
	 * @param LayoutInterface $layout
	 */
	public function __construct(
		Calculator $calculatorHelper,
		LayoutInterface $layout
	) {
		$this->calculatorHelper = $calculatorHelper;
		$this->layout = $layout;
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