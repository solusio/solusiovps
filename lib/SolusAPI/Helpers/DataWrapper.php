<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\SolusAPI\Helpers;

use WHMCS\Module\Server\SolusIoVps\Helpers\Arr;

/**
 * Class DataWrapper
 * @package WHMCS\Module\Server\SolusIoVps\SolusAPI\Helpers
 */
final class DataWrapper
{
    /**
     * @param array $response
     * @return array
     */
    public static function wrap(array $response): array
    {
        return Arr::get($response, 'data', []);
    }
}
