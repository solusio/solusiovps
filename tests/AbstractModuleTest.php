<?php
// Copyright 1999-2024. WebPros International GmbH. All rights reserved.

namespace Tests;

use Mockery;
use PHPUnit\Framework\TestCase;

abstract class AbstractModuleTest extends TestCase
{
    /** @var string $moduleName */
    private static $moduleName = 'solusiovps';

    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    static function getModuleFunction(string $name): string {
        return self::$moduleName . '_' . $name;
    }
}
