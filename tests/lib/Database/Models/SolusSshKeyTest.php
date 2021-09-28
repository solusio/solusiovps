<?php
// Copyright 2021. Plesk International GmbH.

namespace Tests\lib\Database\Models;

use Mockery;
use PHPUnit\Framework\TestCase;
use WHMCS\Module\Server\SolusIoVps\Database\Models\SolusSshKey;

/**
 * @runTestsInSeparateProcesses
 */
class SolusSshKeyTest extends TestCase
{
    private string $key = 'mykey';
    private Mockery\MockInterface $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = Mockery::mock('overload:WHMCS\Database\Capsule');
    }

    public function getIdByKeyDataProvider(): array
    {
        return [
            [null, 0],
            [(object)[ "solus_key_id" => 1 ], 1],
        ];
    }

    /**
     * @dataProvider getIdByKeyDataProvider
     */
    public function testGetIdByKey(?object $row, int $result): void
    {
        $this->db->shouldReceive('table')->with(SolusSshKey::TABLE)->andReturnSelf();
        $this->db->shouldReceive('where')->with(['key_hash' => sha1($this->key)])->andReturnSelf();
        $this->db->shouldReceive('first')->andReturn($row);

        self::assertEquals($result, SolusSshKey::getIdByKey($this->key));
    }

    public function testGetKeyHash(): void
    {
        self::assertEquals(sha1($this->key), SolusSshKey::getKeyHash($this->key));
    }
}
