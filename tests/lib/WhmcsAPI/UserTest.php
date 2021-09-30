<?php
// Copyright 2021. Plesk International GmbH.

namespace Tests\lib\WhmcsAPI;

use Mockery;
use Tests\AbstractModuleTest;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Requests\UserRequestBuilder;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\UserResource;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\User;

/**
 * @runTestsInSeparateProcesses
 */
class UserTest extends AbstractModuleTest
{
    private array $params;
    private int $solusUserId = 1;
    private Mockery\MockInterface $userResource;
    private UserRequestBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->params = [
            'status' => 'Pending',
            'password' => 'test_pass',
            'serviceid' => '1',
            'userid' => '1',
            'clientsdetails' => [
                'email' => 'test_email',
            ],
            // plan id
            'configoption1' => '1',
            // location id if no location in configoptions
            'configoption2' => '1',
            // app id
            'configoption4' => '1',
            // is backups enabled
            'configoption6' => 'off',
            // role id
            'configoption7' => '1',
            // limit group id
            'configoption8' => '1',
            'configoptions' => [
                'Location' => '1'
            ],
            'domain' => 'test.domain.ltd',
            'customfields' => [
                'SSH Key' => 'key',
                ''
            ],
        ];

        $connector = Mockery::mock('GuzzleHttp\ClientInterface');
        $connector->shouldReceive('create')->andReturn(true);
        $this->userResource = Mockery::mock(UserResource::class, [ $connector ]);
        $this->builder = UserRequestBuilder::fromWHMCSCreateAccountParams($this->params);
    }

    public function testSyncWithSolusUserCreate(): void
    {
        $this->userResource->shouldReceive('getUserByEmail')
            ->with($this->params['clientsdetails']['email'])
            ->once()
            ->andReturn([]);
        $this->userResource->shouldReceive('create')
            ->with($this->builder->getCreateRequest())
            ->andReturn($this->solusUserId);

        $this->addToAssertionCount(
            Mockery::getContainer()->mockery_getExpectationCount()
        );

        self::assertEquals($this->solusUserId, User::syncWithSolusUser($this->userResource, $this->builder));
    }

    public function testSyncWithSolusUserUpdate(): void
    {
        $this->userResource->shouldReceive('getUserByEmail')
            ->with($this->params['clientsdetails']['email'])
            ->once()
            ->andReturn([
                'id' => $this->solusUserId,
                'billing_user_id' => 2,
            ]);
        $this->userResource->shouldReceive('updateUser')
            ->with($this->solusUserId, $this->builder->getUpdateRequest());

        $this->addToAssertionCount(
            Mockery::getContainer()->mockery_getExpectationCount()
        );

        self::assertEquals($this->solusUserId, User::syncWithSolusUser($this->userResource, $this->builder));
    }


    public function testSyncWithSolusUserWithoutUpdate(): void
    {
        $this->userResource->shouldReceive('getUserByEmail')
            ->with($this->params['clientsdetails']['email'])
            ->once()
            ->andReturn([
                'id' => $this->solusUserId,
                'billing_user_id' => $this->params['userid'],
            ]);
        $this->userResource->shouldNotReceive('updateUser');

        $this->addToAssertionCount(
            Mockery::getContainer()->mockery_getExpectationCount()
        );

        self::assertEquals($this->solusUserId, User::syncWithSolusUser($this->userResource, $this->builder));
    }
}
