<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources;

class UsageResource extends ApiResource
{
    public function cpu(string $uuid): array
    {
        return $this->processResponse($this->connector->get("usage/cpu/{$uuid}"));
    }

    public function network(string $uuid): array
    {
        return $this->processResponse($this->connector->get("usage/network/{$uuid}"));
    }

    public function disks(string $uuid): array
    {
        return $this->processResponse($this->connector->get("usage/disks/{$uuid}"));
    }

    public function memory(string $uuid): array
    {
        return $this->processResponse($this->connector->get("usage/memory/{$uuid}"));
    }
}
