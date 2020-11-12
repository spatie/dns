<?php

namespace Spatie\Dns\Exceptions;

use InvalidArgumentException;

class InvalidArgument extends InvalidArgumentException
{
    public static function domainIsMissing()
    {
        return new static('A domain name is required');
    }

    public static function invalidRecordType()
    {
        return new static('Acceptable types are "*", php DNS_* constants flags, a single name or an array of names.');
    }
}
