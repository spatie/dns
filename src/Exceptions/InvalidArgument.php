<?php

namespace Spatie\Dns\Exceptions;

use InvalidArgumentException;

class InvalidArgument extends InvalidArgumentException
{
    public static function domainIsMissing()
    {
        return new static('A domain name is required');
    }

    public static function filterIsNotAValidRecordType($filter, $validRecordTypes)
    {
        $recordTypeString = implode(', ', $validRecordTypes);

        return new static("The given filter `{$filter}` is not valid. It should be one of {$recordTypeString}");
    }
}
