<?php

namespace Spatie\Dns\Handlers;

use ReflectionMethod;
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
     * Fetches the first type with its own dig process, and all other types with one shared
     * process. The first type runs alone so that an unreachable nameserver fails after one
     * timeout, with the exception that `__invoke()` throws.
     *
     * Fewer than three types gain nothing from a shared process, so each type gets its own.
     * A subclass that overrides `__invoke()` also gets one process per type, so that its
     * override runs for every type.
     */
    public function getRecordsForTypes(string $domain, array $types): array
    {
        if (count($types) < 3) {
            return parent::getRecordsForTypes($domain, $types);
        }

        if ((new ReflectionMethod($this, '__invoke'))->class !== self::class) {
            return parent::getRecordsForTypes($domain, $types);
        }

        return array_merge(
            parent::getRecordsForTypes($domain, array_slice($types, 0, 1, preserve_keys: true)),
            $this->getRecordsInOneProcess($domain, array_slice($types, 1, preserve_keys: true)),
        );
    }

    /**
     * Fetches several types with one dig process. Each type is fetched again with
     * `__invoke()` when the types cannot share one command, or when the output does not hold
     * exactly one answer per type, in the order of `$types`. A failure then throws the same
     * exception as a lookup of that type alone.
     *
     * The order is checked, not only the count. dig can print an extra answer for one query,
     * such as a BADVERS reply before its retry, while another query gets no reply. The number
     * of answers then still matches.
     *
     * A nameserver that does not answer some of the types makes dig wait out the timeout of
     * each of them, and `__invoke()` then waits once more. So such a lookup takes longer to
     * fail than one process per type, which stops at the first type without an answer. dig
     * buffers its output when it writes to a pipe, so a missing answer only shows once the
     * process has ended. That is why the process cannot be stopped at the first one.
     *
     * @param  array<int, string>  $types
     * @return Record[]
     */
    protected function getRecordsInOneProcess(string $domain, array $types): array
    {
        $command = $this->buildCombinedCommand($domain, $types);

        if ($command === null) {
            return parent::getRecordsForTypes($domain, $types);
        }

        $answers = $this->runCombinedCommand($command);

        if (count($answers) !== count($types)) {
            return parent::getRecordsForTypes($domain, $types);
        }

        $records = [];

        foreach (array_combine($types, $answers) as $type => $answer) {
            if (! preg_match("/^;\S+\s+IN\s+{$type}$/m", $answer)) {
                return parent::getRecordsForTypes($domain, $types);
            }

            $this->guardAgainstErrorResponse($answer, $this->buildCommand($domain, $type));

            $records = array_merge($records, $this->transform($type, $this->parseOutput($answer)));
        }

        return $records;
    }

    /**
     * Builds one dig command that asks for every type. dig accepts several queries in one
     * command, each written as `-q <domain> -t <type>`. This only works when
     * `buildCommand()` returns the same options for every type, followed by that type's
     * query. Otherwise this returns null and each type keeps its own process. That keeps a
     * subclass that overrides `buildCommand()` working. The `+question` option makes dig
     * print which type each answer is for.
     *
     * @param  array<int, string>  $types
     * @return array<int, string>|null
     */
    protected function buildCombinedCommand(string $domain, array $types): ?array
    {
        $options = array_slice($this->buildCommand($domain, reset($types)), 0, -4);
        $command = [...$options, '+question'];

        foreach ($types as $type) {
            $query = ['-q', $domain, '-t', $type];

            if (array_values($this->buildCommand($domain, $type)) !== [...$options, ...$query]) {
                return null;
            }

            array_push($command, ...$query);
        }

        return $command;
    }

    /**
     * Runs the combined command and returns the output of each query, in query order. The
     * `+comments` option makes dig print one `;; ->>HEADER<<-` line for every answer, so
     * the output is split in front of those lines. A query that gets no reply prints no
     * such line, and dig still runs the next query. That is why the caller compares the
     * number of answers with the number of types.
     *
     * Like `__invoke()`, this only trusts a process that exits with 0. dig's exit code only
     * covers the last query, so a query before it that finally failed is found through the
     * "no servers could be reached" line dig prints for it. Such a process returns no
     * answers, and so does one that runs into Symfony's timeout of 60 seconds. The caller
     * then falls back to one process per type.
     *
     * @return array<int, string>
     */
    protected function runCombinedCommand(array $command): array
    {
        $process = new Process($command);

        try {
            $process->run();
        } catch (ProcessTimedOutException) {
            return [];
        }

        if (! $process->isSuccessful()) {
            return [];
        }

        if (preg_match('/^;; .*no servers could be reached/m', $process->getOutput())) {
            return [];
        }

        return array_slice(preg_split('/^(?=;; ->>HEADER<<-)/m', $process->getOutput()), 1);
    }
}
