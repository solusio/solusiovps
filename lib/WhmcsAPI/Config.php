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

    public static function loadModuleConfig(): array
    {
        $configFile = dirname(__DIR__, 2) . '/config.php';

        if (is_file($configFile)) {
            $config = require $configFile;

            if (is_array($config)) {
                return $config;
            }
        }

        throw new \Exception('Failed to load module configuration');
    }
}
