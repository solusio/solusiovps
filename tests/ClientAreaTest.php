<?php

// Copyright 2021. Plesk International GmbH.

namespace Tests;

use Mockery;
use WHMCS\Module\Server\SolusIoVps\Database\Models\ProductConfigOption;
use WHMCS\Module\Server\SolusIoVps\Database\Models\SolusServer;
use WHMCS\Module\Server\SolusIoVps\Helpers\Unit;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ApplicationResource;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ServerResource;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Language;

class ClientAreaTest extends AbstractModuleTest
{
    private int $serverId = 1;
    private array $params;
    private Mockery\MockInterface $serverResource;
    private Mockery\MockInterface $solusServer;
    private Mockery\MockInterface $applicationResource;
    private Mockery\MockInterface $productConfigOption;

    protected function setUp(): void
    {+
        parent::setUp();

        $this->params = [
            'domain' => 'test.domain.ltd',
            'serviceid' => '1',
            'pid' => '1',
            'status' => 'Pending',
            // default operating system id
            'configoption3' => '1',
            // default application id
            'configoption4' => '2',
        ];

        Mockery::getConfiguration()->setConstantsMap([
            'WHMCS\Module\Server\SolusIoVps\Database\Models\ProductConfigOption' => [
                'OPERATING_SYSTEM' => 'Operating System',
                'APPLICATION' => 'Application',
            ],
        ]);

        $connector = Mockery::mock('overload:' . Connector::class);
        $connector->shouldReceive('create')->andReturn(true);
        $this->serverResource = Mockery::mock('overload:' . ServerResource::class);
        $this->productConfigOption = Mockery::mock('overload:' . ProductConfigOption::class);
        $this->solusServer = Mockery::mock('overload:' . SolusServer::class);
        $this->solusServer->shouldReceive('getByServiceId')->once()->andReturn(true);
        $this->applicationResource = Mockery::mock('overload:' . ApplicationResource::class);
    }

    public function loadServerPageNegativeDataProvider(): array
    {
        $this->setUp();

        return [
            [
                'Active',
                'solusiovps_exception_page_default_title',
                'solusiovps_exception_page_default_message',
            ],
            [
                'Pending',
                'solusiovps_exception_page_pending_title',
                'solusiovps_exception_page_pending_message',
            ],
            [
                'Cancelled',
                'solusiovps_exception_page_cancelled_title',
                'solusiovps_exception_page_cancelled_message',
            ]
        ];
    }

    /**
     * @dataProvider loadServerPageNegativeDataProvider
     */
    public function testLoadServerPageServerNegative(string $status, string $title, string $message): void
    {
        $params = $this->params;
        $params['status'] = $status;
        $this->solusServer->shouldReceive('getByServiceId')->andReturn(null);

        $result = call_user_func(self::getModuleFunction('ClientArea'), $params);

        self::assertEquals($result, [
            'tabOverviewReplacementTemplate' => 'error.tpl',
            'templateVariables' => [
                'title' => Language::trans($title),
                'message' => Language::trans($message),
            ],
        ]);
    }

    /**
     * @dataProvider loadServerPageServerDataProvider
     */
    public function testLoadServerPageServer(bool $isEnabledTrafficLimit, ?int $expectedTrafficLimit): void
    {
        $this->solusServer->shouldReceive('getByServiceId')
            ->with((int)$this->params['serviceid'])
            ->andReturn((object)[ 'server_id' => $this->serverId ]);
        $this->serverResource->shouldReceive('get')
            ->with($this->serverId)
            ->andReturn([
               'data' => [
                   'boot_mode' => 'resque',
                   'status' => 'running',
                   'ip_addresses' => [
                       'ipv4' => [
                           0 => [
                               'ip' => '192.168.0.1',
                           ],
                       ],
                   ],
                   'usage' => [
                       'network' => [
                           'incoming' => [
                               'value' => 1024*1024*1024 / 2 - 1,
                           ],
                           'outgoing' => [
                               'value' => 1024*1024*1024 / 2 - 42,
                           ]
                       ],
                   ],
                   'plan' => [
                       'limits' => [
                           'network_total_traffic' => [
                               'is_enabled' => $isEnabledTrafficLimit,
                               'limit' => 1,
                               'unit' => Unit::GiB,
                           ],
                       ],
                   ],
               ],
            ]);
        $this->productConfigOption->shouldReceive('getProductOptions')
            ->with($this->params['pid'], 'Operating System')
            ->andReturn([ 'product' => [ 'option1', 'option2' ] ]);
        $this->productConfigOption->shouldReceive('getProductOptions')
            ->with($this->params['pid'], 'Application')
            ->andReturn([[
                'id' => 2,
                'name' => 'Application 2',
            ]]);
        $this->applicationResource->shouldReceive('list')->andReturn([
            [
                'id' => 1,
                'name' => 'Application 1',
                'json_schema' => json_encode([
                    'foo1' => 'bar1',
                    'required' => ['foo1'],
                    'properties' => [
                        'foo1' => [
                            'type' => 'string',
                        ],
                    ],
                ]),
            ],
            [
                'id' => 2,
                'name' => 'Application 2',
                'json_schema' => json_encode([
                    'foo2' => 'bar2',
                    'required' => ['foo2'],
                    'properties' => [
                        'foo2' => [
                            'type' => 'string',
                        ],
                    ],
                ]),
            ],
        ]);

        $result = call_user_func(self::getModuleFunction('ClientArea'), $this->params);

        self::assertEquals($result, [
            'tabOverviewReplacementTemplate' => 'templates/overview.tpl',
            'templateVariables' => [
                'data' => [
                    'ip' => '192.168.0.1',
                    'status' => 'running',
                    'operating_systems' => json_encode([ 'product' => [ 'option1', 'option2' ] ]),
                    'applications' => json_encode([2 => [
                        'name' => 'Application 2',
                        'schema' => [
                            'foo2' => 'bar2',
                            'required' => ['foo2'],
                            'properties' => [
                                'foo2' => [
                                    'type' => 'string',
                                    'required' => true,
                                ],
                            ],
                        ],
                    ]]),
                    'default_os_id' => 1,
                    'default_application_id' => 2,
                    'domain' => 'test.domain.ltd',
                    'boot_mode' => 'resque',
                    'traffic_current' => 0.99,
                    'traffic_limit' => $expectedTrafficLimit,
                    'traffic_unit' => Unit::GiB,
                ],
            ],
        ]);
    }

    public function loadServerPageServerDataProvider(): array
    {
        return [
            'enabled traffic limit' => [true, 1],
            'disabled traffic limit' => [false, null],
        ];
    }
}
