<?php

use Spatie\Dns\Dns;
use Spatie\Dns\Exceptions\CouldNotFetchDns;
use Spatie\Dns\Handlers\Dig;
use Spatie\Dns\Records\Record;
use Spatie\Dns\Support\Factory;
use Spatie\Dns\Support\Types;

it('returns the same records as fetching each type on its own', function () {
    $dig = (new Dig(new Factory))->useNameserver('ns1.digitalocean.com');
    $types = (new Types)->toNames(DNS_ALL);

    $perType = [];

    foreach ($types as $flag => $type) {
        $perType = array_merge($perType, $dig('spatie.be', $flag, $type));
    }

    $batched = $dig->getRecords('spatie.be', $types);

    $typesOf = fn (array $records) => array_map(fn (Record $record) => $record->type(), $records);
    $sorted = function (array $records) {
        $lines = array_map(fn (Record $record) => json_encode([...$record->toArray(), 'ttl' => null]), $records);
        sort($lines);

        return $lines;
    };

    expect($batched)->not->toBeEmpty()
        ->and($typesOf($batched))->toBe($typesOf($perType))
        ->and($sorted($batched))->toBe($sorted($perType));
});

it('fetches the first type on its own and all other types with one process', function () {
    $dig = new BatchFixtureDig([
        'A' => batchDigAnswer('NOERROR', 'spatie.be. 300 IN A 192.0.2.10'),
        'NS' => batchDigAnswer('NOERROR', 'spatie.be. 300 IN NS ns1.example.com.'),
        'MX' => batchDigAnswer('NOERROR', 'spatie.be. 300 IN MX 10 mail.example.com.'),
        'TXT' => batchDigAnswer('NOERROR', 'spatie.be. 300 IN TXT "v=spf1 -all"'),
    ]);

    $records = Dns::query()->useHandlers([$dig])->getRecords('spatie.be', ['A', 'NS', 'MX', 'TXT']);

    expect(array_map(fn (Record $record) => $record->type(), $records))->toBe(['A', 'NS', 'MX', 'TXT'])
        ->and($dig->processCount())->toBe(2);
});

it('throws with the status of the first answer that failed', function () {
    $dig = new BatchFixtureDig([
        'A' => batchDigAnswer('NOERROR', 'spatie.be. 300 IN A 192.0.2.10'),
        'NS' => batchDigAnswer('NOERROR', 'spatie.be. 300 IN NS ns1.example.com.'),
        'MX' => batchDigAnswer('REFUSED'),
        'TXT' => batchDigAnswer('SERVFAIL'),
    ]);

    $dns = Dns::query()->throwOnErrorResponse()->useHandlers([$dig]);

    expect(fn () => $dns->getRecords('spatie.be', ['A', 'NS', 'MX', 'TXT']))
        ->toThrow(fn (CouldNotFetchDns $exception) => expect($exception->responseStatus)->toBe('REFUSED')
            ->and($exception->getMessage())->toContain('-t MX`'));

    expect($dig->processCount())->toBe(2);
});

it('fetches each type on its own when dig did not answer every query', function () {
    $dig = new BatchFixtureDig([
        'A' => batchDigAnswer('NOERROR', 'spatie.be. 300 IN A 192.0.2.10'),
        'NS' => null,
        'MX' => batchDigAnswer('NOERROR', 'spatie.be. 300 IN MX 10 mail.example.com.'),
    ]);

    expect(fn () => Dns::query()->useHandlers([$dig])->getRecords('spatie.be', ['A', 'NS', 'MX']))
        ->toThrow(fn (CouldNotFetchDns $exception) => expect($exception->exitCode)->toBe(9));

    expect($dig->processCount())->toBe(3);
});

it('runs an overridden __invoke() for every type', function () {
    $dig = new class(['A' => batchDigAnswer('NOERROR'), 'NS' => batchDigAnswer('NOERROR'), 'MX' => batchDigAnswer('NOERROR')]) extends BatchFixtureDig
    {
        public array $invokedTypes = [];

        public function __invoke(string $domain, int $flag, string $type): array
        {
            $this->invokedTypes[] = $type;

            return parent::__invoke($domain, $flag, $type);
        }
    };

    Dns::query()->useHandlers([$dig])->getRecords('spatie.be', ['A', 'NS', 'MX']);

    expect($dig->invokedTypes)->toBe(['A', 'NS', 'MX']);
});

it('fails after a single timeout when the nameserver does not answer', function () {
    $dns = Dns::query()->useNameserver('192.0.2.1')->setTimeout(1)->setRetries(1);

    $start = microtime(true);

    expect(fn () => $dns->getRecords('spatie.be'))->toThrow(CouldNotFetchDns::class)
        ->and(microtime(true) - $start)->toBeLessThan(3);
});

function batchDigAnswer(string $status, string $records = ''): string
{
    return implode(PHP_EOL, [
        ';; Got answer:',
        ";; ->>HEADER<<- opcode: QUERY, status: {$status}, id: 3514",
        ';; flags: qr aa rd; QUERY: 1, ANSWER: 1, AUTHORITY: 0, ADDITIONAL: 1',
        '',
        ';; ANSWER SECTION:',
        $records,
        '',
    ]);
}

class BatchFixtureDig extends Dig
{
    protected string $log;

    /** @param array<string, ?string> $answers */
    public function __construct(protected array $answers)
    {
        parent::__construct(new Factory);

        $this->log = tempnam(sys_get_temp_dir(), 'dig');
    }

    public function __destruct()
    {
        unlink($this->log);
    }

    public function processCount(): int
    {
        return substr_count(file_get_contents($this->log), 'run');
    }

    /** @return array<int, string> */
    protected function buildCommand(string $domain, string $type): array
    {
        $script = implode(PHP_EOL, [
            'file_put_contents('.var_export($this->log, true).', "run\n", FILE_APPEND);',
            '$answers = '.var_export($this->answers, true).';',
            '$exitCode = 0;',
            'foreach (array_keys($argv, "-t") as $index) {',
            '    $answer = $answers[$argv[$index + 1]];',
            '    echo $answer ?? ";; no servers could be reached\n";',
            '    $exitCode = $answer === null ? 9 : 0;',
            '}',
            'exit($exitCode);',
        ]);

        return [PHP_BINARY, '-r', $script, '--', '-q', $domain, '-t', $type];
    }
}
