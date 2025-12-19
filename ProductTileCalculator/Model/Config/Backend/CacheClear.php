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
use Magento\Framework\App\Cache\Frontend\Pool as CacheFrontendPool;
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
     * @var CacheFrontendPool
     */
    protected $cacheFrontendPool;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param CacheFrontendPool $cacheFrontendPool
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        CacheFrontendPool $cacheFrontendPool,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
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
            // Clean (flush) the cache types
            $cacheTypes = ['config', 'full_page', 'block_html'];

            foreach ($cacheTypes as $type) {
                $this->cacheTypeList->cleanType($type);
            }

            // Flush all cache storage
            foreach ($this->cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->getBackend()->clean();
            }
        }

        return parent::afterSave();
    }
}
