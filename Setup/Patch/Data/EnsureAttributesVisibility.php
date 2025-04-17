<?php
/**
 * CravenDunnill ProductTileCalculator Extension
 *
 * @category  CravenDunnill
 * @package   CravenDunnill_ProductTileCalculator
 */

namespace CravenDunnill\ProductTileCalculator\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class EnsureAttributesVisibility implements DataPatchInterface
{
	/**
	 * @var ModuleDataSetupInterface
	 */
	private $moduleDataSetup;

	/**
	 * @var EavSetupFactory
	 */
	private $eavSetupFactory;

	/**
	 * Constructor
	 *
	 * @param ModuleDataSetupInterface $moduleDataSetup
	 * @param EavSetupFactory $eavSetupFactory
	 */
	public function __construct(
		ModuleDataSetupInterface $moduleDataSetup,
		EavSetupFactory $eavSetupFactory
	) {
		$this->moduleDataSetup = $moduleDataSetup;
		$this->eavSetupFactory = $eavSetupFactory;
	}

	/**
	 * @inheritdoc
	 */
	public function apply()
	{
		$this->moduleDataSetup->getConnection()->startSetup();
		$eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
		
		// Attributes to ensure exist
		$attributes = [
			'box_quantity' => [
				'type' => 'decimal',
				'label' => 'Tiles per Box',
				'input' => 'text',
				'required' => false,
				'user_defined' => true,
				'searchable' => false,
				'filterable' => false,
				'comparable' => false,
				'visible_on_front' => false,
				'used_in_product_listing' => true,
				'unique' => false,
				'apply_to' => 'simple',
				'group' => 'Tile Calculator'
			],
			'tile_per_m2' => [
				'type' => 'decimal',
				'label' => 'Tiles per m²',
				'input' => 'text',
				'required' => false,
				'user_defined' => true,
				'searchable' => false,
				'filterable' => false,
				'comparable' => false,
				'visible_on_front' => false,
				'used_in_product_listing' => true,
				'unique' => false,
				'apply_to' => 'simple',
				'group' => 'Tile Calculator'
			],
			'price_m2' => [
				'type' => 'decimal',
				'label' => 'Price per m²',
				'input' => 'text',
				'required' => false,
				'user_defined' => true,
				'searchable' => false,
				'filterable' => false,
				'comparable' => false,
				'visible_on_front' => false,
				'used_in_product_listing' => true,
				'unique' => false,
				'apply_to' => 'simple',
				'group' => 'Tile Calculator'
			]
		];
		
		foreach ($attributes as $attributeCode => $attributeData) {
			$attribute = $eavSetup->getAttribute(Product::ENTITY, $attributeCode);
			
			// If attribute doesn't exist, create it
			if (!$attribute) {
				$eavSetup->addAttribute(
					Product::ENTITY,
					$attributeCode,
					$attributeData
				);
			} else {
				// If attribute exists, just update its properties
				$eavSetup->updateAttribute(
					Product::ENTITY,
					$attributeCode,
					$attributeData
				);
			}
		}
		
		// Create or get attribute set
		$attributeSetId = $eavSetup->getAttributeSetId(Product::ENTITY, 'Default');
		$attributeGroupId = $eavSetup->getAttributeGroupId(Product::ENTITY, $attributeSetId, 'Tile Calculator');
		
		// If group doesn't exist, create it
		if (!$attributeGroupId) {
			$eavSetup->addAttributeGroup(
				Product::ENTITY,
				$attributeSetId,
				'Tile Calculator',
				100
			);
			$attributeGroupId = $eavSetup->getAttributeGroupId(Product::ENTITY, $attributeSetId, 'Tile Calculator');
		}
		
		// Add attributes to group
		foreach (array_keys($attributes) as $attributeCode) {
			$attributeId = $eavSetup->getAttributeId(Product::ENTITY, $attributeCode);
			if ($attributeId) {
				$eavSetup->addAttributeToGroup(
					Product::ENTITY,
					$attributeSetId,
					$attributeGroupId,
					$attributeId,
					10
				);
			}
		}
		
		$this->moduleDataSetup->getConnection()->endSetup();
		
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public static function getDependencies()
	{
		return [];
	}

	/**
	 * @inheritdoc
	 */
	public function getAliases()
	{
		return [];
	}
}