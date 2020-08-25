<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources;

use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\Traits\ListRequest;

/**
 * Class Connector
 * @package WHMCS\Module\Server\SolusIoVps\SolusAPI
 */
class PlanResource extends ApiResource
{
    use ListRequest;

    const ENTITY = 'plans';
}
