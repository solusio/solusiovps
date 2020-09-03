<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\WhmcsAPI;

/**
 * @package WHMCS\Module\Server\SolusIoVps\WhmcsAPI
 */
class Config
{
    public static function getSystemUrl(): string
    {
        $response = localAPI('GetConfigurationValue', ['setting' => 'SystemURL']);

        return $response['value'];
    }
}
