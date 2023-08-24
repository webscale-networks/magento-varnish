<?php
/**
 * Copyright © Webscale. All rights reserved.
 * See LICENSE for license details.
 */

namespace Webscale\Varnish\Block\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;

class Disabled extends Field
{
    /**
     * Disable field
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $element->setReadonly(true)->getElementHtml();
    }
}
