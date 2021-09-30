<?php

// Copyright 2021. Plesk International GmbH.

namespace Tests;

use Mockery;
use WHMCS\Module\Server\SolusIoVps\Database\Models\Hosting;
use WHMCS\Module\Server\SolusIoVps\Database\Models\Server;
use WHMCS\Module\Server\SolusIoVps\Database\Models\SolusServer;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ServerResource;

class RestartTest extends AbstractModuleTest
{
    private int $serverId = 1;
    private array $params;
    private Mockery\MockInterface $hosting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->params = [
            'serviceid' => 1,
        ];

        $this->hosting = Mockery::mock('overload:' . Hosting::class);
    }

    public function testRestart(): void
    {
        $serverParams = [ 'params' => [] ];
        $this->hosting->shouldReceive('getByServiceId')
            ->andReturn((object)[ 'server' => $this->serverId ]);
        $solusServer = Mockery::mock('overload:' . SolusServer::class);
        $solusServer->shouldReceive('getByServiceId')
            ->andReturn((object)[ 'server_id' => $this->serverId ]);
        $server = Mockery::mock('overload:' . Server::class);
        $server->shouldReceive('getParams')->with($this->serverId)
            ->andReturn($serverParams);
        $connector = Mockery::mock('overload:' . Connector::class);
        $connector->shouldReceive('create')->with($serverParams);
        $serverResource = Mockery::mock('overload:' . ServerResource::class);
        $serverResource->shouldReceive('restart');

        self::assertEquals(
            'success',
            call_user_func(self::getModuleFunction('restart'), $this->params)
        );
    }

    public function testRestartNegative(): void
    {
        $this->hosting->shouldReceive('getByServiceId')->andThrow(new \Exception('bad request'));

        self::assertEquals(
            'bad request',
            call_user_func(self::getModuleFunction('restart'), $this->params)
        );
    }
}
