<?php

// Copyright 2020. Plesk International GmbH.

use WHMCS\Module\Server\SolusIoVps\Database\Models\User;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ServerResource;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\UserResource;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Config;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Product;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Servers;

add_hook('AfterProductUpgrade', 1, function (array $params) {
    Product::upgrade($params['upgradeid']);
});

add_hook('PreDeleteClient', 1, function (array $params) {
    $config = Config::loadModuleConfig();

    if (empty($config['delete_solus_user'])) {
        return;
    }

    $whmcsUser = User::getById($params['userid']);

    if ($whmcsUser === null) {
        return;
    }

    $serverParams = Servers::getValidParams();

    if (empty($serverParams)) {
        throw new \Exception('No valid WHMCS server found');
    }

    $userResource = new UserResource(Connector::create($serverParams));
    $solusUser = $userResource->getUserByEmail($whmcsUser->email);

    if (empty($solusUser)) {
        return;
    }

    $serverResource = new ServerResource(Connector::create($serverParams));
    $servers = $serverResource->getAllByUser($solusUser['id']);

    if (!empty($servers)) {
        return;
    }

    $userResource->deleteUser($solusUser['id']);
});
