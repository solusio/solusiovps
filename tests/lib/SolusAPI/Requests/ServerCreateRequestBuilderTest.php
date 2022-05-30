<?php
// Copyright 2021. Plesk International GmbH.

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
        $params['configoptions']['Operating System'] = 1;

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
        $this->params['configoptions']['VCPU'] = 1;
        $this->params['configoptions']['Memory'] = 2;
        $this->params['configoptions']['Disk Space'] = 2;
        $this->params['configoptions']['VCPU Units'] = 8;
        $this->params['configoptions']['VCPU Limit'] = 10;
        $this->params['configoptions']['IO Priority'] = 6;
        $this->params['configoptions']['Swap'] = 4;
        $this->params['configoptions']['Total traffic limit monthly'] = 5;

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
                    'vcpu' => $this->params['configoptions']['VCPU'],
                    'ram' => $this->params['configoptions']['Memory'] * 1024 * 1024,
                    'disk' => $this->params['configoptions']['Disk Space'],
                    'vcpu_units' => $this->params['configoptions']['VCPU Units'],
                    'vcpu_limit' => $this->params['configoptions']['VCPU Limit'],
                    'io_priority' => $this->params['configoptions']['IO Priority'],
                    'swap' => $this->params['configoptions']['Swap'] * 1024 * 1024,
                ],
                'limits' => [
                    'network_total_traffic' => [
                        'limit' => $this->params['configoptions']['Total traffic limit monthly'],
                    ],
                ],
            ],
        ]);
    }
}
