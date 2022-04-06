<?php

namespace Spatie\Dns\Exceptions;

use RuntimeException;
use Symfony\Component\Process\Process;

class CouldNotFetchDns extends RuntimeException
{
    public static function noHandlerFound(): self
    {
        return new static('A runnable handler could not be found');
    }

    public static function digReturnedWithError(Process $process, string $command): self
    {
        $output = trim($process->getErrorOutput());

        if (empty($output)) {
            $output = trim($process->getOutput());
        }

        return new static("Dig command `{$command}` failed with message: `{$output}`");
    }

    public static function dnsGetRecordReturnedWithError(string $error): self
    {
        if ($error === '') {
            $error = 'dns_get_record function failed';
        }

        return new static($error);
    }
}
