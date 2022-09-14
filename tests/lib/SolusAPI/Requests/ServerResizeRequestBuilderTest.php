<?php

// Copyright 2021. Plesk International GmbH.

namespace Tests\lib\SolusAPI\Requests;

use PHPUnit\Framework\TestCase;
use WHMCS\Module\Server\SolusIoVps\Database\Models\ProductConfigOption;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Requests\ServerResizeRequestBuilder;

/**
 * @runTestsInSeparateProcesses
 */
class ServerResizeRequestBuilderTest extends TestCase
{
    public function testBuilderWithConfigOptionsAndCanUseCustomPlan(): void
    {
        $params = [
            // plan id
            'configoption1' => 42,
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

        $builder = ServerResizeRequestBuilder::fromWHMCSUpgradeDowngradeParams($params);

        self::assertEquals([
            'plan_id' => $params['configoption1'],
            'preserve_disk' => false,
            'custom_plan' => [
                'params' => [
                    'vcpu' => $params['configoptions'][ProductConfigOption::VCPU],
                    'ram' => $params['configoptions'][ProductConfigOption::MEMORY] * 1024 * 1024,
                    'disk' => $params['configoptions'][ProductConfigOption::DISK_SPACE],
                    'vcpu_units' => $params['configoptions'][ProductConfigOption::VCPU_UNITS],
                    'vcpu_limit' => $params['configoptions'][ProductConfigOption::VCPU_LIMIT],
                    'io_priority' => $params['configoptions'][ProductConfigOption::IO_PRIORITY],
                    'swap' => $params['configoptions'][ProductConfigOption::SWAP] * 1024 * 1024,
                ],
                'limits' => [
                    'network_total_traffic' => [
                        'limit' => $params['configoptions'][ProductConfigOption::TOTAL_TRAFFIC_LIMIT_MONTHLY],
                    ],
                ],
            ],
        ], $builder->get());
    }

    public function testBuilderWithConfigOptionsWithoutDiskAndCanUseCustomPlan(): void
    {
        $params = [
            // plan id
            'configoption1' => 42,
            'configoptions' => [
                ProductConfigOption::VCPU  => 1,
            ],
        ];

        $builder = ServerResizeRequestBuilder::fromWHMCSUpgradeDowngradeParams($params);

        self::assertEquals([
            'plan_id' => $params['configoption1'],
            'preserve_disk' => true,
            'custom_plan' => [
                'params' => [
                    'vcpu' => $params['configoptions'][ProductConfigOption::VCPU],
                ],
            ],
        ], $builder->get());
    }
}