<?php
/**
 * CravenDunnill ProductTileCalculator Extension
 *
 * Warehouse Closure Notice Block for cart and other pages
 */
declare(strict_types=1);

namespace CravenDunnill\ProductTileCalculator\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class WarehouseClosureNotice extends Template
{
    /**
     * Config path prefix
     */
    const CONFIG_PATH_PREFIX = 'tiles_warehouse_closure/general/';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * Check if warehouse closure notice is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::CONFIG_PATH_PREFIX . 'enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get warehouse closure message HTML content
     *
     * @return string
     */
    public function getMessage(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_PATH_PREFIX . 'message_text',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get warehouse closure background color
     *
     * @return string
     */
    public function getBackgroundColor(): string
    {
        $color = $this->scopeConfig->getValue(
            self::CONFIG_PATH_PREFIX . 'background_color',
            ScopeInterface::SCOPE_STORE
        );
        return $color ?: '#ffcc00';
    }

    /**
     * Get warehouse closure text color
     *
     * @return string
     */
    public function getTextColor(): string
    {
        $color = $this->scopeConfig->getValue(
            self::CONFIG_PATH_PREFIX . 'text_color',
            ScopeInterface::SCOPE_STORE
        );
        return $color ?: '#000000';
    }

    /**
     * Get warehouse closure start datetime
     *
     * @return string
     */
    public function getStartDatetime(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_PATH_PREFIX . 'start_datetime',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get warehouse closure end datetime
     *
     * @return string
     */
    public function getEndDatetime(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_PATH_PREFIX . 'end_datetime',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get all config as JSON for JavaScript
     *
     * @return string
     */
    public function getConfigJson(): string
    {
        $config = [
            'enabled' => $this->isEnabled(),
            'messageHtml' => $this->getMessage(),
            'backgroundColor' => $this->getBackgroundColor(),
            'textColor' => $this->getTextColor(),
            'startDatetime' => $this->getStartDatetime(),
            'endDatetime' => $this->getEndDatetime()
        ];
        return json_encode($config);
    }
}
