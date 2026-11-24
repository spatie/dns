<?php

namespace Spatie\Dns\Handlers;

use Spatie\Dns\Exceptions\CouldNotFetchDns;
use Spatie\Dns\Records\Record;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
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

    /**
     * Fetches several types with two dig processes instead of one process per type. The
     * first type is fetched on its own, the same way as before. So an unreachable
     * nameserver fails after one timeout and throws the same exception as before. The
     * other types are then fetched with one process.
     *
     * @param  array<int, string>  $types
     * @return Record[]
     */
    public function getRecords(string $domain, array $types): array
    {
        if (count($types) < 3) {
            return parent::getRecords($domain, $types);
        }

        return array_merge(
            parent::getRecords($domain, array_slice($types, 0, 1, true)),
            $this->getRecordsInOneProcess($domain, array_slice($types, 1, null, true)),
        );
    }

    /**
     * dig answers the queries one after another, and `+comments` puts a response header in
     * front of each answer. The output is split on those headers, and each part is read
     * the same way as the output of a single query.
     *
     * When the output cannot be split into one answer per type, the types are fetched
     * again one at a time. That path throws the same exceptions as before. This matters
     * because a query that gets no answer has no header, and dig then carries on with the
     * next query. Its exit code only reflects the last query.
     *
     * @param  array<int, string>  $types
     * @return Record[]
     */
    protected function getRecordsInOneProcess(string $domain, array $types): array
    {
        $command = $this->buildBatchCommand($domain, $types);

        if ($command === null) {
            return parent::getRecords($domain, $types);
        }

        $answers = $this->runBatchCommand($command, count($types));

        if ($answers === null) {
            return parent::getRecords($domain, $types);
        }

        $records = [];

        foreach (array_values($types) as $index => $type) {
            $this->guardAgainstErrorResponse($answers[$index], $this->buildCommand($domain, $type));

            $records = array_merge(
                $records,
                $this->transform($type, $this->parseOutput($answers[$index]))
            );
        }

        return $records;
    }

    /**
     * Puts the query of every type behind the options of a single command. The commands
     * can only be combined when they differ in nothing but the query at the end. A
     * subclass that builds other commands gets null here and keeps one process per type.
     *
     * @param  array<int, string>  $types
     */
    protected function buildBatchCommand(string $domain, array $types): ?array
    {
        $options = null;
        $queries = [];

        foreach ($types as $type) {
            $command = array_values($this->buildCommand($domain, $type));
            $query = array_splice($command, -4);

            if ($query !== ['-q', $domain, '-t', $type]) {
                return null;
            }

            $options ??= $command;

            if ($command !== $options) {
                return null;
            }

            array_push($queries, ...$query);
        }

        return [...$options, ...$queries];
    }

    /**
     * Returns the output of each query, or null when dig failed or did not answer every
     * query. Symfony stops a process after 60 seconds. One query stays far below that, but
     * a batch of queries that all time out can reach it. That also counts as a failure.
     *
     * @return array<int, string>|null
     */
    protected function runBatchCommand(array $command, int $queryCount): ?array
    {
        $process = new Process($command);

        try {
            $process
                ->enableOutput()
                ->run();
        } catch (ProcessTimedOutException) {
            return null;
        }

        if (! $process->isSuccessful()) {
            return null;
        }

        $answers = array_slice(preg_split('/^(?=;; ->>HEADER<<-)/m', $process->getOutput()), 1);

        if (count($answers) !== $queryCount) {
            return null;
        }

        return $answers;
    }
}
