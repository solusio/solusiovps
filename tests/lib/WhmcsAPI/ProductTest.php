<?php
// Copyright 2021. Plesk International GmbH.

namespace Tests\lib\WhmcsAPI;

use Mockery;
use Tests\AbstractModuleTest;
use WHMCS\Module\Server\SolusIoVps\Database\Models\Hosting;
use WHMCS\Module\Server\SolusIoVps\Database\Models\Server;
use WHMCS\Module\Server\SolusIoVps\Database\Models\SolusServer;
use WHMCS\Module\Server\SolusIoVps\Database\Models\Upgrade;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ServerResource;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Product;

/**
 * @runTestsInSeparateProcesses
 */
class ProductTest extends AbstractModuleTest
{
    private int $upgradeId = 1;
    private int $relid = 1;
    private int $newProductId = 2;
    private string $serverId = '1';
    private string $paymentType = 'ppal';
    private Mockery\MockInterface $upgrade;

    protected function setUp(): void
    {
        parent::setUp();

        $this->upgrade = Mockery::mock('overload:' . Upgrade::class);
    }

    public function testUpgrade(): void
    {
        $serverParams = [ 'params' => [] ];
        $solusServerId = 20;
        $newPlanId = 25;
        $this->upgrade->shouldReceive('getById')->with($this->upgradeId)
            ->andReturn((object)[
                'type' => Product::CONFIG_OPTIONS_TYPE,
                'relid' => $this->relid,
                'newvalue' => implode(',', [ (string)$this->newProductId, $this->paymentType ]),
            ]);

        $productModel = Mockery::mock('overload:WHMCS\Module\Server\SolusIoVps\Database\Models\Product');
        $productModel->shouldReceive('getById')->with($this->newProductId)
            ->andReturn((object)[
                'type' => Product::MODULE_NAME,
                'configoption1' => $newPlanId,
            ]);

        $hosting = Mockery::mock('overload:' . Hosting::class);
        $hosting->shouldReceive('getByServiceId')
            ->with($this->relid)
            ->andReturn((object)[ 'server' => $this->serverId ]);

        $solusServer = Mockery::mock('overload:' . SolusServer::class);
        $solusServer->shouldReceive('getByServiceId')
            ->with($this->relid)
            ->andReturn((object)[ 'server_id' => $solusServerId ]);

        $server = Mockery::mock('overload:' . Server::class);
        $server->shouldReceive('getParams')
            ->with((int)$this->serverId)
            ->andReturn($serverParams);
        $connector = Mockery::mock('overload:' . Connector::class);
        $connector->shouldReceive('create')->with($serverParams)->andReturn(true);
        $serverResource = Mockery::mock('overload:' . ServerResource::class);
        $serverResource->shouldReceive('resize')
            ->with($solusServerId, $newPlanId);

        self::assertNull(Product::upgrade($this->upgradeId));
    }

    public function testUpgradeWrongModuleName(): void
    {
        $this->upgrade->shouldReceive('getById')->with($this->upgradeId)
            ->andReturn((object)[
                'type' => Product::CONFIG_OPTIONS_TYPE,
                'relid' => $this->relid,
                'newvalue' => implode(',', [ (string)$this->newProductId, $this->paymentType ]),
            ]);

        $productModel = Mockery::mock('overload:WHMCS\Module\Server\SolusIoVps\Database\Models\Product');
        $productModel->shouldReceive('getById')->with($this->newProductId)
            ->andReturn((object)[ 'type' => 'other module' ]);
        self::assertNull(Product::upgrade($this->upgradeId));
    }

    public function testUpgradeNoProduct(): void
    {

        $this->upgrade->shouldReceive('getById')->with($this->upgradeId)
            ->andReturn((object)[
                'type' => Product::CONFIG_OPTIONS_TYPE,
                'relid' => $this->relid,
                'newvalue' => implode(',', [ (string)$this->newProductId, $this->paymentType ]),
            ]);

        $productModel = Mockery::mock('overload:WHMCS\Module\Server\SolusIoVps\Database\Models\Product');
        $productModel->shouldReceive('getById')->with($this->newProductId)
            ->andReturn(null);
        self::assertNull(Product::upgrade($this->upgradeId));
    }

    public function testUpgradeWrongType(): void
    {
        $this->upgrade->shouldReceive('getById')->with($this->upgradeId)
            ->andReturn((object)[ 'type' => 'test', 'relid' => $this->relid ]);

        self::assertNull(Product::upgrade($this->upgradeId));
    }
}
