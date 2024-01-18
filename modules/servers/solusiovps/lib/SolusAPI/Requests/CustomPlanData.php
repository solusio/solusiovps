<?php

// Copyright 1999-2024. WebPros International GmbH. All rights reserved.

namespace WHMCS\Module\Server\SolusIoVps\SolusAPI\Requests;

use WHMCS\Module\Server\SolusIoVps\Database\Models\ProductConfigOption;

class CustomPlanData
{
    public static function fromModuleParams(array $moduleParams): ?array
    {
        $customPlanData = [];

        if ($vcpu = ConfigOptionExtractor::extractFromModuleParams($moduleParams, ProductConfigOption::VCPU)) {
            $customPlanData['params']['vcpu'] = (int)$vcpu;
        }
        if ($ram = ConfigOptionExtractor::extractFromModuleParams($moduleParams, ProductConfigOption::MEMORY)) {
            $customPlanData['params']['ram'] = $ram * 1024 * 1024;
        }
        if ($disk = ConfigOptionExtractor::extractFromModuleParams($moduleParams, ProductConfigOption::DISK_SPACE)) {
            $customPlanData['params']['disk'] = (int)$disk;
        }
        if ($vcpuUnits = ConfigOptionExtractor::extractFromModuleParams($moduleParams, ProductConfigOption::VCPU_UNITS)) {
            $customPlanData['params']['vcpu_units'] = (int)$vcpuUnits;
        }
        if ($vcpuLimit = ConfigOptionExtractor::extractFromModuleParams($moduleParams, ProductConfigOption::VCPU_LIMIT)) {
            $customPlanData['params']['vcpu_limit'] = (int)$vcpuLimit;
        }
        if ($ioPriority = ConfigOptionExtractor::extractFromModuleParams($moduleParams, ProductConfigOption::IO_PRIORITY)) {
            $customPlanData['params']['io_priority'] = (int)$ioPriority;
        }
        if ($swap = ConfigOptionExtractor::extractFromModuleParams($moduleParams, ProductConfigOption::SWAP)) {
            $customPlanData['params']['swap'] = $swap * 1024 * 1024;
        }
        if ($totalTrafficLimitMonthly =
            ConfigOptionExtractor::extractFromModuleParams($moduleParams, ProductConfigOption::TOTAL_TRAFFIC_LIMIT_MONTHLY)
        ) {
            $customPlanData['limits']['network_total_traffic'] = [
                'limit' => (int)$totalTrafficLimitMonthly,
            ];
        }

        return count($customPlanData) > 0 ? $customPlanData : null;
    }
}