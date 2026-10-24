<?php

namespace App\Support;

/** Format angka gaya Indonesia. */
class Number
{
    public static function id(float|int|null $value, int $decimals = 1): string
    {
        return $value === null ? '-' : number_format((float) $value, $decimals, ',', '.');
    }

    public static function money(float|int|null $value): string
    {
        $v = (float) $value;

        return match (true) {
            $v >= 1e12 => 'Rp '.self::id($v / 1e12).' T',
            $v >= 1e9 => 'Rp '.self::id($v / 1e9).' M',
            $v >= 1e6 => 'Rp '.self::id($v / 1e6).' Jt',
            default => 'Rp '.self::id($v, 0),
        };
    }
}
