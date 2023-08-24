<?php
/**
 * Copyright © Webscale. All rights reserved.
 * See LICENSE for license details.
 */

namespace Webscale\Varnish\Controller\Adminhtml\Purge;

use Magento\PageCache\Model\Config as CacheConfig;
use Webscale\Varnish\Controller\Adminhtml\AbstractPost;
use Magento\Framework\App\ResponseInterface;

class All extends AbstractPost
{
    /**
     * Retrieve accounts
     *
     * @return ResponseInterface
     */
    public function execute(): ResponseInterface
    {
        try {
            if ($this->cacheConfig->getType() == CacheConfig::VARNISH && $this->config->isAvailable()) {
                if ($this->purgeCache->sendPurgeRequest('.*')) {
                    $this->messageManager->addSuccessMessage(
                        __('Varnish cache flushed successfully.')
                    );
                } else {
                    $this->messageManager->addErrorMessage(
                        __('There is error occurred while trying to purge varnish cache.' .
                            ' Please refer to logs for more information.')
                    );
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('There is error occurred while trying to purge varnish cache.' .
                    ' Please refer to logs for more information.')
            );
        }

        return $this->_redirect('adminhtml/cache/index', ['_current' => true]);
    }
}
