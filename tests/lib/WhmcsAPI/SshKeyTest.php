<?php
// Copyright 2021. Plesk International GmbH.

namespace Tests\lib\WhmcsAPI;

use Mockery;
use Tests\AbstractModuleTest;
use WHMCS\Module\Server\SolusIoVps\Database\Models\SolusSshKey;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\SshKeyResource;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\SshKey;

class SshKeyTest extends AbstractModuleTest
{
    private int $sshKeyId = 1;
    private int $solusUserId = 1;
    private string $sshKey = 'ssh_key';
    private Mockery\MockInterface $solusSshKey;
    private Mockery\MockInterface $sshKeyResource;

    protected function setUp(): void
    {
        parent::setUp();

        $this->solusSshKey = Mockery::mock('overload:' . SolusSshKey::class);
        $connector = Mockery::mock('overload:' . Connector::class);
        $connector->shouldReceive('create')->andReturn(true);
        $this->sshKeyResource = Mockery::mock('overload:' . SshKeyResource::class);
    }

    public function testCreateKeyExist(): void
    {
        $this->solusSshKey->shouldReceive('getIdByKey')->andReturn($this->sshKeyId);
        self::assertEquals($this->sshKeyId, SshKey::create([], $this->sshKey, $this->solusUserId));
    }

    public function testCreateKeyNotExist(): void
    {
        $hashedKey = 'hashed_key';
        $this->solusSshKey->shouldReceive('getIdByKey')->andReturn(0);
        $this->solusSshKey->shouldReceive('getKeyHash')->andReturn($hashedKey);
        $this->sshKeyResource->shouldReceive('create')
            ->with($hashedKey, $this->sshKey, $this->solusUserId)
            ->andReturn($this->sshKeyId);

        $this->solusSshKey->shouldReceive('create')->with([
            'solus_key_id' => $this->sshKeyId,
            'key_hash' => $hashedKey,
        ]);
        self::assertEquals($this->sshKeyId, SshKey::create([], $this->sshKey, $this->solusUserId));
    }
}
