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
use Magento\Catalog\Model\Product\Type;
use CravenDunnill\ProductTileCalculator\Helper\Calculator;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

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
	 * @var Calculator
	 */
	protected $calculatorHelper;
	
	/**
	 * @var AttributeSetRepositoryInterface
	 */
	protected $attributeSetRepository;

	/**
	 * @var ScopeConfigInterface
	 */
	protected $scopeConfig;

	/**
	 * Warehouse closure config path prefix
	 */
	const CONFIG_PATH_PREFIX = 'tiles_warehouse_closure/general/';

	/**
	 * Required attribute codes for the calculator
	 *
	 * @var array
	 */
	protected $requiredAttributes = [
		'box_quantity',
		'tile_per_m2',
		'price_m2'
	];

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
	 * @param Calculator $calculatorHelper
	 * @param AttributeSetRepositoryInterface $attributeSetRepository
	 * @param ScopeConfigInterface $scopeConfig
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
		Calculator $calculatorHelper,
		AttributeSetRepositoryInterface $attributeSetRepository,
		ScopeConfigInterface $scopeConfig,
		array $data = []
	) {
		$this->_registry = $context->getRegistry();
		$this->productRepository = $productRepository;
		$this->localeFormat = $localeFormat;
		$this->priceCurrency = $priceCurrency;
		$this->formKey = $formKey;
		$this->_storeManager = $storeManager;
		$this->calculatorHelper = $calculatorHelper;
		$this->attributeSetRepository = $attributeSetRepository;
		$this->scopeConfig = $scopeConfig;

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
	 * Check if the product is a simple type
	 * 
	 * @return bool
	 */
	public function isSimpleProduct()
	{
		$product = $this->getProduct();
		return $product && $product->getTypeId() === Type::TYPE_SIMPLE;
	}
	
	/**
	 * Get the attribute set name for the current product
	 * 
	 * @return string|null
	 */
	public function getProductAttributeSetName()
	{
		$product = $this->getProduct();
		if (!$product) {
			return null;
		}
		
		try {
			$attributeSetId = $product->getAttributeSetId();
			if ($attributeSetId) {
				$attributeSet = $this->attributeSetRepository->get($attributeSetId);
				return $attributeSet->getAttributeSetName();
			}
		} catch (\Exception $e) {
			// Log error if needed
			return null;
		}
		
		return null;
	}
	
	/**
	 * Check if the product is in the "Tiles" attribute set
	 * 
	 * @return bool
	 */
	public function isInTilesAttributeSet()
	{
		return $this->getProductAttributeSetName() === 'Tiles';
	}

	/**
	 * Get missing required attributes for the calculator
	 * 
	 * @return array
	 */
	public function getMissingAttributes()
	{
		$product = $this->getProduct();
		$missingAttributes = [];
		
		if (!$product) {
			return $this->requiredAttributes;
		}
		
		foreach ($this->requiredAttributes as $attribute) {
			$value = $product->getData($attribute);
			if (empty($value) && $value !== '0') {
				$missingAttributes[] = $attribute;
			}
		}
		
		return $missingAttributes;
	}

	/**
	 * Get attribute labels for display in error messages
	 * 
	 * @return array
	 */
	public function getAttributeLabels()
	{
		return [
			'box_quantity' => __('Tiles per Box'),
			'tile_per_m2' => __('Tiles per m²'),
			'price_m2' => __('Price per m²')
		];
	}

	/**
	 * Check if product has tile calculator attributes
	 *
	 * @return bool
	 */
	public function canShowCalculator()
	{
		// Only show calculator for Simple product types
		if (!$this->isSimpleProduct()) {
			return false;
		}
		
		// Only show calculator for products in "Tiles" attribute set
		if (!$this->isInTilesAttributeSet()) {
			return false;
		}
		
		// Check if any required attributes are missing
		return empty($this->getMissingAttributes());
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
	 * Get price per m2 attribute value with VAT
	 *
	 * @return float
	 */
	public function getPricePerM2()
	{
		return $this->calculatorHelper->getPricePerM2($this->getProduct());
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
	 * Get total price per box with VAT
	 *
	 * @return float
	 */
	public function getPricePerBox()
	{
		return $this->calculatorHelper->getPricePerBox($this->getProduct());
	}
	
	/**
	 * Get special price per m2 attribute value with VAT
	 *
	 * @return float|null
	 */
	public function getSpecialPricePerM2()
	{
		return $this->calculatorHelper->getSpecialPricePerM2($this->getProduct());
	}
	
	/**
	 * Get special price per box with VAT
	 *
	 * @return float|null
	 */
	public function getSpecialPricePerBox()
	{
		return $this->calculatorHelper->getSpecialPricePerBox($this->getProduct());
	}
	
	/**
	 * Check if product has special pricing
	 *
	 * @return bool
	 */
	public function hasSpecialPrice()
	{
		return $this->calculatorHelper->hasSpecialPrice($this->getProduct());
	}
	
	/**
	 * Get effective price per m2 (special or regular)
	 *
	 * @return float
	 */
	public function getEffectivePricePerM2()
	{
		return $this->calculatorHelper->getEffectivePricePerM2($this->getProduct());
	}
	
	/**
	 * Get effective price per box (special or regular)
	 *
	 * @return float
	 */
	public function getEffectivePricePerBox()
	{
		return $this->calculatorHelper->getEffectivePricePerBox($this->getProduct());
	}
	
	/**
	 * Calculate m2 from boxes
	 *
	 * @param float $boxes
	 * @return float
	 */
	public function calculateM2FromBoxes($boxes)
	{
		return $this->calculatorHelper->calculateM2FromBoxes($boxes, $this->getProduct());
	}
	
	/**
	 * Calculate boxes from m2
	 *
	 * @param float $squareMeters
	 * @return int
	 */
	public function calculateBoxesFromM2($squareMeters)
	{
		return $this->calculatorHelper->calculateBoxesFromM2($squareMeters, $this->getProduct());
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

	/**
	 * Check if warehouse closure notice is enabled
	 *
	 * @return bool
	 */
	public function isWarehouseClosureEnabled(): bool
	{
		return (bool)$this->scopeConfig->getValue(
			self::CONFIG_PATH_PREFIX . 'enabled',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * Get warehouse closure message text
	 *
	 * @return string
	 */
	public function getWarehouseClosureMessage(): string
	{
		return (string)$this->scopeConfig->getValue(
			self::CONFIG_PATH_PREFIX . 'message_text',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * Check if message should be bold
	 *
	 * @return bool
	 */
	public function isWarehouseClosureMessageBold(): bool
	{
		return (bool)$this->scopeConfig->getValue(
			self::CONFIG_PATH_PREFIX . 'message_bold',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * Get warehouse closure link URL
	 *
	 * @return string
	 */
	public function getWarehouseClosureLinkUrl(): string
	{
		return (string)$this->scopeConfig->getValue(
			self::CONFIG_PATH_PREFIX . 'link_url',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * Get warehouse closure link text
	 *
	 * @return string
	 */
	public function getWarehouseClosureLinkText(): string
	{
		return (string)$this->scopeConfig->getValue(
			self::CONFIG_PATH_PREFIX . 'link_text',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * Get warehouse closure background color
	 *
	 * @return string
	 */
	public function getWarehouseClosureBackgroundColor(): string
	{
		$color = $this->scopeConfig->getValue(
			self::CONFIG_PATH_PREFIX . 'background_color',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		return $color ?: '#ffcc00';
	}

	/**
	 * Get warehouse closure text color
	 *
	 * @return string
	 */
	public function getWarehouseClosureTextColor(): string
	{
		$color = $this->scopeConfig->getValue(
			self::CONFIG_PATH_PREFIX . 'text_color',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		return $color ?: '#000000';
	}

	/**
	 * Get warehouse closure start datetime
	 *
	 * @return string
	 */
	public function getWarehouseClosureStartDatetime(): string
	{
		return (string)$this->scopeConfig->getValue(
			self::CONFIG_PATH_PREFIX . 'start_datetime',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * Get warehouse closure end datetime
	 *
	 * @return string
	 */
	public function getWarehouseClosureEndDatetime(): string
	{
		return (string)$this->scopeConfig->getValue(
			self::CONFIG_PATH_PREFIX . 'end_datetime',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * Get all warehouse closure config as JSON for JavaScript
	 *
	 * @return string
	 */
	public function getWarehouseClosureConfigJson(): string
	{
		$config = [
			'enabled' => $this->isWarehouseClosureEnabled(),
			'message' => $this->getWarehouseClosureMessage(),
			'bold' => $this->isWarehouseClosureMessageBold(),
			'linkUrl' => $this->getWarehouseClosureLinkUrl(),
			'linkText' => $this->getWarehouseClosureLinkText(),
			'backgroundColor' => $this->getWarehouseClosureBackgroundColor(),
			'textColor' => $this->getWarehouseClosureTextColor(),
			'startDatetime' => $this->getWarehouseClosureStartDatetime(),
			'endDatetime' => $this->getWarehouseClosureEndDatetime()
		];
		return json_encode($config);
	}
}