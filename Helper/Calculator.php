<?php
/**
 * CravenDunnill ProductTileCalculator Extension
 *
 * @category  CravenDunnill
 * @package   CravenDunnill_ProductTileCalculator
 */

namespace CravenDunnill\ProductTileCalculator\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;

class Calculator extends AbstractHelper
{
	/**
	 * @var ProductRepositoryInterface
	 */
	protected $productRepository;
	
	/**
	 * @var PriceCurrencyInterface
	 */
	protected $priceCurrency;
	
	/**
	 * @var AttributeSetRepositoryInterface
	 */
	protected $attributeSetRepository;

	/**
	 * VAT multiplier (20% VAT)
	 */
	const VAT_MULTIPLIER = 1.2;

	/**
	 * Constructor
	 *
	 * @param Context $context
	 * @param ProductRepositoryInterface $productRepository
	 * @param PriceCurrencyInterface $priceCurrency
	 * @param AttributeSetRepositoryInterface $attributeSetRepository
	 */
	public function __construct(
		Context $context,
		ProductRepositoryInterface $productRepository,
		PriceCurrencyInterface $priceCurrency,
		AttributeSetRepositoryInterface $attributeSetRepository
	) {
		$this->productRepository = $productRepository;
		$this->priceCurrency = $priceCurrency;
		$this->attributeSetRepository = $attributeSetRepository;
		parent::__construct($context);
	}

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
	 * Check if product can use tile calculator
	 *
	 * @param Product|int $product
	 * @return bool
	 */
	public function canUseCalculator($product)
	{
		if (is_numeric($product)) {
			try {
				$product = $this->productRepository->getById($product);
			} catch (\Exception $e) {
				return false;
			}
		}
		
		if (!$product instanceof Product) {
			return false;
		}
		
		// Only for simple products
		if ($product->getTypeId() !== \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
			return false;
		}
		
		// Check if product is in "Tiles" attribute set
		try {
			$attributeSetId = $product->getAttributeSetId();
			if ($attributeSetId) {
				$attributeSet = $this->attributeSetRepository->get($attributeSetId);
				if ($attributeSet->getAttributeSetName() !== 'Tiles') {
					return false;
				}
			} else {
				return false;
			}
		} catch (\Exception $e) {
			return false;
		}
		
		// Check all required attributes exist
		foreach ($this->requiredAttributes as $attribute) {
			$value = $product->getData($attribute);
			if (empty($value) && $value !== '0') {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Get box quantity for a product
	 *
	 * @param Product|int $product
	 * @return float
	 */
	public function getBoxQuantity($product)
	{
		if (is_numeric($product)) {
			try {
				$product = $this->productRepository->getById($product);
			} catch (\Exception $e) {
				return 1;
			}
		}
		
		if (!$product instanceof Product) {
			return 1;
		}
		
		return (float)$product->getData('box_quantity') ?: 1;
	}
	
	/**
	 * Get tiles per m2 for a product
	 *
	 * @param Product|int $product
	 * @return float
	 */
	public function getTilePerM2($product)
	{
		if (is_numeric($product)) {
			try {
				$product = $this->productRepository->getById($product);
			} catch (\Exception $e) {
				return 1;
			}
		}
		
		if (!$product instanceof Product) {
			return 1;
		}
		
		return (float)$product->getData('tile_per_m2') ?: 1;
	}
	
	/**
	 * Get price per m2 for a product (including VAT)
	 *
	 * @param Product|int $product
	 * @return float
	 */
	public function getPricePerM2($product)
	{
		if (is_numeric($product)) {
			try {
				$product = $this->productRepository->getById($product);
			} catch (\Exception $e) {
				return 0;
			}
		}
		
		if (!$product instanceof Product) {
			return 0;
		}
		
		$exVatPrice = (float)$product->getData('price_m2') ?: 0;
		
		// Apply VAT
		return $exVatPrice * self::VAT_MULTIPLIER;
	}
	
	/**
	 * Get price per box for a product (including VAT)
	 *
	 * @param Product|int $product
	 * @return float
	 */
	public function getPricePerBox($product)
	{
		if (is_numeric($product)) {
			try {
				$product = $this->productRepository->getById($product);
			} catch (\Exception $e) {
				return 0;
			}
		}
		
		if (!$product instanceof Product) {
			return 0;
		}
		
		$boxQuantity = $this->getBoxQuantity($product);
		$pricePerTile = (float)$product->getPrice();
		
		// Calculate box price and apply VAT
		$exVatPrice = $pricePerTile * $boxQuantity;
		return $exVatPrice * self::VAT_MULTIPLIER;
	}
	
	/**
	 * Calculate boxes from tiles quantity
	 *
	 * @param int $tilesQty
	 * @param Product|int $product
	 * @return float
	 */
	public function calculateBoxesFromTiles($tilesQty, $product)
	{
		$boxQuantity = $this->getBoxQuantity($product);
		if ($boxQuantity <= 0) {
			return $tilesQty;
		}
		
		return floor($tilesQty / $boxQuantity);
	}
	
	/**
	 * Calculate m2 from boxes
	 *
	 * @param float $boxes
	 * @param Product|int $product
	 * @return float
	 */
	public function calculateM2FromBoxes($boxes, $product)
	{
		$boxQuantity = $this->getBoxQuantity($product);
		$tilePerM2 = $this->getTilePerM2($product);
		
		if ($tilePerM2 <= 0) {
			return $boxes;
		}
		
		$tilesInBoxes = $boxes * $boxQuantity;
		return $tilesInBoxes / $tilePerM2;
	}
	
	/**
	 * Calculate boxes from m2
	 *
	 * @param float $squareMeters
	 * @param Product|int $product
	 * @return int
	 */
	public function calculateBoxesFromM2($squareMeters, $product)
	{
		$tilePerM2 = $this->getTilePerM2($product);
		$boxQuantity = $this->getBoxQuantity($product);
		
		if ($boxQuantity <= 0) {
			return 1;
		}
		
		$tilesNeeded = $squareMeters * $tilePerM2;
		return ceil($tilesNeeded / $boxQuantity);
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
	 * Get box text (singular or plural)
	 *
	 * @param int $boxes
	 * @return string
	 */
	public function getBoxText($boxes)
	{
		if ((int)$boxes === 1) {
			return __('box');
		}
		return __('boxes');
	}
	
	/**
	 * Format box quantity for display
	 *
	 * @param int $boxes
	 * @return string
	 */
	public function formatBoxQuantity($boxes)
	{
		return sprintf('%d %s', (int)$boxes, $this->getBoxText($boxes));
	}
	
	/**
	 * Format area covered with 3 decimal places
	 *
	 * @param float $area
	 * @return string
	 */
	public function formatAreaCovered($area)
	{
		return number_format($area, 3) . ' m²';
	}
}