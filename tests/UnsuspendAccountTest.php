<?php

// Copyright 2021. Plesk International GmbH.

namespace Tests;

use Mockery;
use WHMCS\Module\Server\SolusIoVps\Database\Models\SolusServer;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ServerResource;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Language;

class UnsuspendAccountTest extends AbstractModuleTest
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

    public function testUnsuspendServerNotExist(): void
    {
        $this->solusServer->shouldReceive('getByServiceId')
            ->with($this->params['serviceid'])
            ->andReturn(null);
        $this->serverResource->shouldNotReceive('suspend');

        $result = call_user_func(self::getModuleFunction('UnsuspendAccount'), $this->params);

        self::assertEquals(Language::trans('solusiovps_error_server_not_found'), $result);
    }

    public function testUnsuspendExistingServer(): void
    {
        $this->solusServer->shouldReceive('getByServiceId')
            ->with($this->params['serviceid'])
            ->andReturn((object)['server_id' => $this->serverId]);
        $this->serverResource->shouldReceive('resume')->with($this->serverId);

        $result = call_user_func(self::getModuleFunction('UnsuspendAccount'), $this->params);

        self::assertEquals('success', $result);
    }
}
