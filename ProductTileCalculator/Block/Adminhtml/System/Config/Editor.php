<?php
/**
 * CravenDunnill ProductTileCalculator Extension
 *
 * WYSIWYG Editor field for admin configuration
 */
declare(strict_types=1);

namespace CravenDunnill\ProductTileCalculator\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;

class Editor extends Field
{
    /**
     * @var WysiwygConfig
     */
    protected $wysiwygConfig;

    /**
     * @param Context $context
     * @param WysiwygConfig $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        WysiwygConfig $wysiwygConfig,
        array $data = []
    ) {
        $this->wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $data);
    }

    /**
     * Render WYSIWYG editor
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $config = $this->wysiwygConfig->getConfig([
            'add_variables' => false,
            'add_widgets' => false,
            'add_images' => false,
            'height' => '200px',
            'width' => '100%'
        ]);

        $element->setWysiwyg(true);
        $element->setConfig($config);

        return $element->getElementHtml();
    }
}
