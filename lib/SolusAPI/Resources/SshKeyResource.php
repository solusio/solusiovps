<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources;

use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\Traits\ListRequest;

/**
 * @package WHMCS\Module\Server\SolusIoVps\SolusAPI
 */
class SshKeyResource extends ApiResource
{
    use ListRequest;

    const ENTITY = 'ssh_keys';

    public function create(string $name, string $body, int $userId): int
    {
        $response = $this->processResponse($this->connector->post("ssh_keys", [
            'json' => [
                'name' => $name,
                'body' => $body,
                'user_id' => $userId,
            ],
        ]));

        return (int) $response['data']['id'];
    }
}
