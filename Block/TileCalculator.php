<?php
/**
 * CravenDunnill ProductTileCalculator Extension
 *
 * @category  CravenDunnill
 * @package   CravenDunnill_ProductTileCalculator
 */

namespace CravenDunnill\ProductTileCalculator\Block;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\Form\FormKey;
use Magento\Store\Model\StoreManagerInterface;

class TileCalculator extends \Magento\Catalog\Block\Product\View
{
	/**
	 * @var Registry
	 */
	protected $_registry;

	/**
	 * @var ProductRepositoryInterface
	 */
	protected $productRepository;
	
	/**
	 * @var \Magento\Framework\Locale\FormatInterface
	 */
	protected $localeFormat;
	
	/**
	 * @var \Magento\Framework\Pricing\PriceCurrencyInterface
	 */
	protected $priceCurrency;
	
	/**
	 * @var FormKey
	 */
	protected $formKey;

	/**
	 * @var StoreManagerInterface
	 */
	protected $_storeManager;

	/**
	 * Constructor
	 *
	 * @param Context $context
	 * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
	 * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
	 * @param \Magento\Framework\Stdlib\StringUtils $string
	 * @param \Magento\Catalog\Helper\Product $productHelper
	 * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
	 * @param \Magento\Framework\Locale\FormatInterface $localeFormat
	 * @param \Magento\Customer\Model\Session $customerSession
	 * @param ProductRepositoryInterface $productRepository
	 * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
	 * @param FormKey $formKey
	 * @param StoreManagerInterface $storeManager
	 * @param array $data
	 */
	public function __construct(
		Context $context,
		\Magento\Framework\Url\EncoderInterface $urlEncoder,
		\Magento\Framework\Json\EncoderInterface $jsonEncoder,
		\Magento\Framework\Stdlib\StringUtils $string,
		\Magento\Catalog\Helper\Product $productHelper,
		\Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
		\Magento\Framework\Locale\FormatInterface $localeFormat,
		\Magento\Customer\Model\Session $customerSession,
		ProductRepositoryInterface $productRepository,
		\Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
		FormKey $formKey,
		StoreManagerInterface $storeManager,
		array $data = []
	) {
		$this->_registry = $context->getRegistry();
		$this->productRepository = $productRepository;
		$this->localeFormat = $localeFormat;
		$this->priceCurrency = $priceCurrency;
		$this->formKey = $formKey;
		$this->_storeManager = $storeManager;
		
		parent::__construct(
			$context,
			$urlEncoder,
			$jsonEncoder,
			$string,
			$productHelper,
			$productTypeConfig,
			$localeFormat,
			$customerSession,
			$productRepository,
			$priceCurrency,
			$data
		);
	}

	/**
	 * Get current product
	 *
	 * @return \Magento\Catalog\Model\Product
	 */
	public function getProduct()
	{
		return $this->_registry->registry('current_product');
	}

	/**
	 * Check if product has tile calculator attributes
	 *
	 * @return bool
	 */
	public function canShowCalculator()
	{
		$product = $this->getProduct();
		
		// For initial testing, always show the calculator
		return true;
		
		// Uncomment for production:
		// return $product 
		//     && $product->getData('box_quantity') 
		//     && $product->getData('tile_per_m2') 
		//     && $product->getData('price_m2');
	}
	
	/**
	 * Get box quantity attribute value
	 *
	 * @return float
	 */
	public function getBoxQuantity()
	{
		return (float)$this->getProduct()->getData('box_quantity');
	}
	
	/**
	 * Get tiles per m2 attribute value
	 *
	 * @return float
	 */
	public function getTilePerM2()
	{
		return (float)$this->getProduct()->getData('tile_per_m2');
	}
	
	/**
	 * Get price per m2 attribute value
	 *
	 * @return float
	 */
	public function getPricePerM2()
	{
		return (float)$this->getProduct()->getData('price_m2');
	}
	
	/**
	 * Get price per tile
	 *
	 * @return float
	 */
	public function getPricePerTile()
	{
		return (float)$this->getProduct()->getPrice();
	}
	
	/**
	 * Get total price per box
	 *
	 * @return float
	 */
	public function getPricePerBox()
	{
		return $this->getPricePerTile() * $this->getBoxQuantity();
	}
	
	/**
	 * Calculate m2 from boxes
	 *
	 * @param float $boxes
	 * @return float
	 */
	public function calculateM2FromBoxes($boxes)
	{
		$tilesInBoxes = $boxes * $this->getBoxQuantity();
		return $tilesInBoxes / $this->getTilePerM2();
	}
	
	/**
	 * Calculate boxes from m2
	 *
	 * @param float $squareMeters
	 * @return int
	 */
	public function calculateBoxesFromM2($squareMeters)
	{
		$tilesNeeded = $squareMeters * $this->getTilePerM2();
		return ceil($tilesNeeded / $this->getBoxQuantity());
	}

	/**
	 * Format price
	 *
	 * @param float $price
	 * @return string
	 */
	public function formatPrice($price)
	{
		return $this->priceCurrency->format($price);
	}
	
	/**
	 * Get price format
	 *
	 * @return array
	 */
	public function getPriceFormat()
	{
		return $this->localeFormat->getPriceFormat();
	}
	
	/**
	 * Get form key
	 *
	 * @return string
	 */
	public function getFormKey()
	{
		return $this->formKey->getFormKey();
	}
	
	/**
	 * Get base url
	 *
	 * @return string
	 */
	public function getBaseUrl()
	{
		return $this->_storeManager->getStore()->getBaseUrl();
	}
}