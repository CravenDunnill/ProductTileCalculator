<?php
/**
 * CravenDunnill ProductTileCalculator Extension
 *
 * HTML5 DateTime-local field for admin configuration
 */
declare(strict_types=1);

namespace CravenDunnill\ProductTileCalculator\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class DateTime extends Field
{
    /**
     * Add HTML5 datetime-local input type
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $element->setType('datetime-local');
        $element->setStyle('width: 250px;');

        return $element->getElementHtml();
    }
}
