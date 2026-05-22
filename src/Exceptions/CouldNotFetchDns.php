<?php

namespace Spatie\Dns\Exceptions;

use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class CouldNotFetchDns extends RuntimeException
{
    public function __construct(
        string $message,
        public ?int $exitCode = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function noHandlerFound(): self
    {
        return new static('A runnable handler could not be found');
    }

    public static function digReturnedWithError(Process $process, string $command): self
    {
        $exitCode = $process->getExitCode();

        $output = trim($process->getErrorOutput());

        if ($output === '') {
            $output = trim($process->getOutput());
        }

        $message = "Dig command `{$command}` failed with exit code {$exitCode}";

        $reason = static::digExitCodeReason($exitCode);

        if ($reason !== null) {
            $message = "{$message} ({$reason})";
        }

        if ($output !== '') {
            $message = "{$message}: `{$output}`";
        }

        return new static($message, $exitCode);
    }

    protected static function digExitCodeReason(?int $exitCode): ?string
    {
        return match ($exitCode) {
            1 => 'usage error',
            8 => 'could not open batch file',
            9 => 'no reply from server',
            10 => 'internal error',
            default => null,
        };
    }

    public static function dnsGetRecordReturnedWithError(string $error): self
    {
        if ($error === '') {
            $error = 'dns_get_record function failed';
        }

        return new static($error);
    }
}
