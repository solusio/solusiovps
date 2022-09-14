<?php

// Copyright 2021. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\SolusAPI\Requests;

use WHMCS\Module\Server\SolusIoVps\Helpers\Arr;

class ConfigOptionExtractor
{
    public static function extractFromModuleParams(array $moduleParams, string $optionName)
    {
        return Arr::get($moduleParams, sprintf('configoptions.%s', $optionName));
    }
}