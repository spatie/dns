<?php

namespace Spatie\Dns\Exceptions;

class CouldNotFetchDns extends \Exception
{
    public static function digReturnedWithError($output)
    {
        return new static("Dig command failed with message: `{$output}`");
    }
}
