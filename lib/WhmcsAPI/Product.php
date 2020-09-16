<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\WhmcsAPI;

/**
 * @package WHMCS\Module\Server\SolusIoVps\WhmcsAPI
 */
class Product
{
    public static function updateDomain(int $serviceId, string $domain): void
    {
        localAPI('UpdateClientProduct', [
            'serviceid' => $serviceId,
            'domain' => $domain,
        ]);
    }
}
