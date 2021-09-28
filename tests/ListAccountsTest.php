<?php

// Copyright 2021. Plesk International GmbH.

namespace Tests;

use Mockery;
use WHMCS\Module\Server\SolusIoVps\Database\Models\Server;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ServerResource;

class ListAccountsTest extends AbstractModuleTest
{
    private int $serverId = 1;
    private array $params;
    private array $response;
    private Mockery\MockInterface $server;

    protected function setUp(): void
    {
        parent::setUp();

        $this->response = [
            [
                'name' => 'test.domain.ltd',
                'user' => [
                    'email' => 'email@mail.com',
                ],
                'plan' => [
                    'name' => 'basic',
                ],
                'ip_addresses' => [
                    'ipv4' => [
                        [
                            'ip' => '192.168.0.1'
                        ],
                    ],
                ],
                'created_at' => '2000-01-01 01:01:01',
                'is_suspended' => false,
            ],
        ];

        $this->params = [
            'serverid' => $this->serverId,
        ];

        Mockery::getConfiguration()->setConstantsMap([
            'WHMCS\Service\Status' => [
                'ACTIVE' => 'Active',
                'SUSPENDED' => 'Suspended',
            ],
        ]);

        $this->server = Mockery::mock('overload:' . Server::class);
        Mockery::mock('overload:WHMCS\Service\Status');
    }

    /**
     * @testWith [false, "Active"]
     *           [true, "Suspended"]
     */
    public function testListAccounts($suspended, $status): void
    {
        $response = $this->response;
        $response[0]['is_suspended'] = $suspended;
        $serverParams = [ 'params' => [] ];
        $this->server->shouldReceive('getParams')
            ->with($this->serverId)
            ->andReturn($serverParams);
        $connector = Mockery::mock('overload:' . Connector::class);
        $connector->shouldReceive('create')->with($serverParams)
            ->andReturn((object)[ 'server_id' => $this->serverId ]);
        $serverResource = Mockery::mock('overload:' . ServerResource::class);
        $serverResource->shouldReceive('list')->once()->andReturn($response);
        $carbon = Mockery::mock('overload:Carbon\Carbon');
        $carbon->shouldReceive('parse->format')->andReturn('2000-01-01 01:01:01');

        $result = call_user_func(self::getModuleFunction('ListAccounts'), $this->params);

        self::assertEquals([
            'success' => true,
            'accounts' => [
                [
                    'email' => 'email@mail.com',
                    'username' => 'email@mail.com',
                    'domain' => 'test.domain.ltd',
                    'uniqueIdentifier' => 'test.domain.ltd',
                    'product' =>'basic',
                    'primaryip' => '192.168.0.1',
                    'created' => '2000-01-01 01:01:01',
                    'status' => $status,
                ]
            ],
        ], $result);
    }

    public function testListAccountsNegative(): void
    {
        $this->server->shouldReceive('getParams')->andThrow(new \Exception('bad request'));

        $result = call_user_func(self::getModuleFunction('ListAccounts'), $this->params);

        self::assertEquals(false, $result['success']);
        self::assertEquals('bad request', $result['error']);
    }
}
