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

$serviceId = (int)$_GET['serviceId'];
$ca = new ClientArea();
$hosting = Hosting::getByServiceId($serviceId);

if (!$ca->hasAccessToHosting($hosting)) {
    die('Access denied');
}

$solusServer = SolusServer::getByServiceId($serviceId);
$serverId = (int)$hosting->server;
$serverParams = Server::getParams($serverId);
$serverResource = new ServerResource(Connector::create($serverParams));
$server = $serverResource->get($solusServer->server_id);
$password = $server['data']['settings']['vnc_password'] ?? $server['data']['settings']['vnc']['password'];
$response = $serverResource->vncUp($solusServer->server_id);
$url = 'wss://' . $serverParams['serverhostname'] . '/vnc?url=' . $response['url'];

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>VNC</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body, html {
            height: 100%;
            min-height: 100%;
            padding: 0;
            margin: 0;
        }

        a:hover {
            text-decoration: underline;
        }

        #screen {
            height: calc(100% - 60px);
        }

        .top-bar {
            background: rgb(255, 255, 255);
            display: flex;
            align-items: center;
            padding: 20px;
            max-height: 20px;
        }

        .container {
            height: inherit;
            display: flex;
            flex-direction: column;
        }

        #ctrl-alt-del {
            font-weight: 600;
            display: flex;
            align-items: center;
            color: black;
            text-decoration: none;
        }
    </style>
    <script type="module" crossorigin="anonymous">
        import RFB from '../node_modules/@novnc/novnc/core/rfb.js';

        const url = <?= json_encode($url) ?>;
        const password = <?= json_encode($password) ?>;
        const options = {
            credentials: {
                password: password
            }
        };

        const rfb = new RFB(
            document.getElementById('screen'),
            url,
            options
        );

        rfb.scaleViewport = true;
        rfb.resizeSession = true;
        rfb.focusOnClick = true;

        document.querySelector('#ctrl-alt-del').addEventListener('click', () =>  {
            rfb.sendCtrlAltDel();
        });
    </script>
</head>
<body>
<div class="container">
    <div class="top-bar">
        <a href="#" id="ctrl-alt-del">
            Ctrl + Alt + Del
        </a>
    </div>
    <div id="screen"></div>
</div>
</body>
</html>
