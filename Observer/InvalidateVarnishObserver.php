<?php
/**
 * Copyright © Webscale. All rights reserved.
 * See LICENSE for license details.
 */

namespace Webscale\Varnish\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Webscale\Varnish\Helper\Config;
use Magento\PageCache\Model\Config as CacheConfig;
use Webscale\Varnish\Model\PurgeCache;
use Magento\Framework\DataObject\IdentityInterface;

class InvalidateVarnishObserver implements ObserverInterface
{
    /**
     * Split the tags to invalidate into batches of this size to avoid the API call URL being too long
     **/
    private const TAGS_BATCH_SIZE = 50;

    /** @var CacheConfig $cacheConfig */
    private $cacheConfig;

    /** @var Config $config */
    private $config;

    /** @var PurgeCache $purgeCache */
    private $purgeCache;

    /** @var array $purged */
    private $purged = [];

    /**
     * @param CacheConfig $cacheConfig
     * @param Config $config
     * @param PurgeCache $purgeCache
     */
    public function __construct(
        CacheConfig $cacheConfig,
        Config $config,
        PurgeCache $purgeCache
    ) {
        $this->config = $config;
        $this->cacheConfig = $cacheConfig;
        $this->purgeCache = $purgeCache;
    }

    /**
     * If Varnish caching is enabled it collects array of tags of incoming object and asks to clean cache.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        try {
            if ($this->cacheConfig->getType() == CacheConfig::VARNISH && $this->config->isAvailable()) {
                $object = $observer->getEvent()->getObject();
                if ($object instanceof IdentityInterface) {
                    $tags = [];
                    $pattern = "((^|,)%s(,|$))";
                    foreach ($object->getIdentities() as $tag) {
                        if (!is_string($tag)) {
                            continue;
                        }
                        if (!in_array($tag, $this->purged)) {
                            $tags[] = sprintf($pattern, $tag);
                            $this->purged[] = $tag;
                        }
                    }
                    if (!empty($tags)) {
                        $batched_tags = array_chunk(array_unique($tags), self::TAGS_BATCH_SIZE);
                        foreach ($batched_tags as $batch) {
                            $this->purgeCache->sendPurgeRequest(implode('|', $batch));
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->config->log($e->getMessage() . PHP_EOL . $e->getTraceAsString(), 'critical');
        }
    }
}
