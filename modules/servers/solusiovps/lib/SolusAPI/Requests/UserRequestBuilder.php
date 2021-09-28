<?php

// Copyright 2021. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\SolusAPI\Requests;

use WHMCS\Module\Server\SolusIoVps\Helpers\Arr;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\UserResource;

final class UserRequestBuilder
{
    /**
     * @var string $email
     */
    private $email;

    /**
     * @var string $password
     */
    private $password;

    /**
     * @var string $status
     */
    private $status;

    /**
     * @var int $whmcsUserId
     */
    private $whmcsUserId;

    /**
     * @var int $limitGroupId
     */
    private $limitGroupId;

    /**
     * @var array $roles
     */
    private $roles;

    public function __construct(
        $whmcsUserId,
        $email,
        $password,
        $status
    ) {
        $this->whmcsUserId = $whmcsUserId;
        $this->email = $email;
        $this->password = $password;
        $this->status = $status;
    }

    public static function fromWHMCSCreateAccountParams(array $params): self
    {
        $builder = new self(
            (int)$params['userid'],
            $params['clientsdetails']['email'],
            $params['password'],
            UserResource::STATUS_ACTIVE,
        );

        $role = (int)Arr::get($params, 'configoption7');
        if ($role > 0) {
            $builder->withRoles([$role]);
        }

        $limitGroup = (int)Arr::get($params, 'configoption8');
        if ($limitGroup > 0) {
            $builder->withLimitGroup($limitGroup);
        }

        return $builder;
    }

    public function withRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function withLimitGroup(int $limitGroupId): self
    {
        $this->limitGroupId = $limitGroupId;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getWhmcsUserId(): int
    {
        return $this->whmcsUserId;
    }

    public function getCreateRequest(): array
    {
        $request = [
            'email' => $this->email,
            'password' => $this->password,
            'status' => $this->status,
            'billing_user_id' => $this->whmcsUserId,
        ];

        if (!empty($this->limitGroupId)) {
            $request['limit_group_id'] = $this->limitGroupId;
        }

        if (!empty($this->roles)) {
            $request['roles'] = $this->roles;
        }

        return $request;
    }

    public function getUpdateRequest(): array
    {
        return [
            'status' => $this->status,
            'billing_user_id' => $this->whmcsUserId,
        ];
    }
}