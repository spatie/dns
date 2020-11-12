<?php

namespace Spatie\Dns\Exceptions;

class CouldNotFetchDns extends \Exception
{
    public static function noHandlerFound()
    {
        return new static('Was not able to find a runnable handler');
    }

    public static function digReturnedWithError($output)
    {
        return new static("Dig command failed with message: `{$output}`");
    }
}
