<?php
/**
 * Copyright © Webscale. All rights reserved.
 * See LICENSE for license details.
 */

namespace Webscale\Varnish\Block\System\Config\Settings;

use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\View\Helper\Js;
use Webscale\Varnish\Helper\Config;

class Application extends Fieldset
{
    /** @var Config $config */
    private $config;

    /**
     * @param Context $context
     * @param Session $authSession
     * @param Js $jsHelper
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        Config $config,
        array $data = []
    ) {
        $this->config = $config;

        parent::__construct($context, $authSession, $jsHelper, $data);
    }

    /**
     * @inheritdoc
     */
    public function render(AbstractElement $element): string
    {
        // return parent::render($element);
        return empty($this->config->getApiToken()) ? '' : parent::render($element);
    }
}
