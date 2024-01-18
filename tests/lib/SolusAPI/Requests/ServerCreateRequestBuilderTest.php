<?php
// Copyright 1999-2024. WebPros International GmbH. All rights reserved.

namespace Tests\lib\SolusAPI\Requests;

use Mockery;
use PHPUnit\Framework\TestCase;
use WHMCS\Module\Server\SolusIoVps\Database\Models\ProductConfigOption;
use WHMCS\Module\Server\SolusIoVps\Helpers\Arr;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Requests\ServerCreateRequestBuilder;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Config;

/**
 * @runTestsInSeparateProcesses
 */
class ServerCreateRequestBuilderTest extends TestCase
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

    public function testBuildFromCreateAccountParams(): void
    {
        $builder = ServerCreateRequestBuilder::fromWHMCSCreateAccountParams($this->params);

        self::assertEquals($builder->get(), [
            'name' => 'test.domain.ltd',
            'plan' => 1,
            'location' => 1,
            'password' => 'test_pass',
            'fqdns' => [ 'test.domain.ltd' ],
            'application' => 1,
            'application_data' => [''],
        ]);
    }

    public function testBuildFromCreateAccountParamsWithDefaultDomain(): void
    {
        $config = Mockery::mock('overload:' . Config::class, 'Config');
        $config->shouldReceive('loadModuleConfig')->andReturn([
            'default_domain' => [
                'enabled' => true,
                'mask' => '*.example.com',
            ],
        ]);
        $params = $this->params;
        Arr::forget($params, 'domain');
        $builder = ServerCreateRequestBuilder::fromWHMCSCreateAccountParams($params);

        $domain = sprintf('vps-%d.example.com', $this->params['serviceid']);

        self::assertEquals($builder->get(), [
            'name' => $domain,
            'plan' => 1,
            'location' => 1,
            'password' => 'test_pass',
            'fqdns' => [ $domain ],
            'application' => 1,
            'application_data' => [''],
        ]);
    }

    public function testBuildFromCreateAccountParamsWithSshKeys(): void
    {
        $builder = ServerCreateRequestBuilder::fromWHMCSCreateAccountParams($this->params);

        self::assertEquals($builder->withSshKeys([ 1 ])->get(), [
            'name' => 'test.domain.ltd',
            'plan' => 1,
            'location' => 1,
            'password' => 'test_pass',
            'fqdns' => [ 'test.domain.ltd' ],
            'application' => 1,
            'application_data' => [''],
            'ssh_keys' => [ 1 ],
        ]);
    }

    public function testBuildFromCreateAccountParamsWithOperatingSystem(): void
    {
        $params = Arr::except($this->params, [ 'configoption4']);
        $params['configoption3'] = 1;
        $params['configoption5'] = 'user_data';

        $builder = ServerCreateRequestBuilder::fromWHMCSCreateAccountParams($params);

        self::assertEquals($builder->get(), [
            'name' => 'test.domain.ltd',
            'plan' => 1,
            'location' => 1,
            'password' => 'test_pass',
            'fqdns' => [ 'test.domain.ltd' ],
            'os' => 1,
            'user_data' => 'user_data',
        ]);
    }

    public function testBuildFromCreateAccountParamsWithoutUserData(): void
    {
        $params = Arr::except($this->params, [ 'configoption4']);
        $params['configoption3'] = 1;
        $params['configoption5'] = '';

        $builder = ServerCreateRequestBuilder::fromWHMCSCreateAccountParams($params);

        self::assertEquals($builder->get(), [
            'name' => 'test.domain.ltd',
            'plan' => 1,
            'location' => 1,
            'password' => 'test_pass',
            'fqdns' => [ 'test.domain.ltd' ],
            'os' => 1,
        ]);
    }

    public function testBuildFromCreateAccountParamsWithOperatingSystemInConfigOpts(): void
    {
        $params = Arr::except($this->params, [ 'configoption4']);
        $params['configoption5'] = 'user_data';
        $params['configoptions'][ProductConfigOption::OPERATING_SYSTEM] = 1;

        $builder = ServerCreateRequestBuilder::fromWHMCSCreateAccountParams($params);

        self::assertEquals($builder->get(), [
            'name' => 'test.domain.ltd',
            'plan' => 1,
            'location' => 1,
            'password' => 'test_pass',
            'fqdns' => [ 'test.domain.ltd' ],
            'os' => $params['configoptions'][ProductConfigOption::OPERATING_SYSTEM] ,
            'user_data' => 'user_data',
        ]);
    }

    public function testBuildFromCreateAccountParamsWithUser(): void
    {
        $builder = ServerCreateRequestBuilder::fromWHMCSCreateAccountParams($this->params);
        $builder->withUser(42);

        self::assertEquals($builder->get(), [
            'name' => 'test.domain.ltd',
            'plan' => 1,
            'location' => 1,
            'password' => 'test_pass',
            'fqdns' => [ 'test.domain.ltd' ],
            'application' => 1,
            'application_data' => [''],
            'user' => 42,
        ]);
    }

    public function testBuildFromCreateAccountParamsWithCustomPlanConfigOptions(): void
    {
        $this->params['configoptions'][ProductConfigOption::VCPU] = 1;
        $this->params['configoptions'][ProductConfigOption::MEMORY] = 2;
        $this->params['configoptions'][ProductConfigOption::DISK_SPACE] = 2;
        $this->params['configoptions'][ProductConfigOption::VCPU_UNITS] = 8;
        $this->params['configoptions'][ProductConfigOption::VCPU_LIMIT] = 10;
        $this->params['configoptions'][ProductConfigOption::IO_PRIORITY] = 6;
        $this->params['configoptions'][ProductConfigOption::SWAP] = 4;
        $this->params['configoptions'][ProductConfigOption::TOTAL_TRAFFIC_LIMIT_MONTHLY] = 5;

        $builder = ServerCreateRequestBuilder::fromWHMCSCreateAccountParams($this->params);

        self::assertEquals($builder->get(), [
            'name' => 'test.domain.ltd',
            'plan' => 1,
            'location' => 1,
            'password' => 'test_pass',
            'fqdns' => [ 'test.domain.ltd' ],
            'application' => 1,
            'application_data' => [''],
            'custom_plan' => [
                'params' => [
                    'vcpu' => $this->params['configoptions'][ProductConfigOption::VCPU],
                    'ram' => $this->params['configoptions'][ProductConfigOption::MEMORY] * 1024 * 1024,
                    'disk' => $this->params['configoptions'][ProductConfigOption::DISK_SPACE],
                    'vcpu_units' => $this->params['configoptions'][ProductConfigOption::VCPU_UNITS],
                    'vcpu_limit' => $this->params['configoptions'][ProductConfigOption::VCPU_LIMIT],
                    'io_priority' => $this->params['configoptions'][ProductConfigOption::IO_PRIORITY],
                    'swap' => $this->params['configoptions'][ProductConfigOption::SWAP] * 1024 * 1024,
                ],
                'limits' => [
                    'network_total_traffic' => [
                        'limit' => $this->params['configoptions'][ProductConfigOption::TOTAL_TRAFFIC_LIMIT_MONTHLY],
                    ],
                ],
            ],
        ]);
    }

    public function testBuildFromCreateAccountParamsWithExtraIpAddressConfigOptions(): void
    {
        $this->params['configoptions'][ProductConfigOption::EXTRA_IP_ADDRESS] = 3;

        $builder = ServerCreateRequestBuilder::fromWHMCSCreateAccountParams($this->params);

        self::assertEquals($builder->get(), [
            'name' => 'test.domain.ltd',
            'plan' => 1,
            'location' => 1,
            'password' => 'test_pass',
            'fqdns' => [ 'test.domain.ltd' ],
            'application' => 1,
            'application_data' => [''],
            'additional_ip_count' => $this->params['configoptions'][ProductConfigOption::EXTRA_IP_ADDRESS],
        ]);
    }
}
