<?php

// Copyright 2021. Plesk International GmbH.

namespace Tests;

use Exception;
use Mockery;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ProjectResource;

/**
 * @runTestsInSeparateProcesses
 */
class ConnectionTest extends AbstractModuleTest
{
    private int $serverId = 1;
    private array $params;
    private Mockery\MockInterface $projectResource;

    protected function setUp(): void
    {
        parent::setUp();

        $this->params = [
            'serviceid' => 1,
        ];

        $connector = Mockery::mock('overload:' . Connector::class);
        $connector->shouldReceive('create')->andReturn(true);
        $this->projectResource = Mockery::mock('overload:' . ProjectResource::class);
    }

    public function testConnection(): void
    {
        $this->projectResource->shouldReceive('list')->andReturn(true);

        $result = call_user_func(self::getModuleFunction('TestConnection'), $this->params);

        $this->assertEquals(json_encode(['success' => true, 'error' => '']), json_encode($result));
    }

    public function testConnectionNegative(): void
    {
        $this->projectResource->shouldReceive('list')->andThrow(new Exception('bad request'));

        $result = call_user_func(self::getModuleFunction('TestConnection'), $this->params);

        $this->assertEquals(json_encode(['success' => false, 'error' => 'bad request']), json_encode($result));
    }
}
