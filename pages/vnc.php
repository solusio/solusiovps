<?php

// Copyright 2020. Plesk International GmbH.

use WHMCS\Module\Server\SolusIoVps\Database\Models\Hosting;
use WHMCS\Module\Server\SolusIoVps\Database\Models\Server;
use WHMCS\Module\Server\SolusIoVps\Database\Models\SolusServer;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ServerResource;
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

$solusServer = SolusServer::getByServiceId($serviceId);
$serverId = (int) $hosting->server;
$serverParams = Server::getParams($serverId);
$serverResource = new ServerResource(Connector::create($serverParams));
$server = $serverResource->get($solusServer->server_id);
$password = $server['data']['settings']['vnc']['password'];
$response = $serverResource->vncUp($solusServer->server_id);
$url = 'wss://' . $serverParams['serverhostname'] . '/vnc?url=' . $response['url'];

?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        #screen {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
        }
    </style>
    <script type="module" crossorigin="anonymous">
        import RFB from '/modules/servers/solusiovps/node_modules/@novnc/novnc/core/rfb.js';

        const url = <?= json_encode($url) ?>;
        const password = <?= json_encode($password) ?>;

        let rfb = new RFB(
            document.getElementById('screen'),
            url,
            {
                credentials: {
                    password: password
                }
            }
        );

        rfb.scaleViewport = true;
        rfb.resizeSession = true;
        rfb.focusOnClick = true;
    </script>
</head>
<body>
    <div id="screen"></div>
</body>
</html>
