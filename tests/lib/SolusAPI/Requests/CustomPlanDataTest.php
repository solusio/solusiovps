<?php

// Copyright 2021. Plesk International GmbH.

namespace Tests\lib\SolusAPI\Requests;

use PHPUnit\Framework\TestCase;
use WHMCS\Module\Server\SolusIoVps\Database\Models\ProductConfigOption;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Requests\CustomPlanData;

/**
 * @runTestsInSeparateProcesses
 */
class CustomPlanDataTest extends TestCase
{
    public function testFromModuleParams(): void
    {
        $moduleParams = [
            'configoptions' => [
                ProductConfigOption::VCPU  => 1,
                ProductConfigOption::MEMORY  => 2,
                ProductConfigOption::DISK_SPACE  => 2,
                ProductConfigOption::VCPU_UNITS  => 8,
                ProductConfigOption::VCPU_LIMIT  => 10,
                ProductConfigOption::IO_PRIORITY  => 6,
                ProductConfigOption::SWAP  => 4,
                ProductConfigOption::TOTAL_TRAFFIC_LIMIT_MONTHLY  => 5,
            ],
        ];

        self::assertEquals([
            'params' => [
                'vcpu' => $moduleParams['configoptions'][ProductConfigOption::VCPU],
                'ram' => $moduleParams['configoptions'][ProductConfigOption::MEMORY] * 1024 * 1024,
                'disk' => $moduleParams['configoptions'][ProductConfigOption::DISK_SPACE],
                'vcpu_units' => $moduleParams['configoptions'][ProductConfigOption::VCPU_UNITS],
                'vcpu_limit' => $moduleParams['configoptions'][ProductConfigOption::VCPU_LIMIT],
                'io_priority' => $moduleParams['configoptions'][ProductConfigOption::IO_PRIORITY],
                'swap' => $moduleParams['configoptions'][ProductConfigOption::SWAP] * 1024 * 1024,
            ],
            'limits' => [
                'network_total_traffic' => [
                    'limit' => $moduleParams['configoptions'][ProductConfigOption::TOTAL_TRAFFIC_LIMIT_MONTHLY],
                ],
            ],
        ], CustomPlanData::fromModuleParams($moduleParams));
    }

    public function testFromModuleParamsEmptyConfigOptions(): void
    {
        self::assertEquals(null, CustomPlanData::fromModuleParams([
            'configoptions' => [],
        ]));
    }

    public function testFromModuleParamsNoConfigOptions(): void
    {
        self::assertEquals(null, CustomPlanData::fromModuleParams([]));
    }

    public function testFromModuleParamsSomeValues(): void
    {
        $moduleParams = [
            'configoptions' => [
                ProductConfigOption::VCPU  => 1,
                ProductConfigOption::TOTAL_TRAFFIC_LIMIT_MONTHLY  => 5,
            ],
        ];

        self::assertEquals([
            'params' => [
                'vcpu' => $moduleParams['configoptions'][ProductConfigOption::VCPU],
            ],
            'limits' => [
                'network_total_traffic' => [
                    'limit' => $moduleParams['configoptions'][ProductConfigOption::TOTAL_TRAFFIC_LIMIT_MONTHLY],
                ],
            ],
        ], CustomPlanData::fromModuleParams($moduleParams));
    }
}