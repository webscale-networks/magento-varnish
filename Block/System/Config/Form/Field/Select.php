<?php
/**
 * Copyright © Webscale. All rights reserved.
 * See LICENSE for license details.
 */

namespace Webscale\Varnish\Block\System\Config\Form\Field;

use Magento\Framework\Data\OptionSourceInterface;
use Webscale\Varnish\Helper\Config;

class Select implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [['value' => '', 'label' => __('Please Select...')]];
    }
}
