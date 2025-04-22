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
use Magento\Framework\Pricing\PriceCurrencyInterface;

class ItemPricePlugin
{
	/**
	 * @var Calculator
	 */
	private $calculatorHelper;
	
	/**
	 * @var PriceCurrencyInterface
	 */
	private $priceCurrency;
	
	/**
	 * Constructor
	 *
	 * @param Calculator $calculatorHelper
	 * @param PriceCurrencyInterface $priceCurrency
	 */
	public function __construct(
		Calculator $calculatorHelper,
		PriceCurrencyInterface $priceCurrency
	) {
		$this->calculatorHelper = $calculatorHelper;
		$this->priceCurrency = $priceCurrency;
	}
	
	/**
	 * After get unit price HTML - add box price info
	 *
	 * @param Renderer $subject
	 * @param string $result
	 * @param \Magento\Quote\Model\Quote\Item $item
	 * @return string
	 */
	public function afterGetUnitPriceHtml(Renderer $subject, $result, $item)
	{
		$product = $item->getProduct();
		
		if (!$this->calculatorHelper->canUseCalculator($product)) {
			return $result;
		}
		
		try {
			// Get box quantity
			$tilesPerBox = $this->calculatorHelper->getBoxQuantity($product);
			if (!$tilesPerBox) {
				return $result;
			}
			
			// Get price per tile
			$pricePerTile = $item->getPrice();
			
			// Calculate price per box
			$pricePerBox = $pricePerTile * $tilesPerBox;
			
			// Format the price
			$formattedPrice = $this->priceCurrency->format($pricePerBox);
			
			// Create box price HTML
			$boxPriceHtml = '<div class="box-price-info">' .
				'<span class="box-price-label">' . __('Price per box:') . '</span> ' .
				'<span class="box-price-value" data-box-price="' . $pricePerBox . '" data-tiles-per-box="' . $tilesPerBox . '">' . 
				$formattedPrice . 
				'</span>' .
				'</div>';
			
			// Add box price information after the standard price
			$result .= $boxPriceHtml;
		} catch (\Exception $e) {
			// Silently fail - return original price
		}
		
		return $result;
	}
}