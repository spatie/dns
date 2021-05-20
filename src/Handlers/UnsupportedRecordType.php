<?php

namespace Spatie\Dns\Handlers;

use Exception;

class UnsupportedRecordType extends Exception
{
    public static function make(string $type)
    {
        return new static("Dns record with type `{$type}` is not supported.");
    }
}
