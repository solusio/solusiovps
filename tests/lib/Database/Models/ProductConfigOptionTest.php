<?php
// Copyright 1999-2024. WebPros International GmbH. All rights reserved.

namespace Tests\lib\Database\Models;

use PHPUnit\Framework\TestCase;
use WHMCS\Module\Server\SolusIoVps\Database\Models\ProductConfigOption;

/**
 * @runTestsInSeparateProcesses
 */
class ProductConfigOptionTest extends TestCase
{
    public function testExtractProductOptions(): void
    {
        $rows = [
            (object)['optionname' => '50 | CentOS 8'],
            (object)['optionname' => '96 | Ubuntu 20'],
        ];

        self::assertEquals([
            '50' => 'CentOS 8',
            '96' => 'Ubuntu 20',
        ], ProductConfigOption::extractProductOptions($rows));
    }
}
