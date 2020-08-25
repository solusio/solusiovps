<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources;

/**
 * @package WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources
 */
class UserResource extends ApiResource
{
    /**
     * @param array $data
     * @return int SolusIO user ID
     */
    public function create(array $data): int
    {
        $response = $this->processResponse($this->connector->post("users", [
            'json' => $data,
        ]));

        return (int) $response['data']['id'];
    }

    /**
     * @param int $userId
     * @return string
     */
    public function createToken(int $userId): string
    {
        $response = $this->processResponse($this->connector->post("users/{$userId}/tokens", [
            'json' => [
                'is_one_time' => true,
            ],
        ]));

        return $response['data']['access_token'];
    }
}
