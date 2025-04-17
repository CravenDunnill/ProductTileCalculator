<?php
/**
 * CravenDunnill ProductTileCalculator Extension
 *
 * @category  CravenDunnill
 * @package   CravenDunnill_ProductTileCalculator
 */

namespace CravenDunnill\ProductTileCalculator\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Model\Quote\Item;
use CravenDunnill\ProductTileCalculator\Helper\Calculator;

class TileDetails implements ArgumentInterface
{
	/**
	 * @var Calculator
	 */
	private $calculatorHelper;
	
	/**
	 * @var Item
	 */
	private $quoteItem;
	
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
	 * Set the quote item
	 *
	 * @param Item $item
	 * @return $this
	 */
	public function setQuoteItem(Item $item)
	{
		$this->quoteItem = $item;
		return $this;
	}
	
	/**
	 * Get the quote item
	 *
	 * @return Item
	 */
	public function getQuoteItem()
	{
		return $this->quoteItem;
	}
	
	/**
	 * Get the calculator helper
	 *
	 * @return Calculator
	 */
	public function getCalculatorHelper()
	{
		return $this->calculatorHelper;
	}
}