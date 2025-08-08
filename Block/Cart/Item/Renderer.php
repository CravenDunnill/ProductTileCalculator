<?php
/**
 * CravenDunnill ProductTileCalculator Extension
 *
 * @category  CravenDunnill
 * @package   CravenDunnill_ProductTileCalculator
 */

namespace CravenDunnill\ProductTileCalculator\Block\Cart\Item;

use Magento\Checkout\Block\Cart\Item\Renderer as MagentoRenderer;
use CravenDunnill\ProductTileCalculator\Helper\Calculator;

class Renderer extends MagentoRenderer
{
	/**
	 * @var Calculator
	 */
	protected $calculatorHelper;
	
	/**
	 * Constructor with added calculator helper
	 *
	 * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param \Magento\Catalog\Helper\Product\Configuration $productConfig
	 * @param \Magento\Checkout\Model\Session $checkoutSession
	 * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
	 * @param \Magento\Framework\Url\Helper\Data $urlHelper
	 * @param \Magento\Framework\Message\ManagerInterface $messageManager
	 * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
	 * @param \Magento\Framework\Module\Manager $moduleManager
	 * @param \Magento\Framework\View\Element\Message\InterpretationStrategyInterface $messageInterpretationStrategy
	 * @param Calculator $calculatorHelper
	 * @param array $data
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Catalog\Helper\Product\Configuration $productConfig,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
		\Magento\Framework\Url\Helper\Data $urlHelper,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
		\Magento\Framework\Module\Manager $moduleManager,
		\Magento\Framework\View\Element\Message\InterpretationStrategyInterface $messageInterpretationStrategy,
		Calculator $calculatorHelper,
		array $data = []
	) {
		$this->calculatorHelper = $calculatorHelper;
		parent::__construct(
			$context,
			$productConfig,
			$checkoutSession,
			$imageBuilder,
			$urlHelper,
			$messageManager,
			$priceCurrency,
			$moduleManager,
			$messageInterpretationStrategy,
			$data
		);
	}
	
	/**
	 * Check if item product can use tile calculator
	 *
	 * @return bool
	 */
	public function canUseCalculator()
	{
		$item = $this->getItem();
		$product = $item->getProduct();
		
		return $this->calculatorHelper->canUseCalculator($product);
	}
	
	/**
	 * Get box quantity for the current item
	 *
	 * @return int
	 */
	public function getBoxes()
	{
		$item = $this->getItem();
		$product = $item->getProduct();
		$tilesQty = $item->getQty();
		
		return $this->calculatorHelper->calculateBoxesFromTiles($tilesQty, $product);
	}
	
	/**
	 * Get box quantity formatted string
	 *
	 * @return string
	 */
	public function getFormattedBoxQuantity()
	{
		$boxes = $this->getBoxes();
		return $this->calculatorHelper->formatBoxQuantity($boxes);
	}
	
	/**
	 * Get area in m² covered by this item
	 *
	 * @return float
	 */
	public function getAreaCovered()
	{
		$item = $this->getItem();
		$product = $item->getProduct();
		$boxes = $this->getBoxes();
		
		return $this->calculatorHelper->calculateM2FromBoxes($boxes, $product);
	}
	
	/**
	 * Get area formatted string
	 *
	 * @return string
	 */
	public function getFormattedAreaCovered()
	{
		return number_format($this->getAreaCovered(), 3) . ' m²';
	}
	
	/**
	 * Get box quantity text (singular or plural)
	 *
	 * @return string
	 */
	public function getBoxText()
	{
		$boxes = $this->getBoxes();
		return $this->calculatorHelper->getBoxText($boxes);
	}
	
	/**
	 * Get tiles per box for display
	 *
	 * @return int
	 */
	public function getTilesPerBox()
	{
		$item = $this->getItem();
		$product = $item->getProduct();
		
		return (int)$this->calculatorHelper->getBoxQuantity($product);
	}
	
	/**
	 * Get special price per m2 for the current item
	 *
	 * @return float|null
	 */
	public function getSpecialPricePerM2()
	{
		$item = $this->getItem();
		$product = $item->getProduct();
		
		return $this->calculatorHelper->getSpecialPricePerM2($product);
	}
	
	/**
	 * Get regular price per m2 for the current item
	 *
	 * @return float
	 */
	public function getRegularPricePerM2()
	{
		$item = $this->getItem();
		$product = $item->getProduct();
		
		return $this->calculatorHelper->getPricePerM2($product);
	}
	
	/**
	 * Check if item has special pricing
	 *
	 * @return bool
	 */
	public function hasSpecialPricing()
	{
		$item = $this->getItem();
		$product = $item->getProduct();
		
		return $this->calculatorHelper->hasSpecialPrice($product);
	}
	
	/**
	 * Get formatted price per m2 display for cart
	 *
	 * @return string
	 */
	public function getFormattedPricePerM2Display()
	{
		if ($this->hasSpecialPricing() && $this->getSpecialPricePerM2() !== null) {
			$regularPrice = $this->formatPrice($this->getRegularPricePerM2());
			$specialPrice = $this->formatPrice($this->getSpecialPricePerM2());
			
			return '<span class="old-price" style="text-decoration: line-through; color: #666;">' . 
				   $regularPrice . '</span><br>' .
				   '<span class="special-price" style="color: #ff0000; font-weight: 600;">NOW ' . 
				   $specialPrice . ' per m²</span>';
		}
		
		return $this->formatPrice($this->getRegularPricePerM2()) . ' per m²';
	}
	
	/**
	 * Format price using price currency
	 *
	 * @param float $price
	 * @return string
	 */
	private function formatPrice($price)
	{
		return $this->_priceCurrency->format($price, false);
	}
}