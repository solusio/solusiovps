<?php
// Copyright 1999-2024. WebPros International GmbH. All rights reserved.

namespace Tests;

use Mockery;
use WHMCS\Module\Server\SolusIoVps\Database\Models\SolusServer;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ServerResource;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Language;

class TerminateAccountTest extends AbstractModuleTest
{
    private int $serverId = 1;
    private array $params;
    private Mockery\MockInterface $serverResource;
    private Mockery\MockInterface $solusServer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->params = [
            'serviceid' => 1,
        ];

        $connector = Mockery::mock('overload:' . Connector::class);
        $connector->shouldReceive('create')->andReturn(true);
        $this->serverResource = Mockery::mock('overload:' . ServerResource::class);
        $this->solusServer = Mockery::mock('overload:' . SolusServer::class);
    }

    public function testDeleteServerNotExist(): void
    {
        $this->solusServer->shouldReceive('getByServiceId')
            ->with($this->params['serviceid'])
            ->andReturn(null);
        $this->serverResource->shouldNotReceive('delete');

        $result = call_user_func(self::getModuleFunction('TerminateAccount'), $this->params);

        self::assertEquals(Language::trans('solusiovps_error_server_not_found'), $result);
    }

    public function testDeleteExistingServer(): void
    {
        $this->solusServer->shouldReceive('getByServiceId')
            ->with($this->params['serviceid'])
            ->andReturn((object) [ 'server_id' => $this->serverId ]);
        $this->serverResource->shouldReceive('delete')->with($this->serverId);

        $this->solusServer->shouldReceive('deleteByServerId')->with($this->serverId);

        $result = call_user_func(self::getModuleFunction('TerminateAccount'), $this->params);

        self::assertEquals('success', $result);
    }
}
