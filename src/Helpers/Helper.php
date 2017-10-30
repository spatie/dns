<?php

namespace Spatie\DnsInfo\Helpers;

class Helper
{
    public static function str_before(string $subject, string $search): string
    {
        $pos = strpos($subject, $search);

        if ($pos === false) {
            return $subject;
        }

        return substr($subject, 0, $pos);
    }
}
