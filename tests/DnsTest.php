<?php

use Spatie\Dns\Dns;
use Spatie\Dns\Exceptions\CouldNotFetchDns;
use Spatie\Dns\Exceptions\InvalidArgument;
use Spatie\Dns\Records\A;
use Spatie\Dns\Records\MX;
use Spatie\Dns\Records\NS;
use Spatie\Dns\Records\PTR;
use Spatie\Dns\Records\Record;
use Spatie\Dns\Records\SOA;
use Spatie\Dns\Support\Collection;
use Spatie\Dns\Test\TestClasses\CustomHandler;

beforeEach(function () {
    ray()->newScreen($this->name());

    $this->dns = new Dns();
});

it('throws an exception if an empty string is passed', function () {
    $this->dns->getRecords('');
})->throws(InvalidArgument::class);

it('fetches all records by default', function () {
    $records = $this->dns->getRecords('spatie.be');

    assertSeeRecordTypes($records, [A::class, NS::class, SOA::class, MX::class]);
});

it('has a static constructor', function () {
    $records = DNS::query()->getRecords('spatie.be');

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

it('can use custom handlers', function () {
    $result = $this->dns
        ->useHandlers([new CustomHandler()])
        ->getRecords('spatie.be');

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
});

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
