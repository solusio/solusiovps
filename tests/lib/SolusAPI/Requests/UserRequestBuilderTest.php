<?php
// Copyright 2021. Plesk International GmbH.

namespace Tests\lib\SolusAPI\Requests;

use Mockery;
use PHPUnit\Framework\TestCase;
use WHMCS\Module\Server\SolusIoVps\Database\Models\ProductConfigOption;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Requests\UserRequestBuilder;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\UserResource;

/**
 * @runTestsInSeparateProcesses
 */
class UserRequestBuilderTest extends TestCase
{
    private $params;

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

        Mockery::mock(ProductConfigOption::class, 'ProductConfigOptionStub');
    }

    public function testFromWHMCSCreateAccountParamsCreateRequest(): void
    {
        $builder = UserRequestBuilder::fromWHMCSCreateAccountParams($this->params);

        self::assertEquals($builder->getCreateRequest(), [
            'email' => 'test_email',
            'password' => 'test_pass',
            'status' => UserResource::STATUS_ACTIVE,
            'billing_user_id' => 1,
            'limit_group_id' => 1,
            'roles' => [ 1 ],
        ]);
    }

    public function testFromWHMCSCreateAccountParamsUpdateRequest(): void
    {
        $builder = UserRequestBuilder::fromWHMCSCreateAccountParams($this->params);

        self::assertEquals($builder->getUpdateRequest(), [
            'status' => UserResource::STATUS_ACTIVE,
            'billing_user_id' => 1,
        ]);
    }
}
