<?php

// Copyright 2021. Plesk International GmbH.

namespace Tests;

use Mockery;
use WHMCS\Module\Server\SolusIoVps\Database\Models\Server;
use WHMCS\Module\Server\SolusIoVps\Database\Models\SolusServer;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ServerResource;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\UserResource;

class SyncAccountTest extends AbstractModuleTest
{
    private int $userId = 1;
    private array $params;
    private Mockery\MockInterface $solusServer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->params = [
            'domain' => 'test.domain.ltd',
            'serviceid' => 1,
            'serverid' => 2,
            'clientsdetails' => [
                'email' => 'user@email.com',
            ],
        ];

        $this->solusServer = Mockery::mock('overload:' . SolusServer::class);
    }

    public function testSyncAccount(): void
    {
        $serverParams = [ 'params' => [] ];
        $conn = [ 'connector' => [] ];
        $this->solusServer->shouldReceive('getByServiceId')->andReturn(null);

        $server = Mockery::mock('overload:' . Server::class);
        $server->shouldReceive('getParams')->with($this->params['serverid'])->andReturn($serverParams);
        $connector = Mockery::mock('overload:' . Connector::class);
        $connector->shouldReceive('create')->with($serverParams)->andReturn($conn);
        $userResource = Mockery::mock('overload:' . UserResource::class);
        $userResource->shouldReceive('getUserByEmail')
            ->with($this->params['clientsdetails']['email'])
            ->andReturn([ 'id' => $this->userId ]);

        $serverReponse = [
            'id' => 2,
            'name' => $this->params['domain'],
        ];
        $serverResource = Mockery::mock('overload:' . ServerResource::class);
        $serverResource->shouldReceive('getAllByUser')
            ->with($this->userId)
            ->andReturn([
                $serverReponse
            ]);
        $this->solusServer->shouldReceive('create')->with([
            'service_id' => $this->params['serviceid'],
            'server_id' => $this->params['serverid'],
            'payload' => json_encode($serverReponse),
        ]);

        $result = call_user_func(self::getModuleFunction('syncAccount'), $this->params);
        self::assertEquals(
            'Account has been synced correctly',
            $result['success']
        );
    }

    public function testSyncAccountNegative(): void
    {
        $this->solusServer->shouldReceive('getByServiceId')->andThrow(new \Exception('bad request'));

        $result = call_user_func(self::getModuleFunction('syncAccount'), $this->params);

        self::assertEquals(false, $result['success']);
        self::assertEquals('bad request', $result['error']);
    }
}
