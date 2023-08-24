<?php
/**
 * Copyright © Webscale. All rights reserved.
 * See LICENSE for license details.
 */

namespace Webscale\Varnish\Helper;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\ModuleListInterface;
use Webscale\Varnish\Service\Api;
use Webscale\Varnish\Logger\Logger;

class Config extends AbstractHelper
{
    public const XML_PATH_ENABLED           = 'webscale_varnish/general/enabled';
    public const XML_PATH_TOKEN             = 'webscale_varnish/general/token';
    public const XML_PATH_DEBUG             = 'webscale_varnish/developer/debug';

    public const XML_PATH_ACCOUNT           = 'webscale_varnish/application/account_id';
    public const XML_PATH_APPLICATION       = 'webscale_varnish/application/application_id';
    public const XML_PATH_ENVIRONMENT       = 'webscale_varnish/application/environment_id';

    public const XML_PATH_ACCOUNTS_JSON     = 'webscale_varnish/application/accounts_json';
    public const XML_PATH_APPLICATIONS_JSON = 'webscale_varnish/application/applications_json';
    public const XML_PATH_ENVIRONMENTS_JSON = 'webscale_varnish/application/environments_json';

    public const ENTITY_ACCOUNTS            = 'accounts';
    public const ENTITY_APPLICATIONS        = 'applications';
    public const ENTITY_ENVIRONMENTS        = 'environments';

    /** @var ModuleListInterface $moduleList */
    private $moduleList;

    /** @var Logger $logger */
    protected $logger;

    /**
     * @param Context $context
     * @param ModuleListInterface $moduleList
     * @param Logger $logger
     */
    public function __construct(
        Context             $context,
        ModuleListInterface $moduleList,
        WriterInterface     $writerInterface,
        Logger              $logger
    )
    {
        $this->moduleList = $moduleList;
        $this->writerInterface = $writerInterface;
        $this->logger = $logger;

        parent::__construct($context);
    }

    /**
     * Check if integration is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ENABLED);
    }

    /**
     * Check if module setup
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return (
            $this->isEnabled()
            && $this->getAccountId()
            && $this->getApplicationId()
            && !empty($this->getEnvironmentName())
            && !empty($this->getApiToken())
        );
    }

    /**
     * Retrieve API token
     *
     * @return string
     */
    public function getApiToken(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_TOKEN);
    }

    /**
     * Retrieve debug settings
     *
     * @return bool
     */
    public function debugEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_DEBUG);
    }

    /**
     * Retrieve account id
     *
     * @return string
     */
    public function getAccountId(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_ACCOUNT);
    }

    /**
     * Retrieve application id
     *
     * @return string
     */
    public function getApplicationId(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_APPLICATION);
    }

    /**
     * Retrieve environment name
     *
     * @return string
     */
    public function getEnvironmentName(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_ENVIRONMENT);
    }

    /**
     * Retrieve accounts json
     *
     * @return string
     */
    public function getAccounts(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_ACCOUNTS_JSON);
    }

    /**
     * Retrieve applications json
     *
     * @return string
     */
    public function getApplications(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_APPLICATIONS_JSON);
    }

    /**
     * Retrieve applications environments
     *
     * @return string
     */
    public function getEnvironments(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_ENVIRONMENTS_JSON);
    }

    /**
     * Retrieve proxy name
     *
     * @return string
     */
    public function getProxy(): string
    {
        return 'varnish';
    }

    /**
     * Retrieve module version
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getVersion(): string
    {
        try {
            $name = parent::_getModuleName();
            $version = $this->moduleList->getOne($name)['setup_version'];
        } catch (\Exception $e) {
            $version = '0.0.1';
        }

        return $version;
    }

    /**
     * Save accounts data
     *
     * @param string $data
     * @param string $entity
     * @return bool
     */
    public function saveAccountData(string $data, string $entity): bool
    {
        try {
            switch ($entity) {
                case self::ENTITY_ACCOUNTS:
                    $path = self::XML_PATH_ACCOUNTS_JSON; break;
                case self::ENTITY_APPLICATIONS:
                    $path = self::XML_PATH_APPLICATIONS_JSON; break;
                case self::ENTITY_ENVIRONMENTS:
                    $path = self::XML_PATH_ENVIRONMENTS_JSON; break;
            }

            if (!empty($path)) {
                $this->writerInterface->save($path, $data);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->log($e->getMessage(), 'critical');

            return false;
        }
    }

    /**
     * Generate purge URI
     *
     * @param string $tagsPattern
     * @return string
     */
    public function generatePurgeUri(string $tagsPattern): string
    {
        return $this->generateApertureUrl([
            'accountId' => $this->getAccountId(),
            'applicationId' => $this->getApplicationId(),
            'environmentName' => $this->getEnvironmentName(),
            'proxyName' => $this->getProxy(),
            'uriStem' => '/state?async=true&banExpression='
                . urlencode('obj.http.X-Magento-Tags ~ ' . $tagsPattern)
        ]);
    }

    /**
     * Generate aperture url
     *
     * @param array $params
     * @return string
     */
    public function generateApertureUrl(array $params): string
    {
        $url = Api::API_BASE_URI;

        if (isset($params['accountId'])) {
            $url .= '/account/' . $params['accountId'];
        }

        if (isset($params['applicationId'])) {
            $url .= '/application/' . $params['applicationId'];
        }

        if (isset($params['environmentName'])) {
            $url .= '/environment/' . $params['environmentName'];
        }

        if (isset($params['proxyName'])) {
            $url .= '/proxy/' . $params['proxyName'];
        }

        if (isset($params['domain'])) {
            $url .= '/domain/' . $params['domain'];
        }

        if (isset($params['uriStem'])) {
            $url .= $params['uriStem'];
        }

        return $url;
    }

    /**
     * Write message to custom log
     *
     * @param string|array|object $message
     * @param string $level
     * @return bool
     */
    public function log($message, string $level = 'info'): bool
    {
        if (is_array($message) || is_object($message)) {
            $message = json_encode($message);
        }

        if (!$this->debugEnabled() && $level == 'info') {
            return false;
        }

        $this->logger->log($level, $message);

        return true;
    }
}
