<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\SolusAPI;

use \GuzzleHttp\Client;
use \GuzzleHttp\ClientInterface;
use WHMCS\Module\Server\SolusIoVps\Helpers\Arr;

/**
 * Class Connector
 * @package WHMCS\Module\Server\SolusIoVps\SolusAPI
 */
class Connector
{
    /**
     * @param array $params
     * @param string $apiToken
     * @return ClientInterface
     */
    public static function create(array $params, string $apiToken = null): ClientInterface
    {
        $scheme = Arr::get($params, 'serverhttpprefix');
        $host = Arr::get($params, 'serverhostname');

        if ($apiToken === null) {
            $apiToken = Arr::get($params, 'serverpassword');
        }

        if (!$scheme || !$host || !$apiToken) {
            throw new \Exception('Failed to receive server credentials');
        }

        $apiUrl = $scheme . '://' . $host . '/api/v1/';

        return new Client([
            'base_uri' => $apiUrl,
            'base_url' => $apiUrl,
            'defaults' => [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $apiToken,
                ],
                'verify' => false,
            ],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $apiToken,
            ],
            'verify' => false,
        ]);
    }
}
