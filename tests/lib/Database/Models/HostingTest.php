<?php
// Copyright 2021. Plesk International GmbH.

namespace Tests\lib\Database\Models;

use Mockery;
use PHPUnit\Framework\TestCase;
use WHMCS\Module\Server\SolusIoVps\Database\Models\Hosting;

/**
 * @runTestsInSeparateProcesses
 */
class HostingTest extends TestCase
{
    private Mockery\MockInterface $hosting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hosting = Mockery::mock(Hosting::class)->makePartial();
        $db = Mockery::mock('overload:WHMCS\Database\Capsule');
        $db->shouldReceive('table->where->update')->andReturn(true);
    }

    /**
     * @testWith [true]
     *           [false]
     */
    public function testSyncWithSolusServer(bool $updateDomain): void
    {
        $serviceId = 1;
        $data = [
            'name' => 'test.domain.ltd',
            'ip_addresses' => [
                'ipv4' => [
                    [ 'ip' => '192.168.0.1' ],
                    [ 'ip' => '192.168.0.2' ],
                ],
                'ipv6' => [
                    'primary_ip' => '::ffff:c0a8:1',
                ],
            ],
        ];

        $result = [
            'dedicatedip' => '192.168.0.1',
            'assignedips' => '192.168.0.1,192.168.0.2,::ffff:c0a8:1',
        ];

        if ($updateDomain) {
            $data['domain'] = 'test.domain.ltd';
        }

        $this->hosting->shouldReceive('updateByServiceId')
            ->with($serviceId, $result)->andReturn(true);

        $this->addToAssertionCount(
            Mockery::getContainer()->mockery_getExpectationCount()
        );

        $this->hosting->syncWithSolusServer($serviceId, $data, $updateDomain);
    }
}
