<?php
/**
 * Copyright © Webscale. All rights reserved.
 * See LICENSE for license details.
 */

namespace Webscale\Varnish\Service;

use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;
use Webscale\Varnish\Helper\Config;

class Api
{
    /** API request base URL */
    public const API_BASE_URI = 'https://aperture.section.io/api/v1';

    private const API_PATH_TOKEN = 'section-token';

    private const API_USER_AGENT = 'section.io-magento2';

    /** @var ResponseFactory */
    private $responseFactory;

    /** @var ClientFactory */
    private $clientFactory;

    /** @var Config */
    private $config;

    /**
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param Config $config
     */
    public function __construct(
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        Config $config
    ) {
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->config = $config;
    }

    /**
     * Do API request with provided params
     *
     * @param string $endpoint
     * @param array $params
     * @param string $method
     *
     * @return Response
     */
    public function doRequest(
        string $endpoint,
        array $params = [],
        string $method = Request::HTTP_METHOD_GET
    ): Response {
        $client = $this->clientFactory->create([
            'config' => [
                'base_uri' => static::API_BASE_URI,
                'debug' => false,
                'headers' => [
                    static::API_PATH_TOKEN => $this->config->getApiToken(),
                    'User-Agent' => static::API_USER_AGENT . '/' . $this->config->getVersion(),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ],
        ]);

        $this->config->log(str_replace(self::API_BASE_URI, '', $endpoint));

        try {
            $response = $client->request(
                $method,
                $endpoint,
                $params
            );
        } catch (GuzzleException $exception) {
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        }

        return $response;
    }
}
