<?php
/**
 * CravenDunnill ProductTileCalculator Extension
 *
 * @category  CravenDunnill
 * @package   CravenDunnill_ProductTileCalculator
 */
namespace CravenDunnill\ProductTileCalculator\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Eav\Model\AttributeSetRepository;

class ProductAttributeViewModel implements ArgumentInterface
{
	/**
	 * @var ProductRepositoryInterface
	 */
	private $productRepository;
	
	/**
	 * @var AttributeSetRepositoryInterface
	 */
	private $attributeSetRepository;
	
	/**
	 * @var LoggerInterface
	 */
	private $logger;
	
	/**
	 * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
	 */
	private $attributeSetCollectionFactory;

	/**
	 * Constructor
	 *
	 * @param ProductRepositoryInterface $productRepository
	 * @param AttributeSetRepositoryInterface $attributeSetRepository
	 * @param LoggerInterface $logger
	 * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollectionFactory
	 */
	public function __construct(
		ProductRepositoryInterface $productRepository,
		AttributeSetRepositoryInterface $attributeSetRepository,
		LoggerInterface $logger,
		\Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollectionFactory
	) {
		$this->productRepository = $productRepository;
		$this->attributeSetRepository = $attributeSetRepository;
		$this->logger = $logger;
		$this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
	}
	
	/**
	 * Get product attribute set name
	 *
	 * @param int $productId
	 * @return string
	 */
	public function getAttributeSetName($productId)
	{
		try {
			// Try loading the product first
			$product = $this->productRepository->getById($productId);
			$attributeSetId = $product->getAttributeSetId();
			
			$this->logger->debug('Product info - ID: ' . $productId . ', Attr Set ID: ' . $attributeSetId);
			
			if ($attributeSetId) {
				// Method 1: Try via repository
				try {
					$attributeSet = $this->attributeSetRepository->get($attributeSetId);
					$name = $attributeSet->getAttributeSetName();
					$this->logger->debug('Attribute set name via repository: ' . $name);
					
					if (!empty($name) && $name !== 'Default') {
						return $name;
					}
				} catch (\Exception $e) {
					$this->logger->debug('Repository error: ' . $e->getMessage());
				}
				
				// Method 2: Try via collection
				try {
					$collection = $this->attributeSetCollectionFactory->create();
					$collection->addFieldToFilter('attribute_set_id', $attributeSetId);
					$item = $collection->getFirstItem();
					
					if ($item && $item->getId()) {
						$name = $item->getAttributeSetName();
						$this->logger->debug('Attribute set name via collection: ' . $name);
						return $name ?: 'Product Set #' . $attributeSetId;
					}
				} catch (\Exception $e) {
					$this->logger->debug('Collection error: ' . $e->getMessage());
				}
				
				// Method 3: Return ID if nothing else worked
				return 'Product Set #' . $attributeSetId;
			}
		} catch (NoSuchEntityException $e) {
			$this->logger->error('Could not find product: ' . $e->getMessage());
		} catch (\Exception $e) {
			$this->logger->error('Error retrieving attribute set: ' . $e->getMessage());
		}
		
		return 'Default Product';
	}
	
	/**
	 * Get all available attribute sets (for debugging)
	 *
	 * @return array
	 */
	public function getAllAttributeSets()
	{
		try {
			$collection = $this->attributeSetCollectionFactory->create();
			$collection->addFieldToSelect(['attribute_set_id', 'attribute_set_name', 'entity_type_id']);
			
			$result = [];
			foreach ($collection as $set) {
				$result[] = [
					'id' => $set->getAttributeSetId(),
					'name' => $set->getAttributeSetName(),
					'entity_type_id' => $set->getEntityTypeId()
				];
			}
			
			return $result;
		} catch (\Exception $e) {
			$this->logger->error('Error getting all attribute sets: ' . $e->getMessage());
			return [];
		}
	}
	
	/**
	 * Get product attribute set ID
	 *
	 * @param int $productId
	 * @return int|null
	 */
	public function getAttributeSetId($productId)
	{
		try {
			$product = $this->productRepository->getById($productId);
			return $product->getAttributeSetId();
		} catch (\Exception $e) {
			$this->logger->error('Error retrieving attribute set ID: ' . $e->getMessage());
		}
		
		return null;
	}
	
	/**
	 * Get attribute text for a product
	 *
	 * @param int $productId
	 * @param string $attributeCode
	 * @return string
	 */
	public function getAttributeText($productId, $attributeCode)
	{
		try {
			$product = $this->productRepository->getById($productId);
			$value = $product->getAttributeText($attributeCode);
			
			if (empty($value)) {
				$value = $product->getData($attributeCode);
			}
			
			return $value;
		} catch (\Exception $e) {
			$this->logger->error('Error retrieving attribute text: ' . $e->getMessage());
		}
		
		return '';
	}
	
	/**
	 * Get attribute direct value
	 *
	 * @param int $productId
	 * @param string $attributeCode
	 * @return mixed
	 */
	public function getAttributeValue($productId, $attributeCode)
	{
		try {
			$product = $this->productRepository->getById($productId);
			return $product->getData($attributeCode);
		} catch (\Exception $e) {
			$this->logger->error('Error retrieving attribute value: ' . $e->getMessage());
		}
		
		return null;
	}
}