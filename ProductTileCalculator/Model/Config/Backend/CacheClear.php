<?php
/**
 * CravenDunnill ProductTileCalculator Extension
 *
 * Backend model to clear cache when warehouse closure settings are saved
 */
declare(strict_types=1);

namespace CravenDunnill\ProductTileCalculator\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class CacheClear extends Value
{
    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->cacheTypeList = $cacheTypeList;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Clear cache after saving configuration
     *
     * @return $this
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            // Clear full page cache and block HTML cache
            $this->cacheTypeList->cleanType('full_page');
            $this->cacheTypeList->cleanType('block_html');
        }

        return parent::afterSave();
    }
}
