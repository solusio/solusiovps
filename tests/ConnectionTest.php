<?php

// Copyright 2021. Plesk International GmbH.

namespace Tests;

use Exception;
use Mockery;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ProjectResource;

class ConnectionTest extends AbstractModuleTest
{
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

        self::assertEquals(['success' => true, 'error' => ''], $result);
    }

    public function testConnectionNegative(): void
    {
        $this->projectResource->shouldReceive('list')->andThrow(new Exception('bad request'));

        $result = call_user_func(self::getModuleFunction('TestConnection'), $this->params);

        self::assertEquals(['success' => false, 'error' => 'bad request'], $result);
    }
}
