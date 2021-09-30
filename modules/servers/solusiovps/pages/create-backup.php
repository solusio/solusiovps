<?php

// Copyright 2020. Plesk International GmbH.

use WHMCS\Module\Server\SolusIoVps\Database\Models\Hosting;
use WHMCS\Module\Server\SolusIoVps\Database\Models\Server;
use WHMCS\Module\Server\SolusIoVps\Database\Models\SolusServer;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\BackupResource;
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
$backupResource = new BackupResource(Connector::create($serverParams));

$backupResource->create($server->server_id);
