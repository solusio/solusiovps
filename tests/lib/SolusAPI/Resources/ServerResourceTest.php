<?php
// Copyright 1999-2024. WebPros International GmbH. All rights reserved.

namespace Tests\lib\SolusAPI\Resources;

use Mockery;
use PHPUnit\Framework\TestCase;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ServerResource;

/**
 * @runTestsInSeparateProcesses
 */
class ServerResourceTest extends TestCase
{
    public function testChangeHostname(): void
    {
        $id = 1;
        $hostname = 'test.domain.tld';

        $serverResource = Mockery::mock(ServerResource::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $serverResource->shouldReceive('processResponse')->andReturn([]);
        $serverResource->shouldReceive('getConnector->patch')->with(
            "servers/{$id}",
            [
                'json' => [
                    'name' => $hostname,
                    'fqdns' => [ $hostname ],
                ],
            ],
        );

        $this->addToAssertionCount(
            Mockery::getContainer()->mockery_getExpectationCount()
        );

        $serverResource->changeHostname($id, $hostname);
    }
}
