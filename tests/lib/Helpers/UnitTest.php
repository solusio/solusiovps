<?php

namespace Tests\lib\Helpers;

use InvalidArgumentException;
use Tests\AbstractModuleTest;
use WHMCS\Module\Server\SolusIoVps\Helpers\Unit;

class UnitTest extends AbstractModuleTest
{
    /**
     * @dataProvider convertDataProvider
     */
    public function testConvert(int $bytes, string $unit, float $expected): void
    {
        self::assertEquals($expected, Unit::convert($bytes, $unit));
    }

    public function convertDataProvider(): array
    {
        return [
            [1024, Unit::KiB, 1],
            [100*1024*1024, Unit::MiB, 100],
            [30*1024*1024, Unit::KiB, 30*1024],
            [512*1024*1024, Unit::GiB, 0.5],
            [(int)(0.5*1024*1024*1024), Unit::MiB, 512],
            [1023*1024*1024, Unit::GiB, 0.99],
            [1025*1024*1024, Unit::GiB, 1],
            [1988*1024*1024, Unit::GiB, 1.94],
        ];
    }

    public function testConvertUnknownUnit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown unit: "fake"');
        Unit::convert(42, 'fake');
    }
}
