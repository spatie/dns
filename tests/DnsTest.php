<?php

use Spatie\Dns\Dns;
use Spatie\Dns\Exceptions\CouldNotFetchDns;
use Spatie\Dns\Exceptions\InvalidArgument;
use Spatie\Dns\Handlers\Dig;
use Spatie\Dns\Handlers\DnsGetRecord;
use Spatie\Dns\Records\A;
use Spatie\Dns\Records\DNSKEY;
use Spatie\Dns\Records\DS;
use Spatie\Dns\Records\MX;
use Spatie\Dns\Records\NS;
use Spatie\Dns\Records\PTR;
use Spatie\Dns\Records\Record;
use Spatie\Dns\Records\SOA;
use Spatie\Dns\Support\Collection;
use Spatie\Dns\Support\Factory;
use Spatie\Dns\Support\Types;
use Spatie\Dns\Test\TestClasses\CustomHandler;

beforeEach(function () {
    $this->dns = new Dns;
});

it('throws an exception if an empty string is passed', function () {
    $this->dns->getRecords('');
})->throws(InvalidArgument::class);

it('fetches all records by default', function () {
    $records = $this->dns->getRecords('spatie.be');

    assertSeeRecordTypes($records, [A::class, NS::class, SOA::class, MX::class]);
});

it('has a static constructor', function () {
    $records = Dns::query()->getRecords('spatie.be');

    assertSeeRecordTypes($records, [A::class, NS::class, SOA::class, MX::class]);
});

it('fetches all records with asterisk', function () {
    $records = $this->dns->getRecords('spatie.be', '*');

    assertSeeRecordTypes($records, [A::class, NS::class, SOA::class, MX::class]);
});

it('fetches records for a single type via flag', function () {
    $records = $this->dns->getRecords('spatie.be', DNS_NS);

    assertOnlySeeRecordTypes($records, [NS::class]);
});

it('fetches records for a single type via name', function () {
    $records = $this->dns->getRecords('spatie.be', 'NS');

    assertOnlySeeRecordTypes($records, [NS::class]);
});

it('fetches records for multiple types via flags', function () {
    $records = $this->dns->getRecords('spatie.be', DNS_NS | DNS_SOA);

    assertOnlySeeRecordTypes($records, [NS::class, SOA::class]);
});

it('fetches records for multiple types via names', function () {
    $records = $this->dns->getRecords('spatie.be', ['NS', 'SOA']);

    assertOnlySeeRecordTypes($records, [NS::class, SOA::class]);
});

it('fetches records via name and ignores casing', function () {
    $records = $this->dns->getRecords('spatie.be', 'ns');

    assertOnlySeeRecordTypes($records, [NS::class]);
});

it('transforms DNSSEC dig output into records', function (string $type, string $recordClass, string $output) {
    $records = $this->dns
        ->useHandlers([new FixtureDig($output)])
        ->getRecords('icann.com', $type);

    expect($records)->toHaveCount(1);

    foreach ($records as $record) {
        expect($record)->toBeInstanceOf($recordClass)
            ->and($record->host())->toBe('icann.com');
    }
})->with([
    ['DS', DS::class, 'icann.com. 21600 IN DS 50326 13 2 48DBDF26A611338821CBC565EF046F48AD1B92D5DE15DA95BB9D1ABD C6F88DC9'],
    ['DNSKEY', DNSKEY::class, 'icann.com. 3600 IN DNSKEY 256 3 13 Up+opSl3nHqfqgOgWKBduzNY/18thL7BcDRIxtkdDl3C/LVIOyjy5lEj dbdUrRHYYec/ke+5YTHvhX7pkIffjg=='],
]);

it('ignores dig comments when parsing records', function () {
    $records = $this->dns
        ->useHandlers([new FixtureDig(digResponse('NOERROR', 'spatie.be.		949	IN	A	103.133.1.1'))])
        ->getRecords('spatie.be', DNS_A);

    expect($records)->toHaveCount(1)
        ->and($records[0])->toBeInstanceOf(A::class)
        ->and($records[0]->ip())->toBe('103.133.1.1');
});

it('throws when the nameserver answers with an error status', function (string $status) {
    $dns = $this->dns
        ->throwOnErrorResponse()
        ->useHandlers([new FixtureDig(digResponse($status))]);

    expect(fn () => $dns->getRecords('spatie.be', DNS_A))
        ->toThrow(fn (CouldNotFetchDns $exception) => expect($exception->responseStatus)->toBe($status));
})->with(['REFUSED', 'SERVFAIL']);

it('returns no records for an error status when not asked to throw', function () {
    $records = $this->dns
        ->useHandlers([new FixtureDig(digResponse('REFUSED'))])
        ->getRecords('spatie.be', DNS_A);

    expect($records)->toBe([]);
});

it('treats NXDOMAIN as an answer rather than an error', function () {
    $records = $this->dns
        ->throwOnErrorResponse()
        ->useHandlers([new FixtureDig(digResponse('NXDOMAIN', 'spatie.be.		1800	IN	SOA	ns1.digitalocean.com. hostmaster.spatie.be. 0 10800 3600 604800 1800'))])
        ->getRecords('missing.spatie.be', DNS_A);

    expect($records)->toBe([]);
});

it('reads the status from the header and not from record data', function () {
    $records = $this->dns
        ->throwOnErrorResponse()
        ->useHandlers([new FixtureDig(digResponse('NOERROR', 'spatie.be.		300	IN	TXT	"->>HEADER<<- opcode: QUERY, status: REFUSED, id: 1"'))])
        ->getRecords('spatie.be', DNS_TXT);

    expect($records)->toHaveCount(1);
});

it('resolves DNSSEC names and flags without selecting other record types', function (int|string|array $types, array $expected) {
    $records = $this->dns
        ->useHandlers([new CustomHandler])
        ->getRecords('example.com', $types);

    expect($records)->toBe($expected);
})->with([
    ['ds', ['custom-handler-results-DS']],
    ['dnskey', ['custom-handler-results-DNSKEY']],
    [Types::DNS_DS, ['custom-handler-results-DS']],
    [Types::DNS_DNSKEY, ['custom-handler-results-DNSKEY']],
    [['ds', 'DNSKEY', 'A'], ['custom-handler-results-A', 'custom-handler-results-DS', 'custom-handler-results-DNSKEY']],
    [DNS_A | Types::DNS_DS | Types::DNS_DNSKEY, ['custom-handler-results-A', 'custom-handler-results-DS', 'custom-handler-results-DNSKEY']],
]);

it('rejects DNSSEC queries with the native PHP handler', function (int|string $type, string $name) {
    $dns = $this->dns->useHandlers([new DnsGetRecord(new Factory)]);

    expect(fn () => $dns->getRecords('example.com', $type))
        ->toThrow(CouldNotFetchDns::class, "The native PHP handler does not support fetching {$name} records.");
})->with([
    ['ds', 'DS'],
    ['dnskey', 'DNSKEY'],
    [Types::DNS_DS, 'DS'],
    [Types::DNS_DNSKEY, 'DNSKEY'],
]);

it('fetches records for given type and ignores record chain', function () {
    $records = $this->dns->getRecords('www.opendor.me', DNS_A);

    assertOnlySeeRecordTypes($records, [A::class]);
});

it('can fetch ptr record', function () {
    $records = $this->dns->getRecords('1.73.1.5.in-addr.arpa', DNS_PTR);
    $record = array_pop($records);

    $ptrRecord = PTR::make([
        'host' => '1.73.1.5.in-addr.arpa.',
        'class' => 'IN',
        'ttl' => 3600,
        'type' => 'PTR',
        'target' => 'ae0.452.fra1.de.creoline.net.',
    ]);

    expect([
        $record->host(),
        $record->class(),
        $record->type(),
        $record->target(),
    ])->toBe([
        $ptrRecord->host(),
        $ptrRecord->class(),
        $ptrRecord->type(),
        $ptrRecord->target(),
    ]);
});

it('throws an exception if an invalid record type is passed', function () {
    $this->dns->getRecords('spatie.be', 'xyz');
})->throws(InvalidArgument::class);

it('uses provided nameserver if set', function () {
    $this->dns->useNameserver('ns1.openminds.be');

    expect($this->dns->getNameserver())->toBe('ns1.openminds.be');
});

it('uses default nameserver if not set', function () {
    expect($this->dns->getNameserver())->toBeNull();
});

it('uses provided timeout if set', function () {
    $this->dns->setTimeout(5);

    expect($this->dns->getTimeout())->toBe(5);
});

it('uses default timeout if not set', function () {
    expect($this->dns->getTimeout())->toBe(2);
});

it('uses provided retries if set', function () {
    $this->dns->setRetries(5);

    expect($this->dns->getRetries())->toBe(5);
});

it('uses default retries if not set', function () {
    expect($this->dns->getRetries())->toBe(2);
});

it('throws exception on failed to fetch dns record', function () {
    $this->dns
        ->useNameserver('dns.spatie.be')
        ->getRecords('spatie.be', DNS_A);
})->throws(CouldNotFetchDns::class);

it('can use custom handlers without implicitly requesting DNSSEC records', function (int|string $types) {
    $result = $this->dns
        ->useHandlers([new CustomHandler])
        ->getRecords('spatie.be', $types);

    $handlers = [
        'custom-handler-results-A',
        'custom-handler-results-AAAA',
        'custom-handler-results-CNAME',
        'custom-handler-results-NS',
        'custom-handler-results-PTR',
        'custom-handler-results-SOA',
        'custom-handler-results-MX',
        'custom-handler-results-SRV',
        'custom-handler-results-TXT',
    ];

    if (defined('DNS_CAA')) {
        $handlers[] = 'custom-handler-results-CAA';
    }

    expect($result)->toBe($handlers);
})->with([DNS_ALL, '*']);

function assertSeeRecordTypes(array $records, array $types): void
{
    foreach ($types as $type) {
        $foundRecords = array_filter(
            $records,
            fn (Record $record): bool => is_a($record, $type)
        );

        expect($foundRecords)->not->toBeEmpty();
    }
}

function assertDontSeeRecordTypes(Collection $records, array $types): void
{
    foreach ($types as $type) {
        $foundRecords = array_filter(
            $records->all(),
            fn (Record $record): bool => is_a($record, $type)
        );

        expect($foundRecords)->toBeEmpty();
    }
}

function assertOnlySeeRecordTypes(array $records, array $types): void
{
    $expectedCount = count($records);

    $foundRecords = Collection::make($records)
        ->filter(fn (Record $record) => recordIsOfType($record, $types));

    expect($foundRecords)->toHaveCount($expectedCount);
}

function recordIsOfType(Record $record, array $types): bool
{
    foreach ($types as $type) {
        if (is_a($record, $type) && $record->type() === (new ReflectionClass($type))->getShortName()) {
            return true;
        }
    }

    return false;
}

function digResponse(string $status, string $records = ''): string
{
    return implode(PHP_EOL, [
        ';; Got answer:',
        ";; ->>HEADER<<- opcode: QUERY, status: {$status}, id: 3514",
        ';; flags: qr aa rd; QUERY: 1, ANSWER: 1, AUTHORITY: 0, ADDITIONAL: 1',
        '',
        ';; OPT PSEUDOSECTION:',
        '; EDNS: version: 0, flags:; udp: 1232',
        ';; ANSWER SECTION:',
        $records,
        '',
    ]);
}

class FixtureDig extends Dig
{
    public function __construct(protected string $output)
    {
        parent::__construct(new Factory);
    }

    /** @return array<int, string> */
    protected function buildCommand(string $domain, string $type): array
    {
        return [PHP_BINARY, '-r', 'echo '.var_export($this->output, true).';'];
    }
}
