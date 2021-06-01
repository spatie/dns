<?php

namespace Spatie\Dns\Exceptions;

use InvalidArgumentException;

class InvalidArgument extends InvalidArgumentException
{
    public static function domainIsMissing(string $url): self
    {
        return new static("A domain name is required in the given url: `{$url}`");
    }

    public static function invalidRecordType(): self
    {
        return new static('Acceptable types are "*", php DNS_* constants flags, a single name or an array of names');
    }

    public static function wrongRecordType(string $type, string $expected): self
    {
        return new static("A `{$type}` record was passed to the `{$expected}` record class.");
    }
}
