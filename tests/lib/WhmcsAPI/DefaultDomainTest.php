<?php
// Copyright 2021. Plesk International GmbH.

namespace Tests\lib\WhmcsAPI;

use Tests\AbstractModuleTest;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\DefaultDomain;

class DefaultDomainTest extends AbstractModuleTest
{
    public function testGetDomainPositive(): void
    {
        $additionalDomain = DefaultDomain::createFromConfig([
            'default_domain' => [
                'enabled' => true,
                'mask' => '*.example.com',
            ],
        ]);

        self::assertTrue($additionalDomain->isEnabled());
        self::assertEquals('vps-1.example.com', $additionalDomain->getDomainForName('vps-1'));
    }

    public function testGetDomainNegative(): void
    {
        $additionalDomain = DefaultDomain::createFromConfig([
            'default_domain_bad_config' => [
                'enabled' => true,
                'mask' => '*.example.com',
            ],
        ]);

        self::assertFalse($additionalDomain->isEnabled());
        self::assertEquals('vps-1.domain.tld', $additionalDomain->getDomainForName('vps-1'));
    }
}
