<?php
/**
 * CravenDunnill ProductTileCalculator Extension
 *
 * Backend model to clear cache when warehouse closure settings are saved
 */
declare(strict_types=1);

namespace CravenDunnill\ProductTileCalculator\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Cache\Manager as CacheManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\App\Cache\TypeListInterface;

class CacheClear extends Value
{
    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param CacheManager $cacheManager
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        CacheManager $cacheManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->cacheManager = $cacheManager;
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
            // Flush and refresh cache types (same as admin Cache Management)
            $cacheTypes = ['config', 'full_page', 'block_html'];
            $this->cacheManager->flush($cacheTypes);
        }

        return parent::afterSave();
    }
}
