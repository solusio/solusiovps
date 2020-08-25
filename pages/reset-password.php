<?php

// Copyright 2020. Plesk International GmbH.

use WHMCS\Module\Server\SolusIoVps\Database\Models\Hosting;
use WHMCS\Module\Server\SolusIoVps\Database\Models\Server;
use WHMCS\Module\Server\SolusIoVps\Database\Models\SolusServer;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ServerResource;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\UserResource;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\ClientArea;

define('CLIENTAREA', true);

require dirname(__DIR__, 4) . '/init.php';
require dirname(__DIR__) . '/vendor/autoload.php';

$serviceId = (int) $_GET['serviceId'];
$ca = new ClientArea();
$hosting = Hosting::getByServiceId($serviceId);

if (!$ca->hasAccessToHosting($hosting)) {
    die('Access denied');
}

$server = SolusServer::getByServiceId($serviceId);
$serverId = (int) $hosting->server;
$serverParams = Server::getParams($serverId);
$payload = json_decode($server->payload, true);
$solusUserId = (int) $payload['user']['id'];
$userResource = new UserResource(Connector::create($serverParams));
$userApiToken = $userResource->createToken($solusUserId);
$serverResource = new ServerResource(Connector::create($serverParams, $userApiToken));

$serverResource->resetPassword($server->server_id);
