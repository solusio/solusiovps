<?php
// Copyright 2021. Plesk International GmbH.

namespace Tests;

use Mockery;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Language;

class ConfigParamsTest extends AbstractModuleTest
{
    public function setUp(): void
    {
        parent::setUp();

        $connector = Mockery::mock('overload:WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector');
        $connector->shouldReceive('create')->andReturn(true);
    }

    public function planLoaderProvider(): array
    {
        $this->setUp();

        return [
            [
                [],
                [],
            ],
            [
                [
                    [ 'id' => 1, 'name' => 'plan1' ],
                    [ 'id' => 2, 'name' => 'plan2' ],
                ],
                [
                    1 => 'plan1',
                    2 => 'plan2',
                ]
            ],
        ];
    }

    /**
     * @dataProvider planLoaderProvider
     */
    public function testPlanLoader(array $response, array $expected): void
    {
        $func = self::getModuleFunction('PlanLoader');
        self::assertTrue(function_exists($func));

        $planResource = Mockery::mock('overload:WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\PlanResource');
        $planResource->shouldReceive('list')->once()->andReturn($response);

        $result = call_user_func($func, []);
        self::assertEquals($result, $expected);
    }

    public function osImageLoaderProvider(): array
    {
        $this->setUp();

        return [
            [
                [],
                [ 0 => Language::trans('solusiovps_config_option_none') ],
            ],
            [
                [
                    [
                        'icon' => [ 'name' => 'os1' ],
                        'versions' => [
                            [
                                'id' => 1,
                                'version' => 'version1',

                            ],
                            [
                                'id' => 2,
                                'version' => 'version2',
                            ],
                        ],
                    ],
                    [
                        'name' => 'os2',
                        'versions' => [
                            [
                                'id' => 3,
                                'version' => 'version3',

                            ],
                            [
                                'id' => 4,
                                'version' => 'version4',
                            ],
                        ],
                    ],
                ],
                [
                    0 => Language::trans('solusiovps_config_option_none'),
                    1 => 'os1 version1',
                    2 => 'os1 version2',
                    3 => 'os2 version3',
                    4 => 'os2 version4',
                ]
            ],
        ];
    }

    /**
     * @dataProvider osImageLoaderProvider
     */
    public function testOsImageLoader(array $response, array $expected): void
    {
        $func = self::getModuleFunction('OsImageLoader');
        self::assertTrue(function_exists($func));

        $osImageResource = Mockery::mock('overload:WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\OsImageResource');
        $osImageResource->shouldReceive('list')->once()->andReturn($response);

        $result = call_user_func($func, []);
        self::assertEquals($result, $expected);
    }

    public function locationLoaderProvider(): array
    {
        $this->setUp();

        return [
            [
                [],
                [],
            ],
            [
                [
                    [ 'id' => 1, 'name' => 'location1' ],
                    [ 'id' => 2, 'name' => 'location2' ],
                ],
                [
                    1 => 'location1',
                    2 => 'location2',
                ]
            ],
        ];
    }

    /**
     * @dataProvider planLoaderProvider
     */
    public function testLocationLoader(array $response, array $expected): void
    {
        $func = self::getModuleFunction('LocationLoader');
        self::assertTrue(function_exists($func));

        $planResource = Mockery::mock('overload:WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\LocationResource');
        $planResource->shouldReceive('list')->once()->andReturn($response);

        $result = call_user_func($func, []);
        self::assertEquals($result, $expected);
    }

    public function applicationLoaderProvider(): array
    {
        $this->setUp();

        return [
            [
                [],
                [ 0 => Language::trans('solusiovps_config_option_none') ],
            ],
            [
                [
                    [ 'id' => 1, 'name' => 'application1' ],
                    [ 'id' => 2, 'name' => 'application2' ],
                ],
                [
                    0 => Language::trans('solusiovps_config_option_none'),
                    1 => 'application1',
                    2 => 'application2',
                ]
            ],
        ];
    }

    /**
     * @dataProvider applicationLoaderProvider
     */
    public function testApplicationLoader(array $response, array $expected): void
    {
        $func = self::getModuleFunction('ApplicationLoader');
        self::assertTrue(function_exists($func));

        $planResource = Mockery::mock('overload:WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ApplicationResource');
        $planResource->shouldReceive('list')->once()->andReturn($response);

        $result = call_user_func($func, []);
        self::assertEquals($result, $expected);
    }

    public function roleLoaderProvider(): array
    {
        $this->setUp();

        return [
            [
                [],
                [ 0 => Language::trans('solusiovps_config_option_none') ],
            ],
            [
                [
                    [ 'id' => 1, 'name' => 'role1' ],
                    [ 'id' => 2, 'name' => 'role2' ],
                ],
                [
                    0 => Language::trans('solusiovps_config_option_none'),
                    1 => 'role1',
                    2 => 'role2',
                ]
            ],
        ];
    }

    /**
     * @dataProvider roleLoaderProvider
     */
    public function testRoleLoader(array $response, array $expected): void
    {
        $func = self::getModuleFunction('RoleLoader');
        self::assertTrue(function_exists($func));

        $planResource = Mockery::mock('overload:WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\RoleResource');
        $planResource->shouldReceive('list')->once()->andReturn($response);

        $result = call_user_func($func, []);
        self::assertEquals($result, $expected);
    }

    public function limitGroupLoaderProvider(): array
    {
        $this->setUp();

        return [
            [
                [],
                [ 0 => Language::trans('solusiovps_config_option_none') ],
            ],
            [
                [
                    [ 'id' => 1, 'name' => 'application1' ],
                    [ 'id' => 2, 'name' => 'application2' ],
                ],
                [
                    0 => Language::trans('solusiovps_config_option_none'),
                    1 => 'application1',
                    2 => 'application2',
                ]
            ],
        ];
    }

    /**
     * @dataProvider limitGroupLoaderProvider
     */
    public function testLimitGroupLoader(array $response, array $expected): void
    {
        $func = self::getModuleFunction('LimitGroupLoader');
        self::assertTrue(function_exists($func));

        $planResource = Mockery::mock('overload:WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\LimitGroupResource');
        $planResource->shouldReceive('list')->once()->andReturn($response);

        $result = call_user_func($func, []);
        self::assertEquals($result, $expected);
    }
}
