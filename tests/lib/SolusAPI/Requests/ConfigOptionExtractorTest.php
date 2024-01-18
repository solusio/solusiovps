<?php

// Copyright 1999-2024. WebPros International GmbH. All rights reserved.

namespace Tests\lib\SolusAPI\Requests;

use PHPUnit\Framework\TestCase;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Requests\ConfigOptionExtractor;

class ConfigOptionExtractorTest extends TestCase
{
    /**
     * @testWith ["foo", "bar"]
     *           ["unknown", null]
     */
    public function testExtractFromModuleParams(string $optionName, mixed $expectedValue): void
    {
        $moduleParams = [
            'configoptions' => [
                'foo' => 'bar',
            ],
        ];

        self::assertEquals($expectedValue, ConfigOptionExtractor::extractFromModuleParams($moduleParams, $optionName));
    }
}