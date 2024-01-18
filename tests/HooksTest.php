<?php
// Copyright 1999-2024. WebPros International GmbH. All rights reserved.

namespace Tests;

use Mockery;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\UserResource;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\ClientArea;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Product;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Servers;

class HooksTest extends AbstractModuleTest
{
    public function testAfterUpgrade(): void
    {
        $product = Mockery::mock('overload:' . Product::class);
        $product->shouldReceive('upgrade')->with(1)->once();

        $this->addToAssertionCount(
            Mockery::getContainer()->mockery_getExpectationCount()
        );

        self::assertNull(call_user_func('createAddHook')([ 'upgradeid' => 1 ]));
    }

    public function testClientDetailsValidationChangeEmptyEmail(): void
    {
        self::assertEquals([], call_user_func('createClientDetailsValidationHook')([ 'email' => '' ]));
    }

    public function testClientDetailsValidationNotChangingEmailAdminArea(): void
    {
        $params = [
            'userid' => 1,
            'email' => 'new_email@mail.com'
        ];

        $client = Mockery::mock('overload:WHMCS\User\Client');
        $client->shouldReceive('findOrFail')->with($params['userid'])->andReturnSelf();
        $client->email = $params['email'];

        self::assertEquals([], call_user_func('createClientDetailsValidationHook')($params));
    }

    public function testClientDetailsValidationNotChangingEmailClientArea(): void
    {
        $userId = 1;
        $params = [
            'save' => true,
            'email' => 'new_email@mail.com'
        ];

        $clientArea = Mockery::mock('overload:' . ClientArea::class);
        $clientArea->shouldReceive('getUserID')->andReturn($userId);
        $client = Mockery::mock('overload:WHMCS\User\Client');
        $client->shouldReceive('findOrFail')->with($userId)->andReturnSelf();
        $client->email = $params['email'];

        self::assertEquals([], call_user_func('createClientDetailsValidationHook')($params));
    }

    public function testClientDetailsValidationNoServer(): void
    {
        $params = [
            'userid' => 1,
            'email' => 'new_email@mail.com'
        ];

        $client = Mockery::mock('overload:WHMCS\User\Client');
        $client->shouldReceive('findOrFail')->with($params['userid'])->andReturnSelf();
        $client->email = 'old_email';

        $servers = Mockery::mock('overload:' . Servers::class);
        $servers->shouldReceive('getValidParams')->andReturn([]);

        self::assertEquals([], call_user_func('createClientDetailsValidationHook')($params));
    }

    public function testClientDetailsValidationEmailAlreadyTaken(): void
    {
        $params = [
            'userid' => 1,
            'email' => 'new_email@mail.com'
        ];

        $client = Mockery::mock('overload:WHMCS\User\Client');
        $client->shouldReceive('findOrFail')->with($params['userid'])->andReturnSelf();
        $client->email = 'old_email';

        $servers = Mockery::mock('overload:' . Servers::class);
        $servers->shouldReceive('getValidParams')->andReturn(true);

        $connector = Mockery::mock('overload:' . Connector::class);
        $connector->shouldReceive('create')->andReturn(true);
        $userResource= Mockery::mock('overload:' . UserResource::class);
        $userResource->shouldReceive('getUserByEmail')->andReturn(true);

        self::assertEquals('email is already taken', call_user_func('createClientDetailsValidationHook')($params));
    }

    public function testClientEditEmptyEmail(): void
    {
        self::assertNull(call_user_func('createClientEditHook')([ 'email' => '' ]));
    }

    public function testClientEditNoServer(): void
    {
        $servers = Mockery::mock('overload:' . Servers::class);
        $servers->shouldReceive('getValidParams')->andReturn([]);

        self::assertNull(call_user_func('createClientEditHook')([ 'email' => 'new_mail@mail.com' ]));
    }

    public function testClientEditNoSolusUser(): void
    {
        $params = [
            'olddata' => [
                'email' => 'old_mail@mail.com'
            ],
            'email' => 'new_mail@mail.com',
        ];
        $servers = Mockery::mock('overload:' . Servers::class);
        $servers->shouldReceive('getValidParams')->andReturn(true);

        $connector = Mockery::mock('overload:' . Connector::class);
        $connector->shouldReceive('create')->andReturn(true);
        $userResource= Mockery::mock('overload:' . UserResource::class);
        $userResource->shouldReceive('getUserByEmail')
            ->with($params['olddata']['email'])
            ->andReturn(null);
        $userResource->shouldNotReceive('patchUser');

        self::assertNull(call_user_func('createClientEditHook')($params));
    }

    public function testClientEditChangeUserEmail(): void
    {
        $userId = 1;
        $params = [
            'olddata' => [
                'email' => 'old_mail@mail.com'
            ],
            'email' => 'new_mail@mail.com',
        ];
        $servers = Mockery::mock('overload:' . Servers::class);
        $servers->shouldReceive('getValidParams')->andReturn(true);

        $connector = Mockery::mock('overload:' . Connector::class);
        $connector->shouldReceive('create')->andReturn(true);
        $userResource= Mockery::mock('overload:' . UserResource::class);
        $userResource->shouldReceive('getUserByEmail')
            ->with($params['olddata']['email'])
            ->andReturn([ 'id' => $userId ]);
        $userResource->shouldReceive('patchUser')
            ->with($userId, [ 'email' => $params['email'] ]);

        self::assertNull(call_user_func('createClientEditHook')($params));
    }
}
