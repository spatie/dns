<?php

namespace Spatie\Dns\Exceptions;

use RuntimeException;

class CouldNotFetchDns extends RuntimeException
{
    public static function noHandlerFound(): static
    {
        return new static('Was not able to find a runnable handler');
    }

    public static function digReturnedWithError($output): static
    {
        return new static("Dig command failed with message: `{$output}`");
    }
}
