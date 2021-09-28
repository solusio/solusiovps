<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources;

/**
 * @package WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources
 */
class UserResource extends ApiResource
{
    public const STATUS_ACTIVE = 'active';

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

    /**
     * @param string $email
     * @return array Empty if user does not exist
     */
    public function getUserByEmail(string $email): array
    {
        $response = $this->processResponse($this->connector->get("users", [
            'query' => [
                'filter' => [
                    'search' => $email,
                ],
            ],
        ]));

        return $response['data'][0] ?? [];
    }

    /**
     * @param int $userId
     * @param array $data
     * @return void
     */
    public function updateUser(int $userId, array $data): void
    {
        $this->processResponse($this->connector->put("users/{$userId}", [
            'json' => $data,
        ]));
    }

    /**
     * @param int $userId
     * @param array $data
     * @return void
     */
    public function patchUser(int $userId, array $data): void
    {
        $this->processResponse($this->connector->patch("users/{$userId}", [
            'json' => $data,
        ]));
    }

    /**
     * @param int $userId
     * @return void
     */
    public function deleteUser(int $userId): void
    {
        $this->processResponse($this->connector->delete("users/{$userId}"));
    }
}
