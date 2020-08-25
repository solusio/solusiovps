<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources;

/**
 * @package WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources
 */
class BackupResource extends ApiResource
{
    public function getAll(int $serverId): array
    {
        return $this->processResponse($this->connector->get("servers/{$serverId}/backups"));
    }

    public function create(int $serverId): array
    {
        return $this->processResponse($this->connector->post("servers/{$serverId}/backups", [
            'json' => [
                'id' => $serverId,
            ],
        ]));
    }

    public function restore(int $backupId): array
    {
        return $this->processResponse($this->connector->post("backups/{$backupId}/restore"));
    }
}
