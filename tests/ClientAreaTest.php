<?php

// Copyright 2021. Plesk International GmbH.

namespace Tests;

use Mockery;
use WHMCS\Module\Server\SolusIoVps\Database\Models\ProductConfigOption;
use WHMCS\Module\Server\SolusIoVps\Database\Models\SolusServer;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ServerResource;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Language;

class ClientAreaTest extends AbstractModuleTest
{
    private int $serverId = 1;
    private array $params;
    private Mockery\MockInterface $serverResource;
    private Mockery\MockInterface $solusServer;
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
        ];

        Mockery::getConfiguration()->setConstantsMap([
            'WHMCS\Module\Server\SolusIoVps\Database\Models\ProductConfigOption' => [
                'OPERATING_SYSTEM' => 'Operating System',
            ],
        ]);

        $connector = Mockery::mock('overload:' . Connector::class);
        $connector->shouldReceive('create')->andReturn(true);
        $this->serverResource = Mockery::mock('overload:' . ServerResource::class);
        $this->productConfigOption = Mockery::mock('overload:' . ProductConfigOption::class);
        $this->solusServer = Mockery::mock('overload:' . SolusServer::class);
        $this->solusServer->shouldReceive('getByServiceId')->once()->andReturn(true);
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

    public function testLoadServerPageServer(): void
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
               ],
            ]);
        $this->productConfigOption->shouldReceive('getProductOptions')
            ->with($this->params['pid'], 'Operating System')
            ->andReturn([ 'product' => [ 'option1', 'option2' ] ]);

        $result = call_user_func(self::getModuleFunction('ClientArea'), $this->params);

        self::assertEquals($result, [
            'tabOverviewReplacementTemplate' => 'templates/overview.tpl',
            'templateVariables' => [
                'data' => [
                    'ip' => '192.168.0.1',
                    'status' => 'running',
                    'operating_systems' => json_encode([ 'product' => [ 'option1', 'option2' ] ]),
                    'default_os_id' => 1,
                    'domain' => 'test.domain.ltd',
                    'boot_mode' => 'resque',
                ],
            ],
        ]);
    }
}
