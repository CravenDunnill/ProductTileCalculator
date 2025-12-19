<?php
/**
 * CravenDunnill ProductTileCalculator Extension
 *
 * HTML5 Color Picker field for admin configuration
 */
declare(strict_types=1);

namespace CravenDunnill\ProductTileCalculator\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ColorPicker extends Field
{
    /**
     * Add HTML5 color picker input type
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $element->setType('color');
        $element->setStyle('width: 60px; height: 40px; padding: 2px; cursor: pointer;');

        return $element->getElementHtml();
    }
}
