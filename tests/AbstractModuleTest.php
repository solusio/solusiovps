<?php
// Copyright 2021. Plesk International GmbH.

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
