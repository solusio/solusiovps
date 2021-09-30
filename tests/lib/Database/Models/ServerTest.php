<?php
// Copyright 2021. Plesk International GmbH.

namespace Tests\lib\Database\Models;

use Mockery;
use PHPUnit\Framework\TestCase;
use WHMCS\Module\Server\SolusIoVps\Database\Models\Server;

/**
 * @runTestsInSeparateProcesses
 */
class ServerTest extends TestCase
{
    private Mockery\MockInterface $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = Mockery::mock('overload:WHMCS\Database\Capsule');
    }

    /**
     * @testWith ["on", "https"]
     *           ["off", "http"]
     */
    public function testGetParams(string $secure, string $schema): void
    {
        $serverId = 1;
        $domain = 'test@domain.ltd';
        $password = 'mypass';
        $this->db->shouldReceive('table')->with(Server::TABLE)->andReturnSelf();
        $this->db->shouldReceive('where')->with(['id' => $serverId])->andReturnSelf();
        $this->db->shouldReceive('first')->andReturn((object)[
            'secure' => $secure,
            'hostname' => $domain,
            'password' => $password,
        ]);

        $result = Server::getParams($serverId);

        self::assertEquals([
            'serverhttpprefix' => $schema,
            'serverhostname' => $domain,
            'serverpassword' => $password,
        ], $result);
    }
}
