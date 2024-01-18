<?php

// Copyright 1999-2024. WebPros International GmbH. All rights reserved.

namespace WHMCS\Module\Server\SolusIoVps\SolusAPI\Requests;

use WHMCS\Module\Server\SolusIoVps\Helpers\Arr;

class ConfigOptionExtractor
{
    public static function extractFromModuleParams(array $moduleParams, string $optionName)
    {
        return Arr::get($moduleParams, sprintf('configoptions.%s', $optionName));
    }
}