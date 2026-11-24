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

    $combined = $dig->getRecordsForTypes('spatie.be', $types);

    $typesOf = fn (array $records) => array_map(fn (Record $record) => $record->type(), $records);
    $sorted = function (array $records) {
        $lines = array_map(fn (Record $record) => json_encode([...$record->toArray(), 'ttl' => null]), $records);
        sort($lines);

        return $lines;
    };

    expect($combined)->not->toBeEmpty()
        ->and($typesOf($combined))->toBe($typesOf($perType))
        ->and($sorted($combined))->toBe($sorted($perType));
});

it('fetches the first type on its own and all other types with one process', function () {
    $dig = new ScriptedDig([
        'A' => digAnswer('NOERROR', 'spatie.be. 300 IN A 192.0.2.10'),
        'NS' => digAnswer('NOERROR', 'spatie.be. 300 IN NS ns1.example.com.'),
        'MX' => digAnswer('NOERROR', 'spatie.be. 300 IN MX 10 mail.example.com.'),
        'TXT' => digAnswer('NOERROR', 'spatie.be. 300 IN TXT "v=spf1 -all"'),
    ]);

    $records = Dns::query()->useHandlers([$dig])->getRecords('spatie.be', ['A', 'NS', 'MX', 'TXT']);

    expect(array_map(fn (Record $record) => $record->type(), $records))->toBe(['A', 'NS', 'MX', 'TXT'])
        ->and($dig->processes())->toBe(['A', 'NS MX TXT']);
});

it('throws with the status and command of the first type that failed', function () {
    $dig = new ScriptedDig([
        'A' => digAnswer('NOERROR', 'spatie.be. 300 IN A 192.0.2.10'),
        'NS' => digAnswer('NOERROR', 'spatie.be. 300 IN NS ns1.example.com.'),
        'MX' => digAnswer('REFUSED'),
        'TXT' => digAnswer('SERVFAIL'),
    ]);

    $dns = Dns::query()->throwOnErrorResponse()->useHandlers([$dig]);

    expect(fn () => $dns->getRecords('spatie.be', ['A', 'NS', 'MX', 'TXT']))
        ->toThrow(fn (CouldNotFetchDns $exception) => expect($exception->responseStatus)->toBe('REFUSED')
            ->and($exception->getMessage())->toContain('-t MX`'))
        ->and($dig->processes())->toBe(['A', 'NS MX TXT']);
});

it('fetches each type on its own when a query got no reply', function () {
    $dig = new ScriptedDig([
        'A' => digAnswer('NOERROR', 'spatie.be. 300 IN A 192.0.2.10'),
        'NS' => null,
        'MX' => digAnswer('NOERROR', 'spatie.be. 300 IN MX 10 mail.example.com.'),
    ]);

    expect(fn () => Dns::query()->useHandlers([$dig])->getRecords('spatie.be', ['A', 'NS', 'MX']))
        ->toThrow(fn (CouldNotFetchDns $exception) => expect($exception->exitCode)->toBe(9))
        ->and($dig->processes())->toBe(['A', 'NS MX', 'NS']);
});

it('runs an overridden __invoke() for every type', function () {
    $dig = new class(['A' => digAnswer('NOERROR'), 'NS' => digAnswer('NOERROR'), 'MX' => digAnswer('NOERROR')]) extends ScriptedDig
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

function digAnswer(string $status, string $records = ''): string
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

class ScriptedDig extends Dig
{
    protected string $log;

    /** @param array<string, ?string> $answers dig's output per type, or null when the type gets no reply */
    public function __construct(protected array $answers)
    {
        parent::__construct(new Factory);

        $this->log = tempnam(sys_get_temp_dir(), 'dig');
    }

    public function __destruct()
    {
        unlink($this->log);
    }

    /** @return array<int, string> */
    public function processes(): array
    {
        return file($this->log, FILE_IGNORE_NEW_LINES);
    }

    /** @return array<int, string> */
    protected function buildCommand(string $domain, string $type): array
    {
        $script = implode(PHP_EOL, [
            '$types = array_map(fn ($index) => $argv[$index + 1], array_keys($argv, "-t"));',
            'file_put_contents('.var_export($this->log, true).', implode(" ", $types).PHP_EOL, FILE_APPEND);',
            '$answers = '.var_export($this->answers, true).';',
            'foreach ($types as $type) {',
            '    echo $answers[$type] ?? ";; no servers could be reached\n";',
            '}',
            'exit($answers[end($types)] === null ? 9 : 0);',
        ]);

        return [PHP_BINARY, '-r', $script, '--', '-q', $domain, '-t', $type];
    }
}
