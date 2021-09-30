<?php
// Copyright 2021. Plesk International GmbH.

namespace Tests\lib\WhmcsAPI;

use Mockery;
use Tests\AbstractModuleTest;
use WHMCS\Module\Server\SolusIoVps\Database\Models\Server;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ProjectResource;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Servers;

/**
 * @runTestsInSeparateProcesses
 */
class ServersTest extends AbstractModuleTest
{
    private int $serverId = 1;
    private Mockery\MockInterface $projectResource;
    private Mockery\MockInterface $server;

    protected function setUp(): void
    {
        parent::setUp();

        $connector = Mockery::mock('overload:' . Connector::class);
        $connector->shouldReceive('create')->andReturn(true);
        $this->projectResource = Mockery::mock('overload:' . ProjectResource::class);
        $this->server = Mockery::mock('overload:' . Server::class);
    }

    public function testGetValidParams(): void
    {
        $params = [ 'params' => [] ];
        $this->server->shouldReceive('getModuleServers')->andReturn([ $this->serverId ]);
        $this->server->shouldReceive('getParams')
            ->with($this->serverId)
            ->andReturn($params);

        $this->projectResource->shouldReceive('list')->andReturn(true);

        self::assertEquals($params, Servers::getValidParams());
    }

    public function testGetValidParamsNoServers(): void
    {
        $this->server->shouldReceive('getModuleServers')->andReturn([]);

        self::assertEmpty(Servers::getValidParams());
    }
}
