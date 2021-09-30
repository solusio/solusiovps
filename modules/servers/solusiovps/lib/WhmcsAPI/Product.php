<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\WhmcsAPI;

use WHMCS\Module\Server\SolusIoVps\Database\Models\Hosting;
use WHMCS\Module\Server\SolusIoVps\Database\Models\Product as ProductModel;
use WHMCS\Module\Server\SolusIoVps\Database\Models\Server;
use WHMCS\Module\Server\SolusIoVps\Database\Models\SolusServer;
use WHMCS\Module\Server\SolusIoVps\Database\Models\Upgrade;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ServerResource;

/**
 * @package WHMCS\Module\Server\SolusIoVps\WhmcsAPI
 */
class Product
{
    public const MODULE_NAME = 'solusiovps';
    public const CONFIG_OPTIONS_TYPE = 'configoptions';

    public static function updateDomain(int $serviceId, string $domain): void
    {
        localAPI('UpdateClientProduct', [
            'serviceid' => $serviceId,
            'domain' => $domain,
        ]);
    }

    public static function upgrade(int $upgradeId): void
    {
        $upgrade = Upgrade::getById($upgradeId);
        $serviceId = (int) $upgrade->relid;

        if ($upgrade->type !== self::CONFIG_OPTIONS_TYPE) {
            return;
        }

        list($newProductId, $paymentType) = explode(',', $upgrade->newvalue);

        $newProduct = ProductModel::getById($newProductId);

        if (empty($newProduct) || $newProduct->type !== self::MODULE_NAME) {
            return;
        }

        $newPlanId = (int) $newProduct->configoption1;
        $hosting = Hosting::getByServiceId($serviceId);
        $server = SolusServer::getByServiceId($serviceId);
        $serverId = (int) $hosting->server;
        $serverParams = Server::getParams($serverId);
        $serverResource = new ServerResource(Connector::create($serverParams));

        $serverResource->resize($server->server_id, $newPlanId);
    }
}
