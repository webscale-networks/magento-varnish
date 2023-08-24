<?php
/**
 * Copyright © Webscale. All rights reserved.
 * See LICENSE for license details.
 */

namespace Webscale\Varnish\Block\System\Config\Form\Button;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Webscale\Varnish\Helper\Config;

class SyncAccount extends Field
{
    /** @var Config $config */
    private $config;

    /**
     * @param Context $context
     * @param Config $config
     */
    public function __construct(
        Context $context,
        Config $config
    ) {
        $this->config = $config;

        parent::__construct($context);
    }

    /**
     * Set template path
     *
     * @return $this
     */
    protected function _prepareLayout(): static
    {
        parent::_prepareLayout();
        $this->setTemplate('Webscale_Varnish::system/config/sync_account.phtml');

        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $element = clone $element;
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $originalData = $element->getOriginalData();

        $buttonLabel = $originalData['button_label'];
        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id' => $element->getHtmlId(),
            ]
        );

        return $this->_toHtml();
    }

    /**
     * Retrieve form URI
     *
     * @return string
     */
    public function getFormUrl(): string
    {
        return $this->getUrl('webscalevarnish/sync/all', ['_current' => true]);
    }

    /**
     * Get block config as JSON
     *
     * @return string
     */
    public function getBlockConfigAsJson(): string
    {
        $config = [
            'url' => $this->getFormUrl(),
            'elementId' => $this->getHtmlId(),
            'apiKeyValue' => !empty($this->config->getApiToken()),
            'alertTitle' => __('API key value is missing!'),
            'alertContent' => __('Please add and save API key first!'),
            'accounts' => $this->config->getAccounts() ?: '{}',
            'applications' => $this->config->getApplications() ?: '{}',
            'environments' => $this->config->getEnvironments() ?: '{}',
            'selectedAccount' => $this->config->getAccountId(),
            'selectedApplication' => $this->config->getApplicationId(),
            'selectedEnvironment' => $this->config->getEnvironmentName(),
            'accountsSelectId' => 'webscale_varnish_application_account_id',
            'applicationsSelectId' => 'webscale_varnish_application_application_id',
            'environmentsSelectId' => 'webscale_varnish_application_environment_id',
        ];

        return json_encode($config);
    }
}
