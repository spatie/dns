<?php

namespace Spatie\Dns\Handlers;

use Spatie\Dns\Exceptions\CouldNotFetchDns;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class Dig extends Handler
{
    protected ?int $timeout = 2;

    protected ?int $retries = 2;

    /**
     * Response statuses that are real answers. NXDOMAIN means the name does not exist, so
     * returning no records is correct. Any other status, such as REFUSED or SERVFAIL, means
     * the nameserver did not answer the question.
     *
     * @var array<int, string>
     */
    protected array $successfulResponseStatuses = ['NOERROR', 'NXDOMAIN'];

    public function __invoke(string $domain, int $flag, string $type): array
    {
        $command = $this->buildCommand($domain, $type);

        $process = new Process($command);

        $process
            ->enableOutput()
            ->run();

        if (! $process->isSuccessful()) {
            $command = implode(' ', $command);

            throw CouldNotFetchDns::digReturnedWithError($process, $command);
        }

        $output = $process->getOutput();

        $this->guardAgainstErrorResponse($output, $command);

        return $this->transform(
            $type,
            $this->parseOutput($output)
        );
    }

    public function canHandle(): bool
    {
        if (! $digPath = (new ExecutableFinder)->find('dig', null, ['/usr/bin'])) {
            return false;
        }

        $process = new Process([$digPath, '-v', '2>&1']);
        $process->run();

        if (str_contains($process->getOutput(), 'not found')) {
            return false;
        }

        return $process->isSuccessful();
    }

    protected function buildCommand(string $domain, string $type): array
    {
        return array_filter([
            'dig',
            '+nocmd',
            '+noall',
            '+noidnout',
            '+authority',
            '+answer',
            '+comments',
            '+nomultiline',
            '+answer',
            $this->prepareRetries(),
            $this->prepareTimeout(),
            $this->prepareNameserver(),
            '-q',
            $domain,
            '-t',
            $type,
        ]);
    }

    protected function prepareNameserver(): ?string
    {
        if (empty($this->nameserver)) {
            return null;
        }

        return "@{$this->nameserver}";
    }

    protected function prepareRetries(): ?string
    {
        return "+tries={$this->retries}";
    }

    protected function prepareTimeout(): ?string
    {
        return "+time={$this->timeout}";
    }

    /**
     * Reads the status from the header that `+comments` adds to the output. The pattern is
     * anchored to the start of a line, so text inside a record, such as a TXT value, cannot
     * pose as the header.
     */
    protected function responseStatus(string $output): ?string
    {
        if (! preg_match('/^;; ->>HEADER<<-.*?status: ([A-Z0-9]+)/m', $output, $matches)) {
            return null;
        }

        return $matches[1];
    }

    protected function guardAgainstErrorResponse(string $output, array $command): void
    {
        if (! $this->throwOnErrorResponse) {
            return;
        }

        $status = $this->responseStatus($output);

        if ($status === null) {
            return;
        }

        if (in_array($status, $this->successfulResponseStatuses, true)) {
            return;
        }

        throw CouldNotFetchDns::digReturnedErrorResponse($status, implode(' ', $command));
    }

    /**
     * Keeps only the record lines. Lines that start with a semicolon are dig's comments,
     * such as the header and the EDNS pseudo-section, and are never records.
     */
    protected function parseOutput(string $output): array
    {
        $lines = array_filter(
            explode(PHP_EOL, trim($output)),
            fn (string $line): bool => trim($line) !== '',
        );

        $records = array_filter(
            $lines,
            fn (string $line): bool => ! str_starts_with($line, ';'),
        );

        return array_values($records);
    }
}
