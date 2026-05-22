<?php

namespace Spatie\Dns\Exceptions;

use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class CouldNotFetchDns extends RuntimeException
{
    /** @var array<int, string> */
    protected const DIG_EXIT_CODES = [
        1 => 'usage error',
        8 => 'could not open batch file',
        9 => 'no reply from server',
        10 => 'internal error',
    ];

    public function __construct(
        string $message,
        public readonly ?int $exitCode = null,
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

        if (isset(static::DIG_EXIT_CODES[$exitCode])) {
            $reason = static::DIG_EXIT_CODES[$exitCode];

            $message = "{$message} ({$reason})";
        }

        if ($output !== '') {
            $message = "{$message}: `{$output}`";
        }

        return new static($message, $exitCode);
    }

    public static function dnsGetRecordReturnedWithError(string $error): self
    {
        if ($error === '') {
            $error = 'dns_get_record function failed';
        }

        return new static($error);
    }
}
