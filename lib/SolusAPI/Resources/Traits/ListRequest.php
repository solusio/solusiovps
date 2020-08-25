<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\Traits;

/**
 * Trait ListRequest
 * @package WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\Traits
 */
trait ListRequest
{
    /**
     * @return array
     */
    public function list(): array
    {
        return $this->processResponse($this->connector->get(static::ENTITY));
    }
}
