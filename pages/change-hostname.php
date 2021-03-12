<?php

// Copyright 2020. Plesk International GmbH.

use WHMCS\Module\Server\SolusIoVps\Database\Models\Hosting;
use WHMCS\Module\Server\SolusIoVps\Database\Models\Server;
use WHMCS\Module\Server\SolusIoVps\Database\Models\SolusServer;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ServerResource;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\ClientArea;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Language;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Product;

define('CLIENTAREA', true);

require dirname(__DIR__, 4) . '/init.php';
require dirname(__DIR__) . '/vendor/autoload.php';

$serviceId = (int) $_GET['serviceId'];
$hostname = $_GET['hostname'];
$ca = new ClientArea();
$hosting = Hosting::getByServiceId($serviceId);

if (!$ca->hasAccessToHosting($hosting)) {
    die('Access denied');
}

Language::load();

$server = SolusServer::getByServiceId($serviceId);
$serverId = (int) $hosting->server;
$serverParams = Server::getParams($serverId);
$serverResource = new ServerResource(Connector::create($serverParams));

try {
    $serverResource->changeHostname($server->server_id, $hostname);
    Product::updateDomain($serviceId, $hostname);

    echo Language::trans('solusiovps_hostname_changed');
} catch (\Exception $e) {
    echo Language::trans('solusiovps_error_change_hostname');
}
