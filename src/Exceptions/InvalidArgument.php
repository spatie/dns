<?php

namespace Spatie\Dns\Exceptions;

use InvalidArgumentException;

class InvalidArgument extends InvalidArgumentException
{
    public static function domainIsMissing(): static
    {
        return new static('A domain name is required');
    }

    public static function invalidRecordType(): static
    {
        return new static('Acceptable types are "*", php DNS_* constants flags, a single name or an array of names.');
    }
}
