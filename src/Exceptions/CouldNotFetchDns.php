<?php

namespace Spatie\Dns\Exceptions;

use InvalidArgumentException;

class CouldNotFetchDns extends InvalidArgumentException
{
    public static function digReturnedWithError($output)
    {
        return new static("Dig command failed with message: `{$output}`");
    }
}
