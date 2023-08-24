<?php
/**
 * Copyright © Webscale. All rights reserved.
 * See LICENSE for license details.
 */

namespace Webscale\Varnish\Controller\Adminhtml\Sync;

use Magento\Framework\App\ResponseInterface;
use Webscale\Varnish\Controller\Adminhtml\AbstractGet;
use Webscale\Varnish\Helper\Config;

class All extends AbstractGet
{
    /**
     * Retrieve accounts
     *
     * @return ResponseInterface
     */
    public function execute(): ResponseInterface
    {
        $this->config->log(__('Syncing accounts/applications...'));
        $accounts = $environments = $applications = [];

        try {
            $accounts = $this->fetchAccounts();

            if (!empty($accounts)) {
                foreach ($accounts as $account) {
                    $apps = $this->fetchApplications($account['id']);
                    if (!empty($apps)) {
                        $applications[$account['id']] = $apps;
                        foreach ($apps as $app) {
                            $environments[$app['id']] = $this->fetchEnvironments($account['id'], $app['id']);
                        }
                    } else {
                        unset($accounts[$account['id']]);
                    }
                }

                if (!empty($accounts) && !empty($applications) && !empty($environments)) {
                    $this->config->saveAccountData(json_encode($accounts), Config::ENTITY_ACCOUNTS);
                    $this->config->saveAccountData(json_encode($applications), Config::ENTITY_APPLICATIONS);
                    $this->config->saveAccountData(json_encode($environments), Config::ENTITY_ENVIRONMENTS);

                    $this->cacheTypeList->cleanType('config');
                    $this->messageManager->addSuccessMessage(
                        __('Successfully synced account and applications.')
                    );
                }
            }
        } catch (\Exception $e) {
            $this->config->log($e->getMessage(), 'critical');

            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving accounts.'
                    . ' Please refer to logs for more information.')
            );
        }

        return $this->_redirect('adminhtml/system_config/edit/section/webscale_varnish', ['_current' => true]);
    }

    /**
     * Fetch accounts
     *
     * @return array
     */
    protected function fetchAccounts(): array
    {
        $url = $this->config->generateApertureUrl([
            'uriStem' => '/account'
        ]);

        $result =  $this->fetch($url);

        return $this->compactResult($result, 'id');
    }

    /**
     * Fetch applications
     *
     * @param int $accId
     * @return array
     */
    protected function fetchApplications(int $accId): array
    {
        $url = $this->config->generateApertureUrl([
            'accountId' => $accId,
            'uriStem' => '/application'
        ]);

        $result = $this->fetch($url);

        return $this->compactResult($result);
    }

    /**
     * Fetch environments
     *
     * @param int $accId
     * @param int $appId
     * @return array
     */
    protected function fetchEnvironments(int $accId, int $appId): array
    {
        $url = $this->config->generateApertureUrl([
            'accountId' => $accId,
            'applicationId' => $appId,
            'uriStem' => '/environment'
        ]);

        $result = $this->fetch($url);

        return $this->compactResult($result);
    }

    /**
     * @param array $result
     * @param string $key
     * @return array
     */
    protected function compactResult(array $result, string $key = ''): array
    {
        $entities = [];

        if (!empty($result)) {
            foreach ($result as $entity) {
                if ($key == 'id') {
                    $entities[$entity[$key]] = $entity;
                } else {
                    $entities[] = $entity;
                }
            }
        }

        return $entities;
    }

    /**
     * Retrieve data from aperture
     *
     * @param string $url
     * @return array
     */
    protected function fetch(string $url): array
    {
        $return = [];

        try {
            $request = $this->api->doRequest($url);

            if ($request->getStatusCode() == 200) {
                $return = json_decode($request->getBody()->getContents(), true);
            } else {
                $this->config->log('Code: ' . $request->getStatusCode(), 'critical');
                $this->config->log('Reason Phrase: ' . $request->getReasonPhrase(), 'critical');
            }
        } catch (\Exception $e) {
            $this->config->log($e->getMessage(), 'critical');
        }

        return $return;
    }
}
