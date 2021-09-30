<?php
// Copyright 2021. Plesk International GmbH.

namespace Tests;

use Mockery;
use WHMCS\Module\Server\SolusIoVps\Database\Models\Hosting;
use WHMCS\Module\Server\SolusIoVps\Database\Models\ProductConfigOption;
use WHMCS\Module\Server\SolusIoVps\Database\Models\SolusServer;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Helpers\Strings;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Requests\ServerCreateRequestBuilder;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ServerResource;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\UserResource;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Crypt;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Language;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\SshKey;

/**
 * @runTestsInSeparateProcesses
 */
class CreateAccountTest extends AbstractModuleTest
{
    private array $params;
    private int $solusUserId = 1;
    private int $sshKeyId = 1;
    private array $existingSolusUser;

    private Mockery\MockInterface $userResource;
    private Mockery\MockInterface $connector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->params = [
            'status' => 'Pending',
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
            // os id
            'configoption5' => '1',
            // is backups enabled
            'configoption6' => 'off',
            // role id
            'configoption7' => '1',
            // limit group id
            'configoption8' => '1',
            'configoptions' => [
                ProductConfigOption::LOCATION => '1'
            ],
            'domain' => 'test.domain.ltd',
            'customfields' => [
                'SSH Key' => 'key',
                ''
            ],
        ];

        $this->existingSolusUser = [
            'id' => $this->solusUserId,
            'email' => $this->params['clientsdetails']['email'],
            'billing_user_id' => null,
            'status' => 'active',
            'roles' => [
                [ 'id' => '1'],
                [ 'id' => '2'],
            ],
        ];

        Mockery::getConfiguration()->setConstantsMap([
            'WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\UserResource' => [
                'STATUS_ACTIVE' => 'active',
            ],
            'WHMCS\Module\Server\SolusIoVps\Database\Models\Hosting' => [
                'STATUS_PENDING' => 'Pending',
            ],
        ]);
        $this->connector = Mockery::mock('overload:' . Connector::class);
        $this->connector->shouldReceive('create')->andReturn(true);
        $this->userResource = Mockery::mock('overload:' . UserResource::class);
    }

    public function testCreateAccountStatusNotPending(): void
    {
        $result = call_user_func(self::getModuleFunction('CreateAccount'), ['status' => 'Active']);
        self::assertEquals(Language::trans('solusiovps_error_server_already_created'), $result);
    }

    public function testCreateAccount(): void
    {
        $func = self::getModuleFunction('CreateAccount');
        self::assertTrue(function_exists($func));

        $params = $this->params;
        $password = 'pass';
        $cryptPass = 'crypt_pass';
        $params['password'] = $password;
        $key = 'ssh_key';

        $crypt = Mockery::mock('overload:' . Crypt::class);
        $crypt->shouldReceive('encrypt')->andReturn($cryptPass);
        $strings = Mockery::mock('overload:' . Strings::class);
        $strings->shouldReceive('generatePassword')->andReturn($password);
        $strings->shouldReceive('convertToSshKey')->andReturn($key);

        $sshKey = Mockery::mock('overload:' . SshKey::class);
        $sshKey->shouldReceive('create')->with(
            $params,
            $key,
            $this->solusUserId
        )->andReturn($this->sshKeyId);

        $helpers = Mockery::mock('overload:CreateAccountHelpers');
        $helpers->shouldReceive('createOrUpdateSolusUser')->andReturn($this->solusUserId);
        $helpers->shouldReceive('createSshKey')->andReturn($this->sshKeyId);

        $builder = Mockery::mock('overload:' . ServerCreateRequestBuilder::class);
        $builder->shouldReceive('fromWHMCSCreateAccountParams')->with($params)->andReturnSelf();
        $builder->shouldReceive('withSshKeys')->with([ $this->sshKeyId ])->andReturnSelf();

        $this->userResource->shouldReceive('getUserByEmail')
            ->with($params['clientsdetails']['email'])
            ->once()
            ->andReturn($this->existingSolusUser);
        $this->userResource->shouldReceive('updateUser')->andReturn(true);

        $apiToken = 'token';
        $this->userResource->shouldReceive('createToken')->with($this->solusUserId)->andReturn('token');

        $this->connector->shouldReceive('create')->with($this->params, $apiToken)->andReturn(true);
        $serverResource = Mockery::mock('overload:' . ServerResource::class);

        $createRequest = [
            'name' => 'test_server',
        ];
        $builder->shouldReceive('get')->andReturn($createRequest);
        $createResponse = [
            'data' => [
                'id' => 1,
                'name' => 'test_server',
            ],
        ];
        $serverResource->shouldReceive('create')
            ->with($createRequest)->andReturn($createResponse);

        $hosting = Mockery::mock('overload:' . Hosting::class);
        $hosting->shouldReceive('updateByServiceId')->with(
            (int)$this->params['serviceid'],
            [ 'password' => $cryptPass ],
        );
        $hosting->shouldReceive('syncWithSolusServer')
            ->with($this->params['serviceid'], $createResponse['data'], true);
        $solusServer = Mockery::mock('overload:' . SolusServer::class);
        $solusServer->shouldReceive('create')->with([
            'service_id' => $this->params['serviceid'],
            'server_id' => $createResponse['data']['id'],
            'payload' => json_encode($createResponse['data']),
        ]);
        $result = call_user_func(self::getModuleFunction('CreateAccount'), $this->params);

        self::assertEquals('success', $result);
    }
}
