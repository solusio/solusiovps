<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources;

use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\Traits\ListRequest;

/**
 * Class ProjectResource
 * @package WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources
 */
class ProjectResource extends ApiResource
{
    use ListRequest;

    const ENTITY = 'projects';
}
