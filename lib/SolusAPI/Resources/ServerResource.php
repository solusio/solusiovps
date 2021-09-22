<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources;

use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\Traits\ListRequest;

/**
 * Class ServerResource
 * @package WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources
 */
class ServerResource extends ApiResource
{
    use ListRequest;

    const ENTITY = 'servers';

    public function create(array $data): array
    {
        return $this->processResponse($this->connector->post("servers", [
            'json' => $data
        ]));
    }

    public function get(int $id): array
    {
        return $this->processResponse($this->connector->get("servers/{$id}"));
    }

    public function getAllByUser(int $userId): array
    {
        $response = $this->processResponse($this->connector->get("servers", [
            'query' => [
                'filter' => [
                    'user_id' => $userId,
                ],
            ],
        ]));

        return $response['data'];
    }

    public function delete(int $id): array
    {
        return $this->processResponse($this->connector->delete("servers/{$id}"));
    }

    public function start(int $id): array
    {
        return $this->processResponse($this->connector->post("servers/{$id}/start"));
    }

    public function stop(int $id): array
    {
        return $this->processResponse($this->connector->post("servers/{$id}/stop"));
    }

    public function restart(int $id): array
    {
        return $this->processResponse($this->connector->post("servers/{$id}/restart"));
    }

    public function reinstall(int $serverId, int $osId): array
    {
        $options = [];

        if ($osId > 0) {
            $options = [
                'json' => [
                    'os' => $osId,
                ],
            ];
        }

        return $this->processResponse($this->connector->post("servers/{$serverId}/reinstall", $options));
    }

    public function suspend(int $id): array
    {
        return $this->processResponse($this->connector->post("servers/{$id}/suspend"));
    }

    public function resume(int $id): array
    {
        return $this->processResponse($this->connector->post("servers/{$id}/resume"));
    }

    public function vncUp(int $id): array
    {
        return $this->processResponse($this->connector->post("servers/{$id}/vnc_up"));
    }

    public function resetPassword(int $id): array
    {
        return $this->processResponse($this->connector->post("servers/{$id}/reset_password"));
    }

    public function changeHostname(int $id, string $hostname): array
    {
        return $this->processResponse($this->connector->patch("servers/{$id}", [
            'json' => [
                'name' => $hostname,
            ],
        ]));
    }

    public function resize(int $id, int $planId): array
    {
        return $this->processResponse($this->connector->post("servers/{$id}/resize", [
            'json' => [
                'preserve_disk' => true,
                'plan_id' => $planId,
            ],
        ]));
    }

    public function changeBootMode(int $id, string $bootMode): array
    {
        return $this->processResponse($this->connector->patch("servers/{$id}", [
            'json' => [
                'boot_mode' => $bootMode,
            ],
        ]));
    }
}
