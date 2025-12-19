<?php
/**
 * CravenDunnill ProductTileCalculator Extension
 *
 * HTML5 Color Picker field with text input for admin configuration
 */
declare(strict_types=1);

namespace CravenDunnill\ProductTileCalculator\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ColorPicker extends Field
{
    /**
     * Add HTML5 color picker with text input
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $elementId = $element->getHtmlId();
        $elementName = $element->getName();
        $elementValue = $element->getEscapedValue() ?: '#000000';

        $html = '<div style="display: flex; align-items: center; gap: 10px;">';

        // Color picker input
        $html .= sprintf(
            '<input type="color" id="%s_picker" value="%s" style="width: 60px; height: 40px; padding: 2px; cursor: pointer; border: 1px solid #adadad; border-radius: 4px;"/>',
            $elementId,
            $elementValue
        );

        // Text input for hex code
        $html .= sprintf(
            '<input type="text" id="%s" name="%s" value="%s" class="input-text" style="width: 100px;" maxlength="7" placeholder="#000000"/>',
            $elementId,
            $elementName,
            $elementValue
        );

        $html .= '</div>';

        // JavaScript to sync the two inputs
        $html .= sprintf(
            '<script>
                require(["jquery"], function($) {
                    var $picker = $("#%1$s_picker");
                    var $text = $("#%1$s");

                    $picker.on("input change", function() {
                        $text.val($(this).val());
                    });

                    $text.on("input change", function() {
                        var val = $(this).val();
                        if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
                            $picker.val(val);
                        }
                    });
                });
            </script>',
            $elementId
        );

        return $html;
    }
}
