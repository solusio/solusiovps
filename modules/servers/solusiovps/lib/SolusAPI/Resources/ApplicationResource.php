<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources;

use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\Traits\ListRequest;

/**
 * @package WHMCS\Module\Server\SolusIoVps\SolusAPI
 */
class ApplicationResource extends ApiResource
{
    use ListRequest;

    const ENTITY = 'applications';
}
