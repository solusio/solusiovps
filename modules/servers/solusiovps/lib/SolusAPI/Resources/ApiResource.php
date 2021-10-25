<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources;

use \GuzzleHttp\ClientInterface;

/**
 * Class ApiResource
 * @package WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources
 */
class ApiResource
{
    /**
     * @var ClientInterface
     */
    protected $connector;

    /**
     * Plan constructor.
     * @param ClientInterface $connector
     */
    public function __construct(ClientInterface $connector)
    {
        $this->connector = $connector;
    }

    public function getConnector(): ClientInterface
    {
        return $this->connector;
    }

    /**
     * @param $response
     * @return array
     */
    protected function processResponse($response): array
    {
        if (!$body = $response->getBody()) {
            return [];
        }

        $data = json_decode($body->getContents(), true);

        if (!is_array($data)) {
            $data = [];
        }

        return $data;
    }
}
