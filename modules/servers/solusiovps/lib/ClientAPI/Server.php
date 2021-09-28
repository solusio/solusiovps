<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\ClientAPI;

use Exception;
use WHMCS\Module\Server\SolusIoVps\Database\Models\SolusServer;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ServerResource;

/**
 * Class Server
 * @package WHMCS\Module\Server\SolusIoVps\ClientAPI
 */
class Server
{
    use Response;

    /**
     * @var ServerResource
     */
    private $serverResource;

    /**
     * Server constructor.
     * @param ServerResource $serverResource
     */
    public function __construct(ServerResource $serverResource)
    {
        $this->serverResource = $serverResource;
    }

    /**
     * @param int $serviceId
     */
    public function getStatus(int $serviceId)
    {
        try {
            if (!$server = SolusServer::getByServiceId($serviceId)) {
                $this->error('Entity not found');
            }

            $this->success($this->serverResource->get($server->server_id));
        } catch (Exception $exception) {
            $this->success(['error' => $exception->getMessage()]);
        }
    }
}
