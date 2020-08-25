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
$response = $backupResource->getAll($server->server_id);
$backups = [];

if (isset($response['data']) && is_array($response['data'])) {
    foreach ($response['data'] as $item) {
        $progress = (int) $item['backup_progress'];
        $status = $item['status'];

        if ($progress > 0) {
            $status .= " {$progress}%";
        }

        $time = new DateTimeImmutable($item['created_at']);

        $backups[] = [
            'id' => $item['id'],
            'status' => $status,
            'message' => $item['backup_fail_reason'] ?? '',
            'time' => $time->format('Y-m-d H:i'),
        ];
    }
}

echo json_encode($backups);
