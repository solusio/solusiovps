<?php

namespace WHMCS\Module\Server\SolusIoVps\Helpers;

use InvalidArgumentException;

class Unit
{
    public const KiB = 'KiB';
    public const MiB = 'MiB';
    public const GiB = 'GiB';
    public const TiB = 'TiB';
    public const PiB = 'PiB';

    private const MULTIPLIERS = [
        self::KiB => 1024,
        self::MiB => 1024*1024,
        self::GiB => 1024*1024*1024,
        self::TiB => 1024*1024*1024*1024,
        self::PiB => 1024*1024*1024*1024*1024,
    ];

    public static function convert(int $bytes, string $unit, int $decimal = 2): float
    {
        if (!array_key_exists($unit, self::MULTIPLIERS)) {
            throw new InvalidArgumentException(sprintf('Unknown unit: "%s"', $unit));
        }

        $result = $bytes / self::MULTIPLIERS[$unit];

        if ($decimal === 0) {
            return floor($result);
        }

        $k = 10**$decimal;

        return floor($result*$k)/$k;
    }
}
