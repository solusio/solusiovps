<?php

// Copyright 2020. Plesk International GmbH.

use WHMCS\Module\Server\SolusIoVps\Database\Models\Hosting;
use WHMCS\Module\Server\SolusIoVps\Database\Models\Server;
use WHMCS\Module\Server\SolusIoVps\Database\Models\SolusServer;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\UsageResource;
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
$uuid = $payload['uuid'];

$usageResource = new UsageResource(Connector::create($serverParams));
$cpuUsage = $usageResource->cpu($uuid);
$networkUsage = $usageResource->network($uuid);
$diskUsage = $usageResource->disks($uuid);
$memoryUssage = $usageResource->memory($uuid);

$usage = [
    'cpu' => [],
    'network' => [],
    'disk' => [],
    'memory' => [],
];

foreach ($cpuUsage['data']['items'] as $item) {
    $usage['cpu'][] = [
        'second' => date('H:i:s', strtotime($item['time'])),
        'load_average' => $item['load_average'],
    ];
}

foreach ($networkUsage['data']['items'] as $item) {
    $usage['network'][] = [
        'second' => date('H:i:s', strtotime($item['time'])),
        'read_kb' => $item['derivative']['read_kb'],
        'write_kb' => $item['derivative']['write_kb'],
    ];
}

foreach ($diskUsage['data']['items'] as $item) {
    $usage['disk'][] = [
        'second' => date('H:i:s', strtotime($item['time'])),
        'read_kb' => $item['derivative']['read_kb'],
        'write_kb' => $item['derivative']['write_kb'],
    ];
}

foreach ($memoryUssage['data']['items'] as $item) {
    $usage['memory'][] = [
        'second' => date('H:i:s', strtotime($item['time'])),
        'memory' => $item['memory'],
    ];
}

echo json_encode($usage);
