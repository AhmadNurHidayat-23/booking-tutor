<?php

namespace App\Support;

class Format
{
    public static function money(int $value): string
    {
        return 'Rp '.number_format($value, 0, ',', '.');
    }
}
