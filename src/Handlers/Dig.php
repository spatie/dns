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
     * returning no records is correct.
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
    protected function guardAgainstErrorResponse(string $output, array $command): void
    {
        if (! $this->throwOnErrorResponse) {
            return;
        }

        if (! preg_match('/^;; ->>HEADER<<-.*?status: ([A-Z0-9]+)/m', $output, $matches)) {
            return;
        }

        if (in_array($matches[1], $this->successfulResponseStatuses, true)) {
            return;
        }

        throw CouldNotFetchDns::digReturnedErrorResponse($matches[1], implode(' ', $command));
    }

    /**
     * Keeps only the record lines. dig prints its comments, such as the response header, on
     * lines that start with a semicolon.
     */
    protected function parseOutput(string $output): array
    {
        return array_values(preg_grep('/^[^;]/', explode(PHP_EOL, $output)));
    }
}
