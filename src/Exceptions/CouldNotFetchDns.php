<?php

namespace Spatie\Dns\Exceptions;

use RuntimeException;

class CouldNotFetchDns extends RuntimeException
{
    public static function noHandlerFound(): self
    {
        return new static('Was not able to find a runnable handler');
    }

    public static function digReturnedWithError($output): self
    {
        return new static("Dig command failed with message: `{$output}`");
    }
}
